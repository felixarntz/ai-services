/**
 * WordPress dependencies
 */
import type { WPShortcutKeyCombination } from '@wordpress/keyboard-shortcuts';

export type KeyCombinationProps = {
	keyCombination: WPShortcutKeyCombination;
};

export type ShortcutProps = {
	name: string;
};

export type ShortcutListProps = {
	shortcuts: string[];
};

export type ShortcutSectionProps = {
	shortcuts: string[];
	title?: string;
	className?: string;
};

export type ShortcutCategorySectionProps = {
	title?: string;
	categoryName: string;
};
