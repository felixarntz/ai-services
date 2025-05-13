/**
 * External dependencies
 */
import { useStoreState, useTabStore } from '@ariakit/react';

/**
 * WordPress dependencies
 */
import { useInstanceId } from '@wordpress/compose';
import { useEffect, useMemo } from '@wordpress/element';
import { isRTL } from '@wordpress/i18n';
import type { WordPressComponentProps } from '@wordpress/components/build-types/context';

/**
 * Internal dependencies
 */
import { TabsContext } from './context';
import { Tab } from './tab';
import { TabList } from './tablist';
import { TabPanel } from './tabpanel';
import type { TabsProps } from './types';

const externalToInternalTabId = (
	externalId: string | undefined | null,
	instanceId: string
) => {
	return externalId && `${ instanceId }-${ externalId }`;
};

const internalToExternalTabId = (
	internalId: string | undefined | null,
	instanceId: string
) => {
	return typeof internalId === 'string'
		? internalId.replace( `${ instanceId }-`, '' )
		: internalId;
};

/**
 * Renders tabs infrastructure.
 *
 * @param props - Component props.
 * @returns The component to be rendered.
 */
function InternalTabs( props: WordPressComponentProps< TabsProps, null > ) {
	const {
		selectOnMove = true,
		defaultTabId,
		orientation = 'horizontal',
		onSelect,
		children,
		selectedTabId,
		activeTabId,
		defaultActiveTabId,
		onActiveTabIdChange,
	} = props;

	const instanceId = useInstanceId( Tabs, 'tabs' );
	const store = useTabStore( {
		selectOnMove,
		orientation,
		defaultSelectedId: externalToInternalTabId( defaultTabId, instanceId ),
		setSelectedId: ( newSelectedId ) => {
			onSelect?.( internalToExternalTabId( newSelectedId, instanceId ) );
		},
		selectedId: externalToInternalTabId( selectedTabId, instanceId ),
		defaultActiveId: externalToInternalTabId(
			defaultActiveTabId,
			instanceId
		),
		setActiveId: ( newActiveId ) => {
			onActiveTabIdChange?.(
				internalToExternalTabId( newActiveId, instanceId )
			);
		},
		activeId: externalToInternalTabId( activeTabId, instanceId ),
		rtl: isRTL(),
	} );

	const { items, activeId } = useStoreState( store );
	const { setActiveId } = store;

	useEffect( () => {
		window.requestAnimationFrame( () => {
			const focusedElement =
				items?.[ 0 ]?.element?.ownerDocument.activeElement;

			if (
				! focusedElement ||
				! items.some( ( item ) => focusedElement === item.element )
			) {
				return; // Return early if no tabs are focused.
			}

			// If, after ariakit re-computes the active tab, that tab doesn't match
			// the currently focused tab, then we force an update to ariakit to avoid
			// any mismatches, especially when navigating to previous/next tab with
			// arrow keys.
			if ( activeId !== focusedElement.id ) {
				setActiveId( focusedElement.id );
			}
		} );
	}, [ activeId, items, setActiveId ] );

	const contextValue = useMemo(
		() => ( {
			store,
			instanceId,
		} ),
		[ store, instanceId ]
	);

	return (
		<TabsContext.Provider value={ contextValue }>
			{ children }
		</TabsContext.Provider>
	);
}

/**
 * Tabs is a collection of React components that combine to render
 * an [ARIA-compliant tabs pattern](https://www.w3.org/WAI/ARIA/apg/patterns/tabs/).
 *
 * Tabs organizes content across different screens, data sets, and interactions.
 * It has two sections: a list of tabs, and the view to show when a tab is chosen.
 *
 * `Tabs` itself is a wrapper component and context provider.
 * It is responsible for managing the state of the tabs, and rendering one instance of the `Tabs.TabList` component and one or more instances of the `Tab.TabPanel` component.
 */
const Tabs = Object.assign( InternalTabs, {
	displayName: 'Tabs',
	/**
	 * Renders a single tab.
	 *
	 * The currently active tab receives default styling that can be
	 * overridden with CSS targeting `[aria-selected="true"]`.
	 */
	Tab: Object.assign( Tab, {
		displayName: 'Tabs.Tab',
	} ),
	/**
	 * A wrapper component for the `Tab` components.
	 *
	 * It is responsible for rendering the list of tabs.
	 */
	TabList: Object.assign( TabList, {
		displayName: 'Tabs.TabList',
	} ),
	/**
	 * Renders the content to display for a single tab once that tab is selected.
	 */
	TabPanel: Object.assign( TabPanel, {
		displayName: 'Tabs.TabPanel',
	} ),
	Context: Object.assign( TabsContext, {
		displayName: 'Tabs.Context',
	} ),
} );

export default Tabs;
