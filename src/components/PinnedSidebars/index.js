/**
 * WordPress dependencies
 */
import { PinnedItems } from '@wordpress/interface';

/**
 * Renders the container for any pinned sidebars.
 *
 * Multiple sidebars can be rendered in the application, and users can pin them for easy access.
 * This component will automatically render icon buttons for all pinned sidebars.
 *
 * @since n.e.x.t
 *
 * @return {Component} The component to be rendered.
 */
export default function PinnedSidebars() {
	return <PinnedItems.Slot scope="ai-services" />;
}
