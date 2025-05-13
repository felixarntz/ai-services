/**
 * External dependencies
 */
import type { ReactNode } from 'react';

/**
 * WordPress dependencies
 */
import type { InterfaceSkeletonProps } from '@wordpress/interface';

export type InterfaceProps = {
	className?: string;
	labels: InterfaceSkeletonProps[ 'labels' ];
	children: ReactNode;
};
