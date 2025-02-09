/**
 * WordPress dependencies
 */
import { BaseControl, VisuallyHidden } from '@wordpress/components';

/**
 * Internal dependencies
 */
import './style.scss';

/**
 * Renders a fieldset-based control.
 *
 * @since n.e.x.t
 *
 * @param {Object} props The component props. Matches the props supported by WordPress Core's `BaseControl` component.
 * @return {Component} The component to be rendered.
 */
export default function FieldsetBaseControl( props ) {
	const { label, hideLabelFromVision, children, ...additionalProps } = props;

	return (
		<BaseControl { ...additionalProps }>
			<fieldset className="components-base-control__fieldset">
				{ label &&
					( hideLabelFromVision ? (
						<VisuallyHidden as="legend">{ label }</VisuallyHidden>
					) : (
						<legend className="components-base-control__legend">
							{ label }
						</legend>
					) ) }
				{ children }
			</fieldset>
		</BaseControl>
	);
}
