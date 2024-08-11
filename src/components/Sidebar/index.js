/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { ComplementaryArea } from '@wordpress/interface';

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

export default Sidebar;
