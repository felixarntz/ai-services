import { WPShortcutKeyCombination } from './actions';

type FormattingMethod = 'display' | 'raw' | 'ariaLabel';

/**
 * Returns the main key combination for a given shortcut name.
 *
 * @param state - Global state.
 * @param name  - Shortcut name.
 * @returns Key combination.
 */
export function getShortcutKeyCombination(
	state: Object,
	name: string
): WPShortcutKeyCombination | null;

/**
 * Returns a string representing the main key combination for a given shortcut name.
 *
 * @param state          - Global state.
 * @param name           - Shortcut name.
 * @param representation - Type of representation (display, raw, ariaLabel).
 * @returns Shortcut representation.
 */
export function getShortcutRepresentation(
	state: Object,
	name: string,
	representation?: FormattingMethod
): string | null;

/**
 * Returns the shortcut description given its name.
 *
 * @param state - Global state.
 * @param name  - Shortcut name.
 * @returns Shortcut description.
 */
export function getShortcutDescription(
	state: Object,
	name: string
): string | null;

/**
 * Returns the aliases for a given shortcut name.
 *
 * @param state - Global state.
 * @param name  - Shortcut name.
 * @returns Key combinations.
 */
export function getShortcutAliases(
	state: Object,
	name: string
): WPShortcutKeyCombination[];

/**
 * Returns the shortcuts that include aliases for a given shortcut name.
 *
 * @param state - Global state.
 * @param name  - Shortcut name.
 * @returns Key combinations.
 */
export function getAllShortcutKeyCombinations(
	state: Object,
	name: string
): WPShortcutKeyCombination[];

/**
 * Returns the raw representation of all the keyboard combinations of a given shortcut name.
 *
 * @param state - Global state.
 * @param name  - Shortcut name.
 * @returns Shortcuts.
 */
export function getAllShortcutRawKeyCombinations(
	state: Object,
	name: string
): string[];

/**
 * Returns the shortcut names list for a given category name.
 *
 * @param state - Global state.
 * @param name  Category name.
 * @returns Shortcut names.
 */
export function getCategoryShortcuts(
	state: Object,
	categoryName: string
): string[];
