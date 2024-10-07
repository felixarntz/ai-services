/**
 * External dependencies
 */
import clsx from 'clsx';

/**
 * WordPress dependencies
 */
import { BaseControl } from '@wordpress/components';
import { useInstanceId } from '@wordpress/compose';
import { forwardRef, useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import InputVisibleButton from './input-invisible-button.js';
import './style.scss';

/**
 * Renders a modified version of TextControl, which shows a button to toggle visibility of the input text.
 *
 * By default, the input text is hidden, i.e. the input type is forced to 'password'.
 *
 * The code is almost entirely copied from the original TextControl component implementation.
 *
 * @since 0.1.0
 *
 * @param {Object} props Component props. These are identical to the props of the SensitiveTextControl component, with the
 *                       addition of 'buttonShowLabel', 'buttonHideLabel', and 'HelpContent'.
 * @param {Object} ref   Reference to the component.
 * @return {Component} The component to be rendered.
 */
function UnforwardedSensitiveTextControl( props, ref ) {
	const {
		__nextHasNoMarginBottom,
		__next40pxDefaultSize = false,
		label,
		hideLabelFromVision,
		value,
		help,
		id: idProp,
		className,
		onChange,
		type = 'text',
		buttonShowLabel,
		buttonHideLabel,
		HelpContent,
		...additionalProps
	} = props;
	const id = useInstanceId(
		SensitiveTextControl,
		'inspector-text-control',
		idProp
	);
	const onChangeValue = ( event ) => onChange( event.target.value );

	const [ visible, setVisible ] = useState( false );

	return (
		<BaseControl
			__nextHasNoMarginBottom={ __nextHasNoMarginBottom }
			__associatedWPComponentName="SensitiveTextControl"
			label={ label }
			hideLabelFromVision={ hideLabelFromVision }
			id={ id }
			help={ ! HelpContent ? help : undefined }
			className={ className }
		>
			<div className="ai-services-sensitive-text-control-container">
				<input
					className={ clsx( 'components-text-control__input', {
						'is-next-40px-default-size': __next40pxDefaultSize,
					} ) }
					type={ visible ? type : 'password' }
					id={ id }
					value={ value }
					onChange={ onChangeValue }
					aria-describedby={
						!! help || !! HelpContent ? id + '__help' : undefined
					}
					ref={ ref }
					{ ...additionalProps }
				/>
				<InputVisibleButton
					visible={ visible }
					setVisible={ setVisible }
					showLabel={ buttonShowLabel }
					hideLabel={ buttonHideLabel }
				/>
			</div>
			{ !! HelpContent && (
				<p
					id={ id + '__help' }
					className="components-base-control__help"
				>
					<HelpContent />
				</p>
			) }
		</BaseControl>
	);
}

export const SensitiveTextControl = forwardRef(
	UnforwardedSensitiveTextControl
);

export default SensitiveTextControl;
