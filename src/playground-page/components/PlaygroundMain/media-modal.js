/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';
import { useMemo } from '@wordpress/element';
import { MediaUpload } from '@wordpress/media-utils';
import { __ } from '@wordpress/i18n';

/**
 * Renders the default button to open the media library.
 *
 * @since 0.4.0
 *
 * @param {Object}   props      The component props.
 * @param {Function} props.open The function to open the media library modal.
 * @return {Component} The component to be rendered.
 */
function DefaultMediaButton( { open } ) {
	return (
		<Button variant="secondary" onClick={ open } __next40pxDefaultSize>
			{ __( 'Media Library', 'ai-services' ) }
		</Button>
	);
}

const DEFAULT_ATTACHMENT_IDS = [];

/**
 * Renders the media modal.
 *
 * @since 0.4.0
 *
 * @param {Object}          props               The component props.
 * @param {Function}        props.onSelect      The callback function to call when a media item is selected. If
 *                                              `multiple` is true, it receives an array of media items. Otherwise, it
 *                                              receives a single media item.
 * @param {Component}       props.render        The component to render that controls the media library modal, e.g. a
 *                                              button. It receives the `open` prop to open the media library modal. By
 *                                              default a regular button labeled "Media Library" is rendered.
 * @param {string[]}        props.allowedTypes  The allowed media types.
 * @param {boolean}         props.multiple      Whether to allow multiple media items to be selected. Default false.
 * @param {number|number[]} props.attachmentIds The attachment ID, or array of attachment IDs (if `multiple` is true).
 * @return {Component} The component to be rendered.
 */
export default function MediaModal( {
	onSelect,
	render,
	allowedTypes,
	multiple = false,
	attachmentIds = DEFAULT_ATTACHMENT_IDS,
} ) {
	const sanitizedAttachmentIds = useMemo( () => {
		if ( multiple ) {
			if ( Array.isArray( attachmentIds ) ) {
				return attachmentIds;
			}
			if ( typeof attachmentIds === 'number' ) {
				return [ attachmentIds ];
			}
			return [];
		}

		if ( typeof attachmentIds === 'number' ) {
			return attachmentIds;
		}
		if ( Array.isArray( attachmentIds ) ) {
			return attachmentIds[ 0 ];
		}
		return [];
	}, [ multiple, attachmentIds ] );

	return (
		<MediaUpload
			gallery={ false }
			multiple={ multiple }
			onSelect={ onSelect }
			allowedTypes={ allowedTypes }
			mode="browse"
			value={ sanitizedAttachmentIds }
			render={ render || DefaultMediaButton }
		/>
	);
}

MediaModal.propTypes = {
	onSelect: PropTypes.func.isRequired,
	render: PropTypes.elementType,
	allowedTypes: PropTypes.arrayOf( PropTypes.string ),
	multiple: PropTypes.bool,
	attachmentId: PropTypes.oneOfType( [
		PropTypes.number,
		PropTypes.arrayOf( PropTypes.number ),
	] ),
};
