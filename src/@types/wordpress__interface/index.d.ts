declare module '@wordpress/interface' {
	import type { ReactNode } from 'react';
	import type { WordPressComponentProps } from '@wordpress/components/build-types/context';
	import type {
		ReduxStoreConfig,
		StoreDescriptor,
	} from '@wordpress/data/build-types/types';

	// Store.
	export const store: {
		name: 'core/interface';
	} & StoreDescriptor<
		ReduxStoreConfig<
			unknown,
			typeof import('./store/actions'),
			typeof import('./store/selectors')
		>
	>;

	// `ComplementaryArea` component.
	export type ComplementaryAreaSlotProps = {
		scope: string;
	};
	export type ComplementaryAreaProps = {
		scope: string;
		identifier: string;
		title: string;
		children: ReactNode;
		name?: string;
		className?: string;
		closeLabel?: string;
		header?: ReactNode;
		headerClassName?: string;
		icon?: ReactNode;
		isPinnable?: boolean;
		panelClassName?: string;
		toggleShortcut?: string;
		isActiveByDefault?: boolean;
	};
	export const ComplementaryArea: {
		( props: ComplementaryAreaProps ): JSX.Element;
		Slot: (
			props: WordPressComponentProps< ComplementaryAreaSlotProps, 'div' >
		) => JSX.Element;
	};

	// `InterfaceSkeleton` component.
	export type InterfaceSkeletonProps = {
		isDistractionFree?: boolean;
		footer?: ReactNode;
		header?: ReactNode;
		editorNotices?: ReactNode;
		sidebar?: ReactNode;
		secondarySidebar?: ReactNode;
		content?: ReactNode;
		actions?: ReactNode;
		labels: {
			header?: string;
			body?: string;
			sidebar?: string;
			secondarySidebar?: string;
			actions?: string;
			footer?: string;
		};
		className?: string;
	};
	export function InterfaceSkeleton(
		props: InterfaceSkeletonProps
	): JSX.Element;

	// `PinnedItems` component.
	export type PinnedItemsSlotProps = {
		scope: string;
		className?: string;
	};
	export type PinnedItemsProps = {
		scope: string;
	};
	export const PinnedItems: {
		(
			props: WordPressComponentProps< PinnedItemsProps, 'div' >
		): JSX.Element;
		Slot: (
			props: WordPressComponentProps< PinnedItemsSlotProps, 'div' >
		) => JSX.Element;
	};
}
