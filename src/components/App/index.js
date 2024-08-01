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
import SettingsCards from '../SettingsCards';

export default function App() {
	return (
		<SlotFillProvider>
			<ErrorBoundary>
				<Interface>
					<SettingsCards />
				</Interface>
				<ShortcutsRegister />
			</ErrorBoundary>
		</SlotFillProvider>
	);
}
