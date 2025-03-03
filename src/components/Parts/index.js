/**
 * External dependencies
 */
import Markdown from 'markdown-to-jsx';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useMemo } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import './style.scss';

/**
 * Renders a single media element.
 *
 * @since 0.4.0
 *
 * @param {Object} props          The component props.
 * @param {string} props.mimeType The media MIME type.
 * @param {string} props.src      The media source.
 * @return {Component} The component to be rendered.
 */
function Media( { mimeType, src } ) {
	if ( mimeType.startsWith( 'image' ) ) {
		return <img src={ src } alt="" />;
	}

	if ( mimeType.startsWith( 'audio' ) ) {
		return <audio src={ src } controls />;
	}

	if ( mimeType.startsWith( 'video' ) ) {
		return <video src={ src } controls />;
	}

	return null;
}

/**
 * Renders a textarea with JSON formatted data.
 *
 * @since 0.5.0
 *
 * @param {Object} props       The component props.
 * @param {Object} props.data  The data to display.
 * @param {string} props.label The textarea label.
 * @return {Component} The component to be rendered.
 */
function JsonTextarea( { data, label } ) {
	const dataJson = useMemo( () => {
		return JSON.stringify( data, null, 2 );
	}, [ data ] );

	return (
		<textarea
			className="code"
			aria-label={ label }
			value={ dataJson }
			rows="5"
			readOnly
		/>
	);
}

/**
 * Renders formatted content parts.
 *
 * @since 0.4.0
 * @since 0.5.0 Made publicly available as part of `@ai-services/components`.
 *
 * @param {Object}   props       Component props.
 * @param {Object[]} props.parts The parts to render.
 * @return {Component} The component to be rendered.
 */
function Parts( { parts } ) {
	return parts.map( ( part, index ) => {
		if ( part.text ) {
			return (
				<div className="ai-services-content-part" key={ index }>
					<Markdown
						options={ {
							forceBlock: true,
							forceWrapper: true,
						} }
					>
						{ part.text }
					</Markdown>
				</div>
			);
		}

		if ( part.inlineData ) {
			const { mimeType, data } = part.inlineData;
			const base64 = /^data:[a-z0-9-]+\/[a-z0-9-]+;base64,/.test( data )
				? data
				: `data:${ mimeType };base64,${ data }`;
			return (
				<div className="ai-services-content-part" key={ index }>
					<Media mimeType={ mimeType } src={ base64 } />
				</div>
			);
		}

		if ( part.fileData ) {
			const { mimeType, fileUri } = part.fileData;
			return (
				<div className="ai-services-content-part" key={ index }>
					<Media mimeType={ mimeType } src={ fileUri } />
				</div>
			);
		}

		if ( part.functionCall ) {
			return (
				<div className="ai-services-content-part" key={ index }>
					<JsonTextarea
						data={ part.functionCall }
						label={ __( 'Function call data', 'ai-services' ) }
					/>
				</div>
			);
		}

		if ( part.functionResponse ) {
			return (
				<div className="ai-services-content-part" key={ index }>
					<JsonTextarea
						data={ part.functionResponse }
						label={ __( 'Function response data', 'ai-services' ) }
					/>
				</div>
			);
		}

		return null;
	} );
}

Parts.propTypes = {
	parts: PropTypes.arrayOf( PropTypes.object ).isRequired,
};

export default Parts;
