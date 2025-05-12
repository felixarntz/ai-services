/**
 * WordPress dependencies
 */
import type { ComplementaryAreaProps } from '@wordpress/interface';

export type SidebarProps = Pick<
	ComplementaryAreaProps,
	| 'identifier'
	| 'title'
	| 'children'
	| 'icon'
	| 'header'
	| 'isPinnable'
	| 'isActiveByDefault'
>;
