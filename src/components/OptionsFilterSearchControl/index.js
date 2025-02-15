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

/**
 * Renders a search control that filters options displayed elsewhere (e.g. in a checkbox list or radio list).
 *
 * @since n.e.x.t
 *
 * @param {Object}   props              The component props.
 * @param {string}   props.label        The label for the search control.
 * @param {Object[]} props.options      The list of options to filter. Each option must contain at least `value` and
 *                                      `label` properties.
 * @param {Function} props.onFilter     The callback function to be called when the options are filtered. It will
 *                                      receive the filtered list of options, or potentially the whole list if no
 *                                      filter is active.
 * @param {string[]} props.searchFields Optional. The fields to search in the options. Defaults to
 *                                      `['label', 'value']`.
 * @return {Component} The component to be rendered.
 */
export default function OptionsFilterSearchControl( props ) {
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
		( newFilterValue ) => {
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
