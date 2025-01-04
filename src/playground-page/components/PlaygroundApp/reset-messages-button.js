/**
 * WordPress dependencies
 */
import {
	Button,
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalConfirmDialog as ConfirmDialog,
} from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { reset } from '@wordpress/icons';

/**
 * Internal dependencies
 */
import { store as playgroundStore } from '../../store';

/**
 * Renders the reset messages button.
 *
 * @since n.e.x.t
 *
 * @return {Component} The component to be rendered.
 */
export default function ResetMessagesButton() {
	const [ isConfirmDialogVisible, setIsConfirmDialogVisible ] =
		useState( false );

	const messages = useSelect( ( select ) =>
		select( playgroundStore ).getMessages()
	);
	const disabled = messages.length === 0;

	const { resetMessages } = useDispatch( playgroundStore );

	return (
		<>
			<Button
				icon={ reset }
				label={ __( 'Reset messages', 'ai-services' ) }
				className="is-compact"
				disabled={ disabled }
				onClick={ () => {
					if ( disabled ) {
						return;
					}
					setIsConfirmDialogVisible( true );
				} }
			/>
			{ isConfirmDialogVisible && (
				<ConfirmDialog
					isOpen
					onConfirm={ () => {
						resetMessages();
						setIsConfirmDialogVisible( false );
					} }
					onCancel={ () => setIsConfirmDialogVisible( false ) }
					confirmButtonText={ __( 'Delete' ) }
					size="medium"
				>
					{ __(
						'Are you sure you want to reset all messages? You will not be able to recover them.',
						'ai-services'
					) }
				</ConfirmDialog>
			) }
		</>
	);
}
