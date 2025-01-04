/**
 * External dependencies
 */
import { Modal } from '@ai-services/interface';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import { useMemo } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { store as playgroundStore } from '../../store';
import './style.scss';

/**
 * Renders the modal displaying the raw data for a message.
 *
 * @since n.e.x.t
 *
 * @return {Component} The component to be rendered.
 */
export default function RawDataModal() {
	const activeRawData = useSelect( ( select ) =>
		select( playgroundStore ).getActiveRawData()
	);

	const activeRawDataJson = useMemo( () => {
		return JSON.stringify( activeRawData, null, 2 );
	}, [ activeRawData ] );

	return (
		<Modal
			identifier="raw-message-data"
			title={ __( 'Raw message data', 'ai-services' ) }
			className="ai-services-playground__raw-data-modal"
		>
			<textarea
				className="ai-services-playground__raw-data-textarea code"
				aria-label={ __(
					'Raw data for the selected message',
					'ai-services'
				) }
				value={ activeRawDataJson }
				rows="14"
				readOnly
			/>
		</Modal>
	);
}
