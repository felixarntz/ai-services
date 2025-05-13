/**
 * External dependencies
 */
import type { HTMLProps } from 'react';

/**
 * WordPress dependencies
 */
import type { SearchControlProps } from '@wordpress/components/build-types/search-control/types';

type Option = {
	value: string;
	label: string;
	id?: string;
	[ key: string ]: string | undefined;
};

export type OptionsFilterSearchControlProps = Omit<
	SearchControlProps,
	'value' | 'onChange'
> &
	Omit< HTMLProps< HTMLInputElement >, 'value' | 'onChange' > & {
		options: Option[];
		onFilter: ( options: Option[] ) => void;
		searchFields?: string[];
	};
