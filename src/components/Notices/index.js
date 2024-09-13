/**
 * WordPress dependencies
 */
import { NoticeList } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';

/**
 * Internal dependencies
 */
import './style.scss';

/**
 * Renders the list of all regular notices in the store.
 *
 * This only includes notices added with the type 'default'.
 * Non-dismissible notices are rendered in a separate list from dismissible notices.
 *
 * @since n.e.x.t
 *
 * @return {Component} The component to be rendered.
 */
export default function Notices() {
	const { notices } = useSelect(
		( select ) => ( {
			notices: select( noticesStore ).getNotices(),
		} ),
		[]
	);
	const { removeNotice } = useDispatch( noticesStore );
	const dismissibleNotices = notices.filter(
		( { isDismissible, type } ) => isDismissible && type === 'default'
	);
	const nonDismissibleNotices = notices.filter(
		( { isDismissible, type } ) => ! isDismissible && type === 'default'
	);

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
