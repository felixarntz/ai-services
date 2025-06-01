/**
 * External dependencies
 */
import { enums } from '@ai-services/ai';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useMemo, useRef } from '@wordpress/element';

/**
 * Internal dependencies
 */
import useCodeMirrorEffect from './use-codemirror-effect';

const toPhpValue = ( input, rootIndentTabs = 0 ) => {
	const lines = [];

	const addLine = ( line ) => {
		const indentTabs = '\t'.repeat( rootIndentTabs );
		lines.push( `${ indentTabs }${ line }` );
	};

	const processValue = ( value ) => {
		if (
			Array.isArray( value ) ||
			( typeof value === 'object' && value !== null )
		) {
			return toPhpValue( value, rootIndentTabs + 1 );
		}

		if ( typeof value === 'string' ) {
			const match = value.match( /^data:[a-z0-9-]+\/[a-z0-9-]+;base64,/ );
			if ( match ) {
				value = match[ 0 ] + '...truncated...';
			}
			if ( value.includes( "'" ) ) {
				if ( value.includes( '"' ) ) {
					return `'${ value.replace( /'/g, "\\'" ) }'`;
				}
				return `"${ value }"`;
			}
			return `'${ value }'`;
		}

		return `${ value }`;
	};

	if ( Array.isArray( input ) ) {
		addLine( 'array(' );
		input.forEach( ( value ) => {
			addLine( '\t' + processValue( value ) + ',' );
		} );
		addLine( ')' );
	} else if ( typeof input === 'object' && input !== null ) {
		addLine( 'array(' );
		const maxPropertyLength = Object.keys( input ).reduce(
			( max, key ) => Math.max( max, key.length ),
			0
		);
		Object.keys( input ).forEach( ( key ) => {
			const value = input[ key ];
			const paddedKey = `'${ key }'`.padEnd( maxPropertyLength + 2 );
			addLine( `\t${ paddedKey } => ${ processValue( value ) },` );
		} );
		addLine( ')' );
	} else {
		addLine( processValue( input ) );
	}

	return lines.join( '\n' ).trimStart();
};

const getPhpCode = ( rawData, service, foundationalCapability ) => {
	const line = ( content, rootIndentTabs = 0 ) => {
		return '\t'.repeat( rootIndentTabs ) + content;
	};

	const parts = toPhpValue( rawData.content.parts, 3 );

	let modelParams;
	let functionDeclarations;
	if ( rawData.modelParams.tools?.[ 0 ]?.functionDeclarations ) {
		const modelParamsWithoutFunctionDeclarations = {
			...rawData.modelParams,
			tools: [
				{
					...rawData.modelParams.tools[ 0 ],
					functionDeclarations: '$function_declarations',
				},
				...rawData.modelParams.tools.slice( 1 ),
			],
		};
		modelParams = toPhpValue( modelParamsWithoutFunctionDeclarations, 4 );
		modelParams = modelParams.replace(
			"'$function_declarations'",
			'$function_declarations'
		);

		functionDeclarations =
			'\n' +
			line(
				'$function_declarations = ' +
					toPhpValue(
						rawData.modelParams.tools[ 0 ].functionDeclarations,
						1
					) +
					';',
				1
			) +
			'\n';
	} else {
		modelParams = toPhpValue( rawData.modelParams, 4 );
		functionDeclarations = '';
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
	if (
		rawData.content.parts.length === 1 &&
		rawData.content.parts[ 0 ].text
	) {
		promptComment =
			'\n' +
			line(
				'// Alternatively, you could use the short-hand syntax and set `' +
					promptVariableName +
					'` to only the string.',
				1
			);
	} else if (
		rawData.content.parts.find( ( part ) => part.inlineData?.data )
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
${ functionDeclarations }
	try {
		$candidates = $service
			->get_model(
				${ modelParams }
			)
			->${ method }( ${ promptVariableName } );
	} catch ( Exception $e ) {
		// Handle the exception.
	}
}`;
	return phpCode;
};

/**
 * Renders a textarea with the PHP code for the selected message.
 *
 * @since 0.6.0
 *
 * @param {Object} props                        The component properties.
 * @param {Object} props.rawData                The raw data for the selected message.
 * @param {Object} props.service                The service for the selected message.
 * @param {Object} props.foundationalCapability The foundational capability for the selected message.
 * @return {Component} The component to be rendered.
 */
export default function PhpCodeTextarea( {
	rawData,
	service,
	foundationalCapability,
} ) {
	const phpCode = useMemo( () => {
		if ( ! rawData || ! service || ! foundationalCapability ) {
			return '';
		}
		return getPhpCode( rawData, service, foundationalCapability );
	}, [ rawData, service, foundationalCapability ] );

	const textareaRef = useRef();

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
				rows="14"
				readOnly
			/>
		</div>
	);
}
