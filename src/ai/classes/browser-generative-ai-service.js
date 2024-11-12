/**
 * Internal dependencies
 */
import GenerativeAiService from './generative-ai-service';
import GenerativeAiModel from './generative-ai-model';
import BrowserGenerativeAiModel from './browser-generative-ai-model';
import { findModel } from '../util';

const EMPTY_OBJECT = {};

/**
 * Special service class only used for the 'browser' service.
 *
 * @since 0.1.0
 */
export default class BrowserGenerativeAiService extends GenerativeAiService {
	/**
	 * Gets a generative model instance from the service.
	 *
	 * @since n.e.x.t
	 *
	 * @param {Object} modelParams Model parameters. At a minimum this must include the unique "feature" identifier. It
	 *                             can also include the model slug and other optional parameters.
	 * @return {GenerativeAiModel} Generative AI model instance.
	 */
	getModel( modelParams ) {
		modelParams = modelParams || EMPTY_OBJECT;

		const model = findModel( this.models, modelParams );

		return new BrowserGenerativeAiModel(
			{
				serviceSlug: this.slug,
				...model,
			},
			modelParams
		);
	}
}
