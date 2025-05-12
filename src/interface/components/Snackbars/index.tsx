/**
 * External dependencies
 */
import type { MouseEventHandler } from 'react';

/**
 * WordPress dependencies
 */
import { SnackbarList } from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { useMemo } from '@wordpress/element';
import { store as noticesStore } from '@wordpress/notices';
import type { WPNotice } from '@wordpress/notices/build-types/store/selectors';
import type { SnackbarProps as SingleSnackbarProps } from '@wordpress/components/build-types/snackbar/types';

/**
 * Internal dependencies
 */
import './style.scss';

// This is the same as the entries of the `SnackbarListProps['notices']` array.
type SnackbarProps = Omit< SingleSnackbarProps, 'children' > & {
	id: string;
	content: string;
};

/**
 * Prepares a notice from the 'core/notices' store to be compatible with the `SnackbarList` component.
 *
 * This workaround is necessary because unfortunately their declared types are not entirely compatible.
 *
 * @param notice - The notice to be prepared.
 * @returns Notice to pass to the `SnackbarList` component within the array of notices.
 */
function prepareNoticeForSnackbarProps( notice: WPNotice ): SnackbarProps {
	return {
		...notice,
		// Convert incompatible action type to only allow `url` _or_ `onClick`.
		actions: notice.actions
			.map( ( action ) => {
				if ( action.url ) {
					return {
						label: action.label,
						url: action.url,
					};
				}
				if ( action.onClick ) {
					return {
						label: action.label,
						onClick:
							action.onClick as MouseEventHandler< HTMLButtonElement >,
					};
				}
				return {
					label: action.label,
					url: '',
				};
			} )
			.filter( ( action ) => action.url || action.onClick ),
	};
}

// Last three notices. Slices from the tail end of the list.
const MAX_VISIBLE_NOTICES = -3;

/**
 * Renders the list of the latest 3 snackbar notices in the store.
 *
 * This only includes notices added with the type 'snackbar'.
 *
 * @since 0.1.0
 *
 * @returns The component to be rendered.
 */
export default function Snackbars() {
	const notices = useSelect(
		( select ) => select( noticesStore ).getNotices(),
		[]
	);
	const { removeNotice } = useDispatch( noticesStore );

	const snackbarNotices = useMemo( () => {
		return notices
			.filter( ( { type } ) => type === 'snackbar' )
			.map( prepareNoticeForSnackbarProps )
			.slice( MAX_VISIBLE_NOTICES );
	}, [ notices ] );

	return (
		<SnackbarList
			notices={ snackbarNotices }
			className="ais-notices__snackbar"
			onRemove={ removeNotice }
		/>
	);
}
