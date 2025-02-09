/**
 * External dependencies
 */
import clsx from 'clsx';

/**
 * WordPress dependencies
 */
import { SearchControl } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { useDebounce } from '@wordpress/compose';
import { speak } from '@wordpress/a11y';
import { __, _n, sprintf } from '@wordpress/i18n';

/**
 * Renders a search control that filters options displayed elsewhere (e.g. in a checkbox list or radio list).
 *
 * @since n.e.x.t
 *
 * @param {Object}   props          The component props.
 * @param {string}   props.label    The label for the search control.
 * @param {Object[]} props.options  The list of options to filter. Each option must contain at least `value` and
 *                                  `label` properties.
 * @param {Function} props.onFilter The callback function to be called when the options are filtered. It will receive
 *                                  the filtered list of options, or potentially the whole list if no filter is active.
 * @return {Component} The component to be rendered.
 */
export default function OptionsFilterSearchControl( props ) {
	const {
		label: labelProp,
		options = [],
		onFilter,
		className,
		...additionalProps
	} = props;

	const [ filterValue, setFilterValue ] = useState( '' );
	const debouncedSpeak = useDebounce( speak, 500 );

	const setFilter = ( newFilterValue ) => {
		const newFilteredOptions = options.filter( ( option ) => {
			if ( newFilterValue === '' ) {
				return true;
			}
			if (
				option.label
					?.toLowerCase()
					.includes( newFilterValue.toLowerCase() )
			) {
				return true;
			}
			return option.value
				?.toLowerCase()
				.includes( newFilterValue.toLowerCase() );
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
	};

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
