/**
 * Internal dependencies
 */
import Interface from '../Interface';
import './style.scss';

export default function Footer( { children } ) {
	return (
		<Interface.Footer>
			<div className="wpoopple-footer">{ children }</div>
		</Interface.Footer>
	);
}
