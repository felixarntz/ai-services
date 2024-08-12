/**
 * External dependencies
 */
import clsx from 'clsx';

/**
 * WordPress dependencies
 */
import { Modal } from '@wordpress/components';
import { Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
	useShortcut,
	store as keyboardShortcutsStore,
} from '@wordpress/keyboard-shortcuts';
import { useDispatch, useSelect } from '@wordpress/data';
import { displayShortcutList, shortcutAriaLabel } from '@wordpress/keycodes';
import { store as interfaceStore } from '@wordpress/interface';

function KeyCombination( { keyCombination } ) {
	const shortcut = keyCombination.modifier
		? displayShortcutList[ keyCombination.modifier ](
				keyCombination.character
		  )
		: keyCombination.character;
	const ariaLabel = keyCombination.modifier
		? shortcutAriaLabel[ keyCombination.modifier ](
				keyCombination.character
		  )
		: keyCombination.character;

	return (
		<kbd
			className="editor-keyboard-shortcut-help-modal__shortcut-key-combination"
			aria-label={ ariaLabel }
		>
			{ ( Array.isArray( shortcut ) ? shortcut : [ shortcut ] ).map(
				( character, index ) => {
					if ( character === '+' ) {
						return <Fragment key={ index }>{ character }</Fragment>;
					}

					return (
						<kbd
							key={ index }
							className="editor-keyboard-shortcut-help-modal__shortcut-key"
						>
							{ character }
						</kbd>
					);
				}
			) }
		</kbd>
	);
}

function Shortcut( { name } ) {
	const { keyCombination, description, aliases } = useSelect(
		( select ) => {
			const {
				getShortcutKeyCombination,
				getShortcutDescription,
				getShortcutAliases,
			} = select( keyboardShortcutsStore );

			return {
				keyCombination: getShortcutKeyCombination( name ),
				aliases: getShortcutAliases( name ),
				description: getShortcutDescription( name ),
			};
		},
		[ name ]
	);

	if ( ! keyCombination ) {
		return null;
	}

	return (
		<>
			<div className="editor-keyboard-shortcut-help-modal__shortcut-description">
				{ description }
			</div>
			<div className="editor-keyboard-shortcut-help-modal__shortcut-term">
				<KeyCombination keyCombination={ keyCombination } />
				{ aliases.map( ( alias, index ) => (
					<KeyCombination keyCombination={ alias } key={ index } />
				) ) }
			</div>
		</>
	);
}

function ShortcutList( { shortcuts } ) {
	return (
		/*
		 * Disable reason: The `list` ARIA role is redundant but
		 * Safari+VoiceOver won't announce the list otherwise.
		 */
		/* eslint-disable jsx-a11y/no-redundant-roles */
		<ul
			className="editor-keyboard-shortcut-help-modal__shortcut-list"
			role="list"
		>
			{ shortcuts.map( ( shortcut, index ) => (
				<li
					className="editor-keyboard-shortcut-help-modal__shortcut"
					key={ index }
				>
					<Shortcut name={ shortcut } />
				</li>
			) ) }
		</ul>
		/* eslint-enable jsx-a11y/no-redundant-roles */
	);
}

function ShortcutSection( { title, shortcuts, className } ) {
	return (
		<section
			className={ clsx(
				'editor-keyboard-shortcut-help-modal__section',
				className
			) }
		>
			{ !! title && (
				<h2 className="editor-keyboard-shortcut-help-modal__section-title">
					{ title }
				</h2>
			) }
			<ShortcutList shortcuts={ shortcuts } />
		</section>
	);
}

function ShortcutCategorySection( {
	title,
	categoryName,
	additionalShortcuts = [],
} ) {
	const categoryShortcuts = useSelect(
		( select ) => {
			return select( keyboardShortcutsStore ).getCategoryShortcuts(
				categoryName
			);
		},
		[ categoryName ]
	);

	return (
		<ShortcutSection
			title={ title }
			shortcuts={ categoryShortcuts.concat( additionalShortcuts ) }
		/>
	);
}

export default function KeyboardShortcutsHelpModal() {
	const isModalActive = useSelect(
		( select ) =>
			select( interfaceStore ).isModalActive(
				'wp-oop-plugin-lib-example/keyboard-shortcuts-help'
			),
		[]
	);
	const { openModal, closeModal } = useDispatch( interfaceStore );
	const toggleModal = () => {
		if ( isModalActive ) {
			closeModal();
		} else {
			openModal( 'wp-oop-plugin-lib-example/keyboard-shortcuts-help' );
		}
	};
	useShortcut( 'wp-oop-plugin-lib-example/keyboard-shortcuts', toggleModal );

	if ( ! isModalActive ) {
		return null;
	}

	return (
		<Modal
			className="editor-keyboard-shortcut-help-modal"
			title={ __( 'Keyboard shortcuts', 'wp-oop-plugin-lib-example' ) }
			closeButtonLabel={ __( 'Close', 'wp-oop-plugin-lib-example' ) }
			onRequestClose={ toggleModal }
		>
			<ShortcutCategorySection categoryName="main" />
			<ShortcutCategorySection
				title={ __( 'Global shortcuts', 'wp-oop-plugin-lib-example' ) }
				categoryName="global"
			/>
		</Modal>
	);
}
