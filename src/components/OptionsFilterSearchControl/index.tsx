/**
 * External dependencies
 */
import clsx from 'clsx';

/**
 * WordPress dependencies
 */
import { SearchControl } from '@wordpress/components';
import { useCallback, useState } from '@wordpress/element';
import { useDebounce } from '@wordpress/compose';
import { speak } from '@wordpress/a11y';
import { __, _n, sprintf } from '@wordpress/i18n';
import type { WordPressComponentProps } from '@wordpress/components/build-types/context';

/**
 * Internal dependencies
 */
import type { OptionsFilterSearchControlProps } from './types';

/**
 * Renders a search control that filters options displayed elsewhere (e.g. in a checkbox list or radio list).
 *
 * @since 0.5.0
 *
 * @param props - Component props.
 * @returns The component to be rendered.
 */
export default function OptionsFilterSearchControl(
	props: WordPressComponentProps< OptionsFilterSearchControlProps, null >
) {
	const {
		label: labelProp,
		options = [],
		onFilter,
		searchFields,
		className,
		...additionalProps
	} = props;

	const [ filterValue, setFilterValue ] = useState( '' );
	const debouncedSpeak = useDebounce( speak, 500 );

	const setFilter = useCallback(
		( newFilterValue: string ) => {
			const fields = searchFields || [ 'label', 'value' ];
			const newFilteredOptions = options.filter( ( option ) => {
				if ( newFilterValue === '' ) {
					return true;
				}

				for ( const field of fields ) {
					if (
						option[ field ]
							?.toLowerCase()
							.includes( newFilterValue.toLowerCase() )
					) {
						return true;
					}
				}

				return false;
			} );

			setFilterValue( newFilterValue );
			onFilter( newFilteredOptions );

			const resultCount = newFilteredOptions.length;
			const resultsFoundMessage = sprintf(
				/* translators: %d: number of results */
				_n(
					'%d result found.',
					'%d results found.',
					resultCount,
					'ai-services'
				),
				resultCount
			);

			debouncedSpeak( resultsFoundMessage, 'assertive' );
		},
		[ options, onFilter, searchFields, debouncedSpeak ]
	);

	return (
		<SearchControl
			label={ labelProp || __( 'Search options', 'ai-services' ) }
			className={ clsx(
				'components-options-filter-search-control',
				className
			) }
			value={ filterValue }
			onChange={ setFilter }
			{ ...additionalProps }
		/>
	);
}
