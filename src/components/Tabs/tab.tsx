/**
 * External dependencies
 */
import type { ForwardedRef } from 'react';

/**
 * WordPress dependencies
 */
import { forwardRef } from '@wordpress/element';
import { chevronRight } from '@wordpress/icons';
import warning from '@wordpress/warning';
import type { WordPressComponentProps } from '@wordpress/components/build-types/context';

/**
 * Internal dependencies
 */
import { useTabsContext } from './context';
import { StyledTab, StyledTabChildren, StyledTabChevron } from './styles';
import type { TabProps } from './types';

/**
 * Renders a tab.
 *
 * @param props - Component props.
 * @param ref   - Reference to the component.
 * @returns The component to be rendered.
 */
function UnforwardedTab(
	props: Omit< WordPressComponentProps< TabProps, 'button', false >, 'id' >,
	ref: ForwardedRef< HTMLButtonElement >
) {
	const { children, tabId, disabled, render, ...otherProps } = props;

	const { store, instanceId } = useTabsContext() ?? {};

	if ( ! store ) {
		warning( '`Tabs.Tab` must be wrapped in a `Tabs` component.' );
		return null;
	}

	const instancedTabId = `${ instanceId }-${ tabId }`;

	return (
		<StyledTab
			ref={ ref }
			store={ store }
			id={ instancedTabId }
			disabled={ disabled }
			render={ render }
			{ ...otherProps }
		>
			<StyledTabChildren>{ children }</StyledTabChildren>
			<StyledTabChevron icon={ chevronRight } />
		</StyledTab>
	);
}

export const Tab = forwardRef( UnforwardedTab );
