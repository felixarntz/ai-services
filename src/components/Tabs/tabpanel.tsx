/**
 * External dependencies
 */
import { useStoreState } from '@ariakit/react';
import type { ForwardedRef } from 'react';

/**
 * WordPress dependencies
 */
import { forwardRef } from '@wordpress/element';
import warning from '@wordpress/warning';
import type { WordPressComponentProps } from '@wordpress/components/build-types/context';

/**
 * Internal dependencies
 */
import { StyledTabPanel } from './styles';
import { useTabsContext } from './context';
import type { TabPanelProps } from './types';

/**
 * Renders a tab panel.
 *
 * @param props - Component props.
 * @param ref   - Reference to the component.
 * @returns The component to be rendered.
 */
function UnforwardedTabPanel(
	props: Omit< WordPressComponentProps< TabPanelProps, 'div' >, 'id' >,
	ref: ForwardedRef< HTMLDivElement >
) {
	const { children, tabId, focusable = true, ...otherProps } = props;

	const context = useTabsContext();
	const selectedId = useStoreState( context?.store, 'selectedId' );
	if ( ! context ) {
		warning( '`Tabs.TabPanel` must be wrapped in a `Tabs` component.' );
		return null;
	}
	const { store, instanceId } = context;
	const instancedTabId = `${ instanceId }-${ tabId }`;

	return (
		<StyledTabPanel
			ref={ ref }
			store={ store }
			// For TabPanel, the id passed here is the id attribute of the DOM
			// element.
			// `tabId` is the id of the tab that controls this panel.
			id={ `${ instancedTabId }-view` }
			tabId={ instancedTabId }
			focusable={ focusable }
			{ ...otherProps }
		>
			{ selectedId === instancedTabId && children }
		</StyledTabPanel>
	);
}

export const TabPanel = forwardRef( UnforwardedTabPanel );
