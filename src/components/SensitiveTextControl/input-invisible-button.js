/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { Button, Dashicon } from '@wordpress/components';
import { _x } from '@wordpress/i18n';

/**
 * Renders a wrapper for the actions within the header of the application.
 *
 * Any children passed to this component will be rendered inside the header actions area.
 *
 * @since 0.1.0
 *
 * @param {Object}   props            Component props.
 * @param {boolean}  props.visible    Whether the input is visible.
 * @param {Function} props.setVisible Function to toggle the input visibility. Must accept a boolean.
 * @param {string}   props.showLabel  Label for the show action.
 * @param {string}   props.hideLabel  Label for the hide action.
 * @return {Component} The component to be rendered.
 */
function InputVisibleButton( { visible, setVisible, showLabel, hideLabel } ) {
	return (
		<Button
			variant="secondary"
			className="ai-services-input-visible-button"
			onClick={ () => setVisible( ! visible ) }
			aria-label={ visible ? hideLabel : showLabel }
		>
			<Dashicon
				icon={ visible ? 'hidden' : 'visibility' }
				aria-hidden="true"
			/>
			<span className="text">
				{ visible
					? _x( 'Hide', 'action', 'ai-services' )
					: _x( 'Show', 'action', 'ai-services' ) }
			</span>
		</Button>
	);
}

InputVisibleButton.propTypes = {
	visible: PropTypes.bool.isRequired,
	setVisible: PropTypes.func.isRequired,
	showLabel: PropTypes.string,
	hideLabel: PropTypes.string,
};

export default InputVisibleButton;
