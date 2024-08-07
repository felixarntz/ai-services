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

export default function App( { children } ) {
	return (
		<SlotFillProvider>
			<ErrorBoundary>
				<Interface>{ children }</Interface>
				<ShortcutsRegister />
			</ErrorBoundary>
		</SlotFillProvider>
	);
}
