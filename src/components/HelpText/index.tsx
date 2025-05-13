/**
 * External dependencies
 */
import clsx from 'clsx';

/**
 * WordPress dependencies
 */
import type { WordPressComponentProps } from '@wordpress/components/build-types/context';

/**
 * Internal dependencies
 */
import type { HelpTextProps } from './types';
import './style.scss';

/**
 * Renders a help text paragraph.
 *
 * @since 0.5.0
 *
 * @param props - Component props.
 * @returns The component to be rendered.
 */
export default function HelpText(
	props: WordPressComponentProps< HelpTextProps, 'p' >
) {
	const { id, className, children, ...additionalProps } = props;

	return (
		<p
			id={ id }
			className={ clsx(
				'components-base-control__help',
				'components-base-control__help-text',
				className
			) }
			{ ...additionalProps }
		>
			{ children }
		</p>
	);
}
