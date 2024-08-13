/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { ComplementaryArea } from '@wordpress/interface';
import {
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalUseSlotFills as useSlotFills,
} from '@wordpress/components';

/**
 * Renders a sidebar for the application.
 *
 * Multiple sidebars can be rendered, but only one can be active at a time.
 * Additionally, sidebars can be pinned by the user for easy access. The PinnedSidebars component can be used to render
 * icon buttons for the pinned sidebars.
 *
 * @since n.e.x.t
 *
 * @param {Object}   props            Component props.
 * @param {string}   props.identifier Identifier for the sidebar, to use in the store.
 * @param {string}   props.title      Title of the sidebar.
 * @param {?Element} props.icon       Icon to display in the sidebar header.
 * @param {Element}  props.children   Child elements to render.
 * @return {Component} The component to be rendered.
 */
function Sidebar( { identifier, title, icon, children } ) {
	return (
		<ComplementaryArea
			scope="wp-oop-plugin-lib-example"
			identifier={ identifier }
			title={ title }
			icon={ icon }
		>
			{ children }
		</ComplementaryArea>
	);
}

Sidebar.propTypes = {
	identifier: PropTypes.string.isRequired,
	title: PropTypes.string.isRequired,
	icon: PropTypes.node,
	children: PropTypes.node.isRequired,
};

Sidebar.Slot = () => {
	return <ComplementaryArea.Slot scope="wp-oop-plugin-lib-example" />;
};

/**
 * Hook to check whether any fills are provided for the Sidebar slot.
 *
 * @since n.e.x.t
 *
 * @return {boolean} True if there are any Sidebar fills, false otherwise.
 */
export function useHasSidebar() {
	const fills = useSlotFills( 'ComplementaryArea/wp-oop-plugin-lib-example' );
	return !! fills?.length;
}

export default Sidebar;
