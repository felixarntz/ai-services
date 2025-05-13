import type { WPKeycodeModifier } from '@wordpress/keycodes';

type WPShortcutKeyCombination = {
	character: string;
	modifier?: WPKeycodeModifier;
};

type WPShortcutConfig = {
	name: string;
	category: string;
	description: string;
	keyCombination: WPShortcutKeyCombination;
	aliases?: WPShortcutKeyCombination[];
};

/**
 * Registers a new keyboard shortcut.
 *
 * @param config - Shortcut config.
 * @returns Action creator.
 */
export function registerShortcut( shortcutConfig: WPShortcutConfig ): void;

/**
 * Returns an action object used to unregister a keyboard shortcut.
 *
 * @param name - Shortcut name.
 * @returns Action creator.
 */
export function unregisterShortcut( name: string ): void;
