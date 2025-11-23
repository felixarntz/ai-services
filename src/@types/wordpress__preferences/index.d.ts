declare module '@wordpress/preferences' {
	import type {
		ReduxStoreConfig,
		StoreDescriptor,
	} from '@wordpress/data/build-types/types';

	// Store.
	export const store: {
		name: 'core/preferences';
	} & StoreDescriptor<
		ReduxStoreConfig<
			unknown,
			typeof import('./store/actions'),
			typeof import('./store/selectors')
		>
	>;

	// `PreferenceToggleMenuItem` component.
	export type PreferenceToggleMenuItemProps = {
		scope: string;
		name: string;
		label: string;
		info?: string;
		messageActivated?: string;
		messageDeactivated?: string;
		shortcut?: string;
		handleToggling?: boolean;
		onToggle?: () => void;
		disabled?: boolean;
	};
	export function PreferenceToggleMenuItem(
		props: PreferenceToggleMenuItemProps
	): JSX.Element;
}
