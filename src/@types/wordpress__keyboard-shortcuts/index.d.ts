declare module '@wordpress/keyboard-shortcuts' {
	import type { WordPressComponentProps } from '@wordpress/components/build-types/context';
	import type {
		ReduxStoreConfig,
		StoreDescriptor,
	} from '@wordpress/data/build-types/types';
	import type { WPKeycodeModifier } from '@wordpress/keycodes';

	// Store.
	export const store: {
		name: 'core/keyboard-shortcuts';
	} & StoreDescriptor<
		ReduxStoreConfig<
			unknown,
			typeof import('./store/actions'),
			typeof import('./store/selectors')
		>
	>;
	export type WPShortcutKeyCombination = {
		character: string;
		modifier?: WPKeycodeModifier;
	};

	// `ShortcutProvider` component.
	export function ShortcutProvider(
		props: WordPressComponentProps< null, 'div' >
	): JSX.Element;

	// `useShortcut` hook.
	type UseShortcutOptions = {
		isDisabled?: boolean;
	};
	export function useShortcut(
		name: string,
		callback: ( event: Event ) => void,
		options?: UseShortcutOptions
	): void;
}
