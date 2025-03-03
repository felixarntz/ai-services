/**
 * External dependencies
 */
import clsx from 'clsx';

/**
 * WordPress dependencies
 */
import { CheckboxControl } from '@wordpress/components';
import { useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import FieldsetBaseControl from '../FieldsetBaseControl';
import OptionsFilterSearchControl from '../OptionsFilterSearchControl';
import './style.scss';

/**
 * Renders a multi-checkbox control.
 *
 * To group the checkboxes properly, they are wrapped in a fieldset element, where the overall label is used as legend.
 * Optionally, a search input can be rendered to filter which checkboxes are displayed.
 *
 * @since 0.5.0
 *
 * @param {Object}   props                     The component props.
 * @param {string}   props.label               The label for the checkbox list, used as legend.
 * @param {boolean}  props.hideLabelFromVision Whether the label should be visually hidden.
 * @param {string[]} props.value               The value of the checkbox list, which needs to be an array of strings,
 *                                             referring to which values from the given options are selected.
 * @param {Object[]} props.options             The list of options to be displayed as checkboxes. Each option must
 *                                             contain at least `value` and `label` properties. `id` is also supported.
 * @param {Function} props.onChange            The callback function to be called when the selected list changes. It
 *                                             will receive the entire list of selected values as an array of strings.
 *                                             Either this prop or `onToggle` must be provided, but not both.
 * @param {Function} props.onToggle            The callback function to be called when a single checkbox is toggled. It
 *                                             will receive the value of the checkbox that was toggled. Either this prop
 *                                             or `onChange` must be provided, but not both.
 * @param {boolean}  props.showFilter          Whether to show a search input to filter which checkboxes are displayed.
 * @param {string}   props.searchLabel         The label for the search input. Only relevant if `showFilter` is true.
 * @param {string}   props.id                  The ID for the container element.
 * @param {string}   props.className           The class name for container element.
 * @param {string}   props.help                The help text to be displayed below the checkbox list.
 * @return {Component} The component to be rendered.
 */
export default function MultiCheckboxControl( props ) {
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

	const onToggle = ( value ) => {
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
				tabIndex="0"
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
