/**
 * External dependencies
 */
import { enums } from '@ai-services/ai';
import type {
	AiCapability,
	Candidates,
	ModelParams,
} from '@ai-services/ai/types';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useMemo, useRef } from '@wordpress/element';

/**
 * Internal dependencies
 */
import useCodeMirrorEffect from './use-codemirror-effect';
import type { AiPlaygroundMessageAdditionalData } from '../../types';

const toPhpValue = ( input: unknown, rootIndentTabs: number = 0 ): string => {
	const lines: string[] = [];

	const addLine = ( line: string ): void => {
		const indentTabs = '\t'.repeat( rootIndentTabs );
		lines.push( `${ indentTabs }${ line }` );
	};

	const processValue = ( value: unknown ): string => {
		if (
			Array.isArray( value ) ||
			( typeof value === 'object' && value !== null )
		) {
			// Type assertion needed is recursive.
			return toPhpValue( value, rootIndentTabs + 1 );
		}

		if ( typeof value === 'string' ) {
			let strValue = value;
			const match = strValue.match(
				/^data:[a-z0-9-]+\/[a-z0-9-]+;base64,/
			);
			if ( match ) {
				strValue = match[ 0 ] + '...truncated...';
			}
			if ( strValue.includes( "'" ) ) {
				if ( strValue.includes( '"' ) ) {
					return `'${ strValue.replace( /'/g, "\\'" ) }'`;
				}
				return `"${ strValue }"`;
			}
			return `'${ strValue }'`;
		}

		return `${ value }`;
	};

	if ( Array.isArray( input ) ) {
		addLine( 'array(' );
		input.forEach( ( value: unknown ) => {
			addLine( '\t' + processValue( value ) + ',' );
		} );
		addLine( ')' );
	} else if ( typeof input === 'object' && input !== null ) {
		const objectInput = input as { [ key: string ]: unknown };
		addLine( 'array(' );
		const maxPropertyLength = Object.keys( objectInput ).reduce(
			( max, key ) => Math.max( max, key.length ),
			0
		);
		Object.keys( objectInput ).forEach( ( key: string ) => {
			const value = objectInput[ key ];
			const paddedKey = `'${ key }'`.padEnd( maxPropertyLength + 2 );
			addLine( `\t${ paddedKey } => ${ processValue( value ) },` );
		} );
		addLine( ')' );
	} else {
		addLine( processValue( input ) );
	}

	return lines.join( '\n' ).trimStart();
};

type UserMessageRawData = Exclude<
	AiPlaygroundMessageAdditionalData[ 'rawData' ],
	Candidates | undefined
>;
type UserMessageService = Exclude<
	AiPlaygroundMessageAdditionalData[ 'service' ],
	undefined
>;

const getPhpCode = (
	rawData: UserMessageRawData,
	service: UserMessageService,
	foundationalCapability: AiCapability
): string => {
	// TODO: Support multiple content parts, relevant for sending history.
	const content = Array.isArray( rawData.content )
		? rawData.content[ rawData.content.length - 1 ]
		: rawData.content;

	const line = (
		lineContent: string,
		rootIndentTabs: number = 0
	): string => {
		return '\t'.repeat( rootIndentTabs ) + lineContent;
	};

	const parts = toPhpValue( content.parts, 3 );

	let modelParamsString: string;
	let functionDeclarationsString: string = '';
	if (
		rawData.modelParams.tools !== undefined &&
		rawData.modelParams.tools.length &&
		'functionDeclarations' in rawData.modelParams.tools[ 0 ]
	) {
		const functionDeclarations =
			rawData.modelParams.tools[ 0 ].functionDeclarations;

		const modelParamsWithoutFunctionDeclarations: ModelParams = {
			...rawData.modelParams,
			tools: [
				{
					...rawData.modelParams.tools[ 0 ],
					// eslint-disable-next-line @typescript-eslint/no-explicit-any
					functionDeclarations: '$function_declarations' as any, // Hack to allow for placeholder.
				},
				...rawData.modelParams.tools.slice( 1 ),
			],
		};
		modelParamsString = toPhpValue(
			modelParamsWithoutFunctionDeclarations,
			4
		);
		modelParamsString = modelParamsString.replace(
			"'$function_declarations'",
			'$function_declarations'
		);

		functionDeclarationsString =
			'\n' +
			line(
				'$function_declarations = ' +
					toPhpValue( functionDeclarations, 1 ) +
					';',
				1
			) +
			'\n';
	} else {
		modelParamsString = toPhpValue( rawData.modelParams, 4 );
	}

	let method = 'generate_text';
	let promptVariableName = '$prompt';
	switch ( foundationalCapability ) {
		case enums.AiCapability.IMAGE_GENERATION:
			method = 'generate_image';
			break;
		case enums.AiCapability.TEXT_TO_SPEECH:
			method = 'text_to_speech';
			promptVariableName = '$input';
			break;
	}

	let promptComment = '';
	if ( content.parts.length === 1 && 'text' in content.parts[ 0 ] ) {
		promptComment =
			'\n' +
			line(
				'// Alternatively, you could use the short-hand syntax and set `' +
					promptVariableName +
					'` to only the string.',
				1
			);
	} else if (
		content.parts.find(
			( part ) => 'inlineData' in part && part.inlineData.data
		)
	) {
		promptComment =
			'\n' +
			line( '// See the raw JSON data for the full base64 data URL.', 1 );
	}

	const phpCode = `<?php
use Exception;
use Felix_Arntz\\AI_Services\\Services\\API\\Enums\\Content_Role;
use Felix_Arntz\\AI_Services\\Services\\API\\Types\\Content;

if ( ai_services()->is_service_available( '${ service.slug }' ) ) {
	$service = ai_services()->get_available_service( '${ service.slug }' );
${ promptComment }
	${ promptVariableName } = Content::from_array(
		array(
			'role'  => Content_Role::USER,
			'parts' => ${ parts },
		)
	);
${ functionDeclarationsString }
	try {
		$candidates = $service
			->get_model(
				${ modelParamsString }
			)
			->${ method }( ${ promptVariableName } );
	} catch ( Exception $e ) {
		// Handle the exception.
	}
}`;
	return phpCode;
};

type PhpCodeTextareaProps = {
	rawData: UserMessageRawData;
	service: UserMessageService;
	foundationalCapability: AiCapability;
};

/**
 * Renders a textarea with the PHP code for the selected message.
 *
 * @since 0.6.0
 *
 * @param props - The component props.
 * @returns The component to be rendered.
 */
export default function PhpCodeTextarea( props: PhpCodeTextareaProps ) {
	const { rawData, service, foundationalCapability } = props;

	const phpCode = useMemo( () => {
		if ( ! rawData || ! service || ! foundationalCapability ) {
			return '';
		}
		return getPhpCode( rawData, service, foundationalCapability );
	}, [ rawData, service, foundationalCapability ] );

	const textareaRef = useRef< HTMLTextAreaElement | null >( null );

	// Initialize 'wp-codemirror'.
	useCodeMirrorEffect( textareaRef, 'php' );

	return (
		<div className="ai-services-playground__code-textarea-wrapper">
			<textarea
				ref={ textareaRef }
				className="ai-services-playground__code-textarea code"
				aria-label={ __(
					'PHP code to implement the selected prompt',
					'ai-services'
				) }
				value={ phpCode }
				rows={ 14 }
				readOnly
			/>
		</div>
	);
}
