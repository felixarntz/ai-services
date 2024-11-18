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
import { __ } from '@wordpress/i18n';

/**
 * Renders a sidebar for the application.
 *
 * Multiple sidebars can be rendered, but only one can be active at a time.
 * Additionally, sidebars can be pinned by the user for easy access. The PinnedSidebars component can be used to render
 * icon buttons for the pinned sidebars.
 *
 * @since 0.1.0
 *
 * @param {Object}   props                   Component props.
 * @param {string}   props.identifier        Identifier for the sidebar, to use in the store.
 * @param {string}   props.title             Title of the sidebar.
 * @param {Element}  props.icon              Icon to display in the sidebar header.
 * @param {?boolean} props.isPinnable        Whether the sidebar can be pinned by the user. If not, UI to open/close
 *                                           the sidebar needs to be manually rendered, e.g. via the SidebarToggle
 *                                           component.
 * @param {?boolean} props.isActiveByDefault Whether the sidebar is active by default.
 * @param {Element}  props.children          Child elements to render.
 * @return {Component} The component to be rendered.
 */
function Sidebar( {
	identifier,
	title,
	icon,
	isPinnable,
	isActiveByDefault,
	children,
} ) {
	return (
		<ComplementaryArea
			scope="ai-services"
			identifier={ identifier }
			title={ title }
			icon={ icon }
			isPinnable={ isPinnable }
			isActiveByDefault={ isActiveByDefault }
			closeLabel={ __( 'Close sidebar', 'ai-services' ) }
		>
			{ children }
		</ComplementaryArea>
	);
}

Sidebar.propTypes = {
	identifier: PropTypes.string.isRequired,
	title: PropTypes.string.isRequired,
	icon: PropTypes.node.isRequired,
	isPinnable: PropTypes.bool,
	isActiveByDefault: PropTypes.bool,
	children: PropTypes.node.isRequired,
};

Sidebar.Slot = () => {
	return <ComplementaryArea.Slot scope="ai-services" />;
};

/**
 * Hook to check whether any fills are provided for the Sidebar slot.
 *
 * @since 0.1.0
 *
 * @return {boolean} True if there are any Sidebar fills, false otherwise.
 */
export function useHasSidebar() {
	const fills = useSlotFills( 'ComplementaryArea/ai-services' );
	return !! fills?.length;
}

export default Sidebar;
