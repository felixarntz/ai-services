/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { SlotFillProvider } from '@wordpress/components';
import { ErrorBoundary } from '@wordpress/editor';

/**
 * Internal dependencies
 */
import Interface from '../Interface';
import ShortcutsRegister from '../ShortcutsRegister';
import KeyboardShortcutsHelpModal from '../KeyboardShortcutsHelpModal';
import './style.scss';

export default function App( { className, labels, children } ) {
	return (
		<SlotFillProvider>
			<ErrorBoundary>
				<Interface className={ className } labels={ labels }>
					{ children }
				</Interface>
				<ShortcutsRegister />
				<KeyboardShortcutsHelpModal />
			</ErrorBoundary>
		</SlotFillProvider>
	);
}

App.propTypes = {
	className: PropTypes.string,
	labels: PropTypes.shape( {
		header: PropTypes.string,
		body: PropTypes.string,
		sidebar: PropTypes.string,
		secondarySidebar: PropTypes.string,
		actions: PropTypes.string,
		footer: PropTypes.string,
	} ),
	children: PropTypes.node.isRequired,
};
