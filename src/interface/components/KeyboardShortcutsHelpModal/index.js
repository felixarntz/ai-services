/**
 * External dependencies
 */
import clsx from 'clsx';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
	useShortcut,
	store as keyboardShortcutsStore,
} from '@wordpress/keyboard-shortcuts';
import { useDispatch, useSelect } from '@wordpress/data';
import { displayShortcutList, shortcutAriaLabel } from '@wordpress/keycodes';

/**
 * Internal dependencies
 */
import { store as interfaceStore } from '../../store';
import Modal from '../Modal';

/**
 * Renders a shortcut key combination.
 *
 * @since 0.1.0
 *
 * @param {Object} props                Component props.
 * @param {Object} props.keyCombination Object containing 'modifier' and 'character' properties (both strings).
 * @return {Component} The component to be rendered.
 */
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

KeyCombination.propTypes = {
	keyCombination: PropTypes.shape( {
		modifier: PropTypes.string,
		character: PropTypes.string,
	} ).isRequired,
};

/**
 * Renders a shortcut.
 *
 * @since 0.1.0
 *
 * @param {Object} props      Component props.
 * @param {string} props.name Identifier that the shortcut is registered under in the store.
 * @return {Component} The component to be rendered.
 */
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

Shortcut.propTypes = {
	name: PropTypes.string.isRequired,
};

/**
 * Renders a list of shortcuts.
 *
 * @since 0.1.0
 *
 * @param {Object}   props           Component props.
 * @param {string[]} props.shortcuts List of shortcut identifiers.
 * @return {Component} The component to be rendered.
 */
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

ShortcutList.propTypes = {
	shortcuts: PropTypes.arrayOf( PropTypes.string ).isRequired,
};

/**
 * Renders a section for a group of shortcuts.
 *
 * @since 0.1.0
 *
 * @param {Object}   props           Component props.
 * @param {string[]} props.shortcuts List of shortcut identifiers.
 * @param {?string}  props.title     Title for the section.
 * @param {?string}  props.className Class name to add to the section.
 * @return {Component} The component to be rendered.
 */
function ShortcutSection( { shortcuts, title, className } ) {
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

ShortcutSection.propTypes = {
	shortcuts: PropTypes.arrayOf( PropTypes.string ).isRequired,
	title: PropTypes.string,
	className: PropTypes.string,
};

/**
 * Renders a section for a category of shortcuts.
 *
 * @since 0.1.0
 *
 * @param {Object}  props              Component props.
 * @param {string}  props.categoryName Identifier of the shortcut category in the store.
 * @param {?string} props.title        Title for the section.
 * @return {Component} The component to be rendered.
 */
function ShortcutCategorySection( { title, categoryName } ) {
	const categoryShortcuts = useSelect(
		( select ) => {
			return select( keyboardShortcutsStore ).getCategoryShortcuts(
				categoryName
			);
		},
		[ categoryName ]
	);

	return <ShortcutSection title={ title } shortcuts={ categoryShortcuts } />;
}

ShortcutCategorySection.propTypes = {
	title: PropTypes.string,
	categoryName: PropTypes.string.isRequired,
};

/**
 * Renders the modal displaying the available keyboard shortcuts.
 *
 * @since 0.1.0
 *
 * @return {Component} The component to be rendered.
 */
export default function KeyboardShortcutsHelpModal() {
	const { toggleModal } = useDispatch( interfaceStore );

	useShortcut( 'wp-starter-plugin/keyboard-shortcuts', () =>
		toggleModal( 'keyboard-shortcuts-help' )
	);

	return (
		<Modal
			identifier="keyboard-shortcuts-help"
			title={ __( 'Keyboard shortcuts', 'wp-starter-plugin' ) }
			className="editor-keyboard-shortcut-help-modal"
		>
			<ShortcutCategorySection categoryName="main" />
			<ShortcutCategorySection
				title={ __( 'Global shortcuts', 'wp-starter-plugin' ) }
				categoryName="global"
			/>
		</Modal>
	);
}
