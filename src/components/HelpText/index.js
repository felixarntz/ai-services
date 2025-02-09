/**
 * External dependencies
 */
import clsx from 'clsx';

/**
 * Internal dependencies
 */
import './style.scss';

/**
 * Renders a help text paragraph.
 *
 * @since n.e.x.t
 *
 * @param {Object}  props           The component props.
 * @param {string}  props.id        The ID for the help text.
 * @param {string}  props.className The class name for the help text.
 * @param {Element} props.children  The help text content.
 * @return {Component} The component to be rendered.
 */
export default function HelpText( {
	id,
	className,
	children,
	...additionalProps
} ) {
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
