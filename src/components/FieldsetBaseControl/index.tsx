/**
 * WordPress dependencies
 */
import { BaseControl, VisuallyHidden } from '@wordpress/components';
import type { WordPressComponentProps } from '@wordpress/components/build-types/context';

/**
 * Internal dependencies
 */
import type { FieldsetBaseControlProps } from './types';
import './style.scss';

/**
 * Renders a fieldset-based control.
 *
 * @since 0.5.0
 *
 * @param props - Component props.
 * @returns The component to be rendered.
 */
export default function FieldsetBaseControl(
	props: WordPressComponentProps< FieldsetBaseControlProps, null >
) {
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
