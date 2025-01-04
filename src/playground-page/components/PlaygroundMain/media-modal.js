/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';
import { MediaUpload } from '@wordpress/media-utils';
import { __ } from '@wordpress/i18n';

/**
 * Renders the default button to open the media library.
 *
 * @since n.e.x.t
 *
 * @param {Object}   props      The component props.
 * @param {Function} props.open The function to open the media library modal.
 * @return {Component} The component to be rendered.
 */
function DefaultMediaButton( { open } ) {
	return (
		<Button variant="secondary" onClick={ open }>
			{ __( 'Media Library', 'ai-services' ) }
		</Button>
	);
}

/**
 * Renders the media modal.
 *
 * @since n.e.x.t
 *
 * @param {Object}    props              The component props.
 * @param {Function}  props.onSelect     The callback function to call when a media item is selected.
 * @param {Component} props.render       The component to render that controls the media library modal, e.g. a button.
 *                                       It receives the `open` prop to open the media library modal. By default a
 *                                       regular button labeled "Media Library" is rendered.
 * @param {string[]}  props.allowedTypes The allowed media types.
 * @param {number}    props.attachmentId The attachment ID.
 * @return {Component} The component to be rendered.
 */
export default function MediaModal( {
	onSelect,
	render,
	allowedTypes,
	attachmentId,
} ) {
	return (
		<MediaUpload
			gallery={ false }
			multiple={ false }
			onSelect={ onSelect }
			allowedTypes={ allowedTypes }
			mode="browse"
			value={ attachmentId }
			render={ render || DefaultMediaButton }
		/>
	);
}

MediaModal.propTypes = {
	onSelect: PropTypes.func.isRequired,
	render: PropTypes.elementType,
	allowedTypes: PropTypes.arrayOf( PropTypes.string ),
	attachmentId: PropTypes.number,
};
