/**
 * Renders the loader.
 *
 * @since 0.3.0
 *
 * @return {Component} The component to be rendered.
 */
export default function Loader() {
	return (
		<div className="ai-services-chatbot__loader-container">
			<svg
				width="50px"
				height="21px"
				viewBox="0 0 132 58"
				version="1.1"
				xmlns="http://www.w3.org/2000/svg"
			>
				<g stroke="none" fill="none">
					<g className="ai-services-chatbot__loader">
						<circle
							className="ai-services-chatbot__loader-dot"
							cx="25"
							cy="30"
							r="13"
						></circle>
						<circle
							className="ai-services-chatbot__loader-dot"
							cx="65"
							cy="30"
							r="13"
						></circle>
						<circle
							className="ai-services-chatbot__loader-dot"
							cx="105"
							cy="30"
							r="13"
						></circle>
					</g>
				</g>
			</svg>
		</div>
	);
}
