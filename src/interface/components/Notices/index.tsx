/**
 * External dependencies
 */
import type { MouseEventHandler } from 'react';

/**
 * WordPress dependencies
 */
import { NoticeList } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import { useMemo } from '@wordpress/element';
import { store as noticesStore } from '@wordpress/notices';
import type { WPNotice } from '@wordpress/notices/build-types/store/selectors';
import type { NoticeProps as SingleNoticeProps } from '@wordpress/components/build-types/notice/types';

/**
 * Internal dependencies
 */
import './style.scss';

// This is the same as the entries of the `NoticeListProps['notices']` array.
type NoticeProps = Omit< SingleNoticeProps, 'children' > & {
	id: string;
	content: string;
};

/**
 * Prepares a notice from the 'core/notices' store to be compatible with the `NoticeList` component.
 *
 * This workaround is necessary because unfortunately their declared types are not entirely compatible.
 *
 * @param notice - The notice to be prepared.
 * @returns Notice to pass to the `NoticeList` component within the array of notices.
 */
function prepareNoticeForNoticeProps( notice: WPNotice ): NoticeProps {
	const { __unstableHTML, ...restProps } = notice;

	return {
		...restProps,
		// Convert string to the  union type of actual status strings.
		status: notice.status as NoticeProps[ 'status' ],
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

/**
 * Renders the list of all regular notices in the store.
 *
 * This only includes notices added with the type 'default'.
 * Non-dismissible notices are rendered in a separate list from dismissible notices.
 *
 * @since 0.1.0
 *
 * @returns The component to be rendered.
 */
export default function Notices() {
	const { notices } = useSelect(
		( select ) => ( {
			notices: select( noticesStore ).getNotices(),
		} ),
		[]
	);
	const { removeNotice } = useDispatch( noticesStore );

	const { dismissibleNotices, nonDismissibleNotices } = useMemo( () => {
		return {
			dismissibleNotices: notices
				.filter(
					( { isDismissible, type } ) =>
						isDismissible && type === 'default'
				)
				.map( prepareNoticeForNoticeProps ),
			nonDismissibleNotices: notices
				.filter(
					( { isDismissible, type } ) =>
						! isDismissible && type === 'default'
				)
				.map( prepareNoticeForNoticeProps ),
		};
	}, [ notices ] );

	return (
		<>
			<NoticeList
				notices={ nonDismissibleNotices }
				className="ais-notices__pinned"
			/>
			<NoticeList
				notices={ dismissibleNotices }
				className="ais-notices__dismissible"
				onRemove={ removeNotice }
			/>
		</>
	);
}
