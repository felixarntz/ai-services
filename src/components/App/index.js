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

export default function App() {
	return (
		<SlotFillProvider>
			<ErrorBoundary>
				<Interface />
				<ShortcutsRegister />
			</ErrorBoundary>
		</SlotFillProvider>
	);
}
