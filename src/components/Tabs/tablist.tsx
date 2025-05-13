/**
 * External dependencies
 */
import { useStoreState } from '@ariakit/react';
import clsx from 'clsx';
import type { ForwardedRef } from 'react';

/**
 * WordPress dependencies
 */
import warning from '@wordpress/warning';
import { forwardRef, useState } from '@wordpress/element';
import { useMergeRefs } from '@wordpress/compose';
import type { WordPressComponentProps } from '@wordpress/components/build-types/context';

/**
 * Internal dependencies
 */
import { useTabsContext } from './context';
import { StyledTabList } from './styles';
import { useTrackOverflow } from './use-track-overflow';
import type { TabListProps } from './types';

/**
 * Renders a list of tabs.
 *
 * @param props - Component props.
 * @param ref   - Reference to the component.
 * @returns The component to be rendered.
 */
function UnforwardedTabList(
	props: WordPressComponentProps< TabListProps, 'div' >,
	ref: ForwardedRef< HTMLDivElement >
) {
	const { children, ...otherProps } = props;

	const { store } = useTabsContext() ?? {};

	const selectedId = useStoreState( store, 'selectedId' );
	const activeId = useStoreState( store, 'activeId' );
	const selectOnMove = useStoreState( store, 'selectOnMove' );
	const items = useStoreState( store, 'items' );
	const [ parent, setParent ] = useState();
	const refs = useMergeRefs( [ ref, setParent ] );

	// Track overflow to show scroll hints.
	const overflow = useTrackOverflow( parent, {
		first: items?.at( 0 )?.element,
		last: items?.at( -1 )?.element,
	} );

	const onBlur = () => {
		if ( ! selectOnMove ) {
			return;
		}

		// When automatic tab selection is on, make sure that the active tab is up
		// to date with the selected tab when leaving the tablist. This makes sure
		// that the selected tab will receive keyboard focus when tabbing back into
		// the tablist.
		if ( selectedId !== activeId ) {
			store?.setActiveId( selectedId );
		}
	};

	if ( ! store ) {
		warning( '`Tabs.TabList` must be wrapped in a `Tabs` component.' );
		return null;
	}

	return (
		<StyledTabList
			ref={ refs }
			store={ store }
			render={ ( renderProps ) => (
				<div
					{ ...renderProps }
					// Fallback to -1 to prevent browsers from making the tablist
					// tabbable when it is a scrolling container.
					tabIndex={ renderProps.tabIndex ?? -1 }
				/>
			) }
			onBlur={ onBlur }
			data-select-on-move={ selectOnMove ? 'true' : 'false' }
			{ ...otherProps }
			className={ clsx(
				overflow.first && 'is-overflowing-first',
				overflow.last && 'is-overflowing-last',
				otherProps.className
			) }
		>
			{ children }
		</StyledTabList>
	);
}

export const TabList = forwardRef( UnforwardedTabList );
