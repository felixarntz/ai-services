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
				className="wpoopple-notices__pinned"
			/>
			<NoticeList
				notices={ dismissibleNotices }
				className="wpoopple-notices__dismissible"
				onRemove={ removeNotice }
			/>
		</>
	);
}
