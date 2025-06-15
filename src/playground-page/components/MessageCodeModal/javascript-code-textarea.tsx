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

const toJavaScriptValue = ( input: unknown, rootIndentTabs: number = 0 ) => {
	const lines: string[] = [];

	const addLine = ( line: string ) => {
		const indentTabs = '\t'.repeat( rootIndentTabs );
		lines.push( `${ indentTabs }${ line }` );
	};

	const processValue = ( value: unknown ) => {
		if (
			Array.isArray( value ) ||
			( typeof value === 'object' && value !== null )
		) {
			return toJavaScriptValue( value, rootIndentTabs + 1 );
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
		addLine( '[' );
		input.forEach( ( value: unknown ) => {
			addLine( '\t' + processValue( value ) + ',' );
		} );
		addLine( ']' );
	} else if ( typeof input === 'object' && input !== null ) {
		const objectInput = input as { [ key: string ]: unknown };
		addLine( '{' );
		Object.keys( objectInput ).forEach( ( key ) => {
			const value = objectInput[ key ];
			addLine( `\t${ key }: ${ processValue( value ) },` );
		} );
		addLine( '}' );
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

const getJavaScriptCode = (
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

	const parts = toJavaScriptValue( content.parts, 2 );

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
					functionDeclarations: 'functionDeclarations' as any, // Hack to allow for placeholder.
				},
				...rawData.modelParams.tools.slice( 1 ),
			],
		};
		modelParamsString = toJavaScriptValue(
			modelParamsWithoutFunctionDeclarations,
			3
		);
		modelParamsString = modelParamsString.replace(
			"'functionDeclarations'",
			'functionDeclarations'
		);

		functionDeclarationsString =
			'\n' +
			line(
				'const functionDeclarations = ' +
					toJavaScriptValue( functionDeclarations, 1 ) +
					';',
				1
			) +
			'\n';
	} else {
		modelParamsString = toJavaScriptValue( rawData.modelParams, 3 );
	}

	let method = 'generateText';
	let promptVariableName = 'prompt';
	switch ( foundationalCapability ) {
		case enums.AiCapability.IMAGE_GENERATION:
			method = 'generateImage';
			break;
		case enums.AiCapability.TEXT_TO_SPEECH:
			method = 'textToSpeech';
			promptVariableName = 'input';
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

	const jsCode = `// Dependencies.
const { enums, store: aiStore } = aiServices.ai;
const { select } = wp.data;

const { isServiceAvailable, getAvailableService } = select( aiStore );

if ( isServiceAvailable( '${ service.slug }' ) ) {
	const service = getAvailableService( '${ service.slug }' );
${ promptComment }
	const ${ promptVariableName } = {
		role: enums.ContentRole.USER,
		parts: ${ parts },
	};
${ functionDeclarationsString }
	try {
		const candidates = await service.${ method }(
			${ promptVariableName },
			${ modelParamsString }
		);
	} catch ( error ) {
		// Handle the error.
	}
}
`;

	return jsCode;
};

type JavaScriptCodeTextareaProps = {
	rawData: UserMessageRawData;
	service: UserMessageService;
	foundationalCapability: AiCapability;
};

/**
 * Renders a textarea with the JavaScript code for the selected message.
 *
 * @since 0.6.0
 *
 * @param props - The component props.
 * @returns The component to be rendered.
 */
export default function JavaScriptCodeTextarea(
	props: JavaScriptCodeTextareaProps
) {
	const { rawData, service, foundationalCapability } = props;

	const jsCode = useMemo( () => {
		if ( ! rawData || ! service || ! foundationalCapability ) {
			return '';
		}
		return getJavaScriptCode( rawData, service, foundationalCapability );
	}, [ rawData, service, foundationalCapability ] );

	const textareaRef = useRef< HTMLTextAreaElement | null >( null );

	// Initialize 'wp-codemirror'.
	useCodeMirrorEffect( textareaRef, 'javascript' );

	return (
		<div className="ai-services-playground__code-textarea-wrapper">
			<textarea
				ref={ textareaRef }
				className="ai-services-playground__code-textarea code"
				aria-label={ __(
					'JavaScript code to implement the selected prompt',
					'ai-services'
				) }
				value={ jsCode }
				rows={ 14 }
				readOnly
			/>
		</div>
	);
}
