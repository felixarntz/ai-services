/**
 * WordPress dependencies
 */
import { SlotFillProvider } from '@wordpress/components';
import { ErrorBoundary } from '@wordpress/editor';
import type { WordPressComponentProps } from '@wordpress/components/build-types/context';

/**
 * Internal dependencies
 */
import Interface from '../Interface';
import ShortcutsRegister from '../ShortcutsRegister';
import KeyboardShortcutsHelpModal from '../KeyboardShortcutsHelpModal';
import type { AppProps } from './types';
import './style.scss';

/**
 * Renders the root of the application.
 *
 * @since 0.1.0
 *
 * @param props - Component props.
 * @returns The component to be rendered.
 */
export default function App(
	props: WordPressComponentProps< AppProps, null >
) {
	const { className, labels, children } = props;

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
