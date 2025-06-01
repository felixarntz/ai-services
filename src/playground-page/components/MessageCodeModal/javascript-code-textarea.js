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

const toJavaScriptValue = ( input, rootIndentTabs = 0 ) => {
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
			return toJavaScriptValue( value, rootIndentTabs + 1 );
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
		addLine( '[' );
		input.forEach( ( value ) => {
			addLine( '\t' + processValue( value ) + ',' );
		} );
		addLine( ']' );
	} else if ( typeof input === 'object' && input !== null ) {
		addLine( '{' );
		Object.keys( input ).forEach( ( key ) => {
			const value = input[ key ];
			addLine( `\t${ key }: ${ processValue( value ) },` );
		} );
		addLine( '}' );
	} else {
		addLine( processValue( input ) );
	}

	return lines.join( '\n' ).trimStart();
};

const getJavaScriptCode = ( rawData, service, foundationalCapability ) => {
	const line = ( content, rootIndentTabs = 0 ) => {
		return '\t'.repeat( rootIndentTabs ) + content;
	};

	const parts = toJavaScriptValue( rawData.content.parts, 2 );

	let modelParams;
	let functionDeclarations;
	if ( rawData.modelParams.tools?.[ 0 ]?.functionDeclarations ) {
		const modelParamsWithoutFunctionDeclarations = {
			...rawData.modelParams,
			tools: [
				{
					...rawData.modelParams.tools[ 0 ],
					functionDeclarations: 'functionDeclarations',
				},
				...rawData.modelParams.tools.slice( 1 ),
			],
		};
		modelParams = toJavaScriptValue(
			modelParamsWithoutFunctionDeclarations,
			3
		);
		modelParams = modelParams.replace(
			"'functionDeclarations'",
			'functionDeclarations'
		);

		functionDeclarations =
			'\n' +
			line(
				'const functionDeclarations = ' +
					toJavaScriptValue(
						rawData.modelParams.tools[ 0 ].functionDeclarations,
						1
					) +
					';',
				1
			) +
			'\n';
	} else {
		modelParams = toJavaScriptValue( rawData.modelParams, 3 );
		functionDeclarations = '';
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
${ functionDeclarations }
	try {
		const candidates = await service.${ method }(
			${ promptVariableName },
			${ modelParams }
		);
	} catch ( error ) {
		// Handle the error.
	}
}
`;

	return jsCode;
};

/**
 * Renders a textarea with the JavaScript code for the selected message.
 *
 * @since 0.6.0
 *
 * @param {Object} props                        The component properties.
 * @param {Object} props.rawData                The raw data for the selected message.
 * @param {Object} props.service                The service for the selected message.
 * @param {Object} props.foundationalCapability The foundational capability for the selected message.
 * @return {Component} The component to be rendered.
 */
export default function JavaScriptCodeTextarea( {
	rawData,
	service,
	foundationalCapability,
} ) {
	const jsCode = useMemo( () => {
		if ( ! rawData || ! service || ! foundationalCapability ) {
			return '';
		}
		return getJavaScriptCode( rawData, service, foundationalCapability );
	}, [ rawData, service, foundationalCapability ] );

	const textareaRef = useRef();

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
				rows="14"
				readOnly
			/>
		</div>
	);
}
