/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { SVG, Circle, G, Path } from '@wordpress/components';

/**
 * Renders the plugin icon.
 *
 * @since n.e.x.t
 *
 * @param {Object}  props             The component props.
 * @param {number}  props.size        The size of the icon.
 * @param {boolean} props.hideCircle  Whether to hide the circle.
 * @param {boolean} props.invertColor Whether to invert the colors of the circle and main icon.
 * @return {Component} The component to be rendered.
 */
function PluginIcon( { size, hideCircle, invertColor, ...extraProps } ) {
	const circleColor = invertColor ? '#ffffff' : '#0174aa';
	const iconColor = invertColor ? '#0174aa' : '#ffffff';

	return (
		<SVG
			xmlns="http://www.w3.org/2000/svg"
			viewBox="0 0 512 512"
			fill="#ff0000"
			width={ size }
			height={ size }
			{ ...extraProps }
		>
			{ ! hideCircle && (
				<Circle cx="256" cy="256" r="256" fill={ circleColor } />
			) }
			<G
				transform="translate(0, 500) scale(0.05,-0.05)"
				fill={ iconColor }
				stroke="none"
			>
				<Path
					d="M3640 8640 c-206 -39 -370 -203 -401 -402 -27 -177 85 -384 259 -476
        l62 -33 0 -260 0 -259 -228 0 c-256 0 -299 -7 -420 -66 -87 -42 -255 -166
        -332 -245 -68 -70 -122 -160 -158 -264 l-27 -80 3 -335 c2 -184 0 -394 -4
        -467 l-6 -131 -186 -4 c-213 -3 -234 -10 -308 -90 -80 -86 -79 -80 -79 -628 0
        -445 1 -488 18 -525 69 -155 154 -195 412 -195 l150 0 3 -598 c4 -666 3 -658
        74 -794 74 -142 219 -273 375 -338 166 -69 -15 -64 2303 -68 1440 -2 2120 1
        2176 8 207 27 401 138 530 304 59 74 115 184 133 257 7 30 11 247 11 636 l1
        591 177 4 c177 3 178 3 244 36 76 37 120 85 149 162 18 47 19 83 19 528 -1
        514 -1 514 -55 592 -32 46 -102 95 -159 112 -26 8 -98 11 -190 10 -83 -1 -158
        0 -167 3 -16 5 -17 40 -18 459 -1 495 -3 509 -62 632 -16 35 -47 85 -69 113
        -88 111 -328 288 -468 345 l-77 31 -315 5 -315 5 -3 251 -2 251 56 29 c73 37
        165 124 209 198 182 309 -62 696 -439 696 -196 0 -364 -105 -448 -279 -27 -57
        -33 -80 -36 -161 -3 -78 0 -107 18 -160 39 -118 125 -224 228 -282 l52 -30 1
        -141 c1 -268 -2 -362 -14 -369 -7 -4 -547 -7 -1202 -5 l-1190 2 -3 266 -2 267
        52 30 c118 66 216 207 240 342 35 202 -110 426 -325 499 -68 24 -183 33 -247
        21z m455 -2508 c258 -71 451 -225 565 -452 134 -267 127 -564 -21 -827 -55
        -98 -215 -261 -309 -316 -204 -118 -445 -153 -665 -97 -302 77 -568 359 -636
        675 -20 94 -16 275 9 377 80 329 342 583 672 654 89 19 292 11 385 -14z m2590
        -7 c244 -71 434 -234 544 -466 191 -397 62 -854 -310 -1104 -222 -149 -521
        -185 -787 -95 -275 94 -497 337 -574 629 -28 108 -28 277 0 393 83 340 358
        596 711 663 117 22 303 14 416 -20z m428 -2696 c50 -13 103 -61 118 -106 23
        -72 -10 -160 -76 -200 -29 -17 -102 -18 -1895 -21 l-1865 -2 -45 22 c-135 68
        -112 274 35 309 50 12 3684 10 3728 -2z"
				/>
				<Path
					d="M3680 1719 c0 -232 4 -357 11 -371 27 -50 -30 -48 1581 -48 1411 0
        1505 1 1538 18 59 29 60 33 60 410 l0 342 -1595 0 -1595 0 0 -351z"
				/>
			</G>
		</SVG>
	);
}

PluginIcon.propTypes = {
	size: PropTypes.number.isRequired,
	hideCircle: PropTypes.bool,
	invertColor: PropTypes.bool,
};

export default PluginIcon;
