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
import './style.scss';

export default function App( { className, labels, children } ) {
	return (
		<SlotFillProvider>
			<ErrorBoundary>
				<Interface className={ className } labels={ labels }>
					{ children }
				</Interface>
				<ShortcutsRegister />
			</ErrorBoundary>
		</SlotFillProvider>
	);
}
