/**
 * External dependencies
 */
import Markdown from 'markdown-to-jsx';
import { helpers } from '@ai-services/ai';

/**
 * WordPress dependencies
 */
import { useMemo } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import type { WordPressComponentProps } from '@wordpress/components/build-types/context';

/**
 * Internal dependencies
 */
import type { MediaProps, JsonTextareaProps, PartsProps } from './types';
import './style.scss';

/**
 * Renders a single media element.
 *
 * @since 0.4.0
 *
 * @param props - Component props.
 * @returns The component to be rendered.
 */
function Media( props: WordPressComponentProps< MediaProps, null > ) {
	const { mimeType, src } = props;

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
 * @param props - Component props.
 * @returns The component to be rendered.
 */
function JsonTextarea(
	props: WordPressComponentProps< JsonTextareaProps, null >
) {
	const { data, label } = props;

	const dataJson = useMemo( () => {
		return JSON.stringify( data, null, 2 );
	}, [ data ] );

	return (
		<textarea
			className="code"
			aria-label={ label }
			value={ dataJson }
			rows={ 5 }
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
 * @param props - Component props.
 * @returns The component to be rendered.
 */
export default function Parts(
	props: WordPressComponentProps< PartsProps, null >
) {
	const { parts } = props;

	return parts.map( ( part, index ) => {
		if ( 'text' in part ) {
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

		if ( 'inlineData' in part ) {
			const { mimeType, data } = part.inlineData;
			const base64 = helpers.base64DataToBase64DataUrl( data, mimeType );
			return (
				<div className="ai-services-content-part" key={ index }>
					<Media mimeType={ mimeType } src={ base64 } />
				</div>
			);
		}

		if ( 'fileData' in part ) {
			const { mimeType, fileUri } = part.fileData;
			return (
				<div className="ai-services-content-part" key={ index }>
					<Media mimeType={ mimeType } src={ fileUri } />
				</div>
			);
		}

		if ( 'functionCall' in part ) {
			return (
				<div className="ai-services-content-part" key={ index }>
					<JsonTextarea
						data={ part.functionCall }
						label={ __( 'Function call data', 'ai-services' ) }
					/>
				</div>
			);
		}

		if ( 'functionResponse' in part ) {
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
