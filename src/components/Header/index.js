/**
 * WordPress dependencies
 */
import { __unstableMotion as motion } from '@wordpress/components';

/**
 * Internal dependencies
 */
import './style.scss';

const toolbarVariations = {
	distractionFreeDisabled: { y: '-50px' },
	distractionFreeHover: { y: 0 },
	distractionFreeHidden: { y: '-50px' },
	visible: { y: 0 },
	hidden: { y: 0 },
};

export default function Header() {
	return (
		<div className="wpoopple-interface-header">
			<motion.div
				variants={ toolbarVariations }
				className="wpoopple-interface-header__toolbar"
				transition={ { type: 'tween' } }
			>
				<div>Title</div>
			</motion.div>
			<motion.div
				variants={ toolbarVariations }
				transition={ { type: 'tween' } }
				className="editor-header__settings"
			>
				<div>Save</div>
			</motion.div>
		</div>
	);
}
