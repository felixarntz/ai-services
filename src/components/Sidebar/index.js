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

Sidebar.Slot = () => {
	return <ComplementaryArea.Slot scope="wp-oop-plugin-lib-example" />;
};

export default Sidebar;
