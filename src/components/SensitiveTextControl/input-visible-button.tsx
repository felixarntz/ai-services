/**
 * WordPress dependencies
 */
import { Button, Dashicon } from '@wordpress/components';
import { _x } from '@wordpress/i18n';
import type { WordPressComponentProps } from '@wordpress/components/build-types/context';

/**
 * Internal dependencies
 */
import type { InputVisibleButtonProps } from './types';

/**
 * Renders a wrapper for the actions within the header of the application.
 *
 * Any children passed to this component will be rendered inside the header actions area.
 *
 * @since 0.1.0
 *
 * @param props - Component props.
 * @returns The component to be rendered.
 */
export default function InputVisibleButton(
	props: WordPressComponentProps< InputVisibleButtonProps, null >
) {
	const { visible, setVisible, showLabel, hideLabel } = props;

	return (
		<Button
			variant="secondary"
			className="ai-services-input-visible-button"
			onClick={ () => setVisible( ! visible ) }
			aria-label={ visible ? hideLabel : showLabel }
			__next40pxDefaultSize
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
