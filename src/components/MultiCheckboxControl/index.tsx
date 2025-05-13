/**
 * External dependencies
 */
import clsx from 'clsx';

/**
 * WordPress dependencies
 */
import { CheckboxControl } from '@wordpress/components';
import { useState } from '@wordpress/element';
import type { WordPressComponentProps } from '@wordpress/components/build-types/context';

/**
 * Internal dependencies
 */
import FieldsetBaseControl from '../FieldsetBaseControl';
import OptionsFilterSearchControl from '../OptionsFilterSearchControl';
import type { MultiCheckboxControlProps } from './types';
import './style.scss';

/**
 * Renders a multi-checkbox control.
 *
 * To group the checkboxes properly, they are wrapped in a fieldset element, where the overall label is used as legend.
 * Optionally, a search input can be rendered to filter which checkboxes are displayed.
 *
 * @since 0.5.0
 *
 * @param props - Component props.
 * @returns The component to be rendered.
 */
export default function MultiCheckboxControl(
	props: WordPressComponentProps< MultiCheckboxControlProps, null >
) {
	const {
		__nextHasNoMarginBottom,
		label: labelProp,
		hideLabelFromVision,
		help,
		id: idProp,
		className,
		value: valueProp = [],
		options = [],
		onChange,
		onToggle: onToggleProp,
		showFilter = false,
		searchLabel,
	} = props;

	const [ filteredOptions, setFilteredOptions ] = useState( options );

	const optionsToRender = showFilter ? filteredOptions : options;

	const onToggle = ( value: string ) => {
		if ( onToggleProp ) {
			onToggleProp( value );
			return;
		}

		if ( onChange ) {
			const newValue = valueProp.includes( value )
				? valueProp.filter( ( v ) => v !== value )
				: [ ...valueProp, value ];

			onChange( newValue );
		}
	};

	return (
		<FieldsetBaseControl
			label={ labelProp }
			hideLabelFromVision={ hideLabelFromVision }
			className={ clsx( 'components-multi-checkbox-control', className ) }
			id={ idProp }
			help={ help }
			__nextHasNoMarginBottom={ __nextHasNoMarginBottom }
		>
			{ showFilter && (
				<OptionsFilterSearchControl
					label={ searchLabel }
					className="components-multi-checkbox-control__search-control"
					options={ options }
					onFilter={ setFilteredOptions }
					__nextHasNoMarginBottom={ __nextHasNoMarginBottom }
				/>
			) }
			<div
				className="components-multi-checkbox-control__checkbox-list"
				tabIndex={ 0 }
			>
				{ optionsToRender.map( ( { id, label, value }, index ) => (
					<CheckboxControl
						key={ id || `${ label }-${ value }-${ index }` }
						className="components-multi-checkbox-control__checkbox-control"
						checked={ valueProp.indexOf( value ) !== -1 }
						onChange={ () => {
							onToggle( value );
						} }
						label={ label }
						__nextHasNoMarginBottom={ __nextHasNoMarginBottom }
					/>
				) ) }
			</div>
		</FieldsetBaseControl>
	);
}
