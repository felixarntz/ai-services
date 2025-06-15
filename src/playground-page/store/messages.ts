/**
 * External dependencies
 */
import { enums, helpers, store as aiStore } from '@ai-services/ai';
import type {
	AiCapability,
	Modality,
	Content,
	InlineDataPart,
	ModelParams,
	FunctionResponsePart,
	FunctionDeclaration,
	Tool,
	TextGenerationConfig,
	ImageGenerationConfig,
	TextToSpeechConfig,
} from '@ai-services/ai/types';

/**
 * WordPress dependencies
 */
import { resolveSelect } from '@wordpress/data';
import { __, _x, sprintf } from '@wordpress/i18n';
import { store as noticesStore } from '@wordpress/notices';
import { uploadMedia } from '@wordpress/media-utils';

/**
 * Internal dependencies
 */
import { STORE_NAME } from './name';
import logError from '../../utils/log-error';
import type { StoreConfig, Action, ThunkArgs } from '../../utils/store-types';
import type {
	AiPlaygroundMessage,
	AiPlaygroundMessageAdditionalData,
	WordPressAttachment,
} from '../types';
import errorToString from '../../utils/error-to-string';

const EMPTY_MESSAGE_ARRAY: AiPlaygroundMessage[] = [];

const FEATURE_SLUG = 'ai-playground';
const HISTORY_SLUG = 'default';
const UPLOAD_ATTACHMENT_NOTICE_ID = 'UPLOAD_ATTACHMENT_NOTICE_ID';

const prepareContentForCache = (
	content: Content,
	attachments: ( WordPressAttachment | null )[]
): Content => {
	return {
		...content,
		parts: content.parts.map( ( part, partIndex ) => {
			/*
			 * For inline data where the attachment is known, strip the actual base64 data to save space.
			 * Otherwise, the data may be too large for session storage.
			 */
			if (
				'inlineData' in part &&
				part.inlineData.data &&
				attachments[ partIndex ]
			) {
				const { data, ...otherInlineData } = part.inlineData;
				return {
					...part,
					inlineData: {
						...otherInlineData,
						data: '',
					},
				};
			}
			return part;
		} ),
	};
};

const parseContentFromCache = async (
	content: Content,
	attachments: ( WordPressAttachment | null )[]
): Promise< Content > => {
	return {
		...content,
		parts: await Promise.all(
			content.parts.map( async ( part, partIndex ) => {
				// For inline data where the attachment is known but base64 data was stripped before cache, restore it.
				if (
					'inlineData' in part &&
					! part.inlineData.data &&
					attachments[ partIndex ]
				) {
					return {
						...part,
						inlineData: {
							...part.inlineData,
							data: await helpers.fileToBase64DataUrl(
								attachments[ partIndex ].sizes?.large?.url ||
									attachments[ partIndex ].url
							),
						},
					};
				}
				return part;
			} )
		),
	};
};

const prepareMessageForCache = (
	message: AiPlaygroundMessage
): AiPlaygroundMessage => {
	// Migrate old "attachment" property to new "attachments" array on-the-fly.
	if (
		! ( 'attachments' in message ) &&
		'attachment' in message &&
		message.attachment
	) {
		let partIndex = message.content.parts.findIndex(
			( part ) => 'inlineData' in part
		);
		if ( partIndex === -1 ) {
			partIndex = 0;
		}
		message = {
			...message,
			attachments: getFreshPartsAttachments(
				message,
				partIndex,
				message.attachment
			),
		};
		delete message.attachment;
	}

	// We can only optimize messages with inline media if they have the attachments specified.
	if ( ! message.attachments ) {
		return message;
	}

	const prepared = {
		...message,
		content: prepareContentForCache( message.content, message.attachments ),
	};
	if ( prepared.rawData ) {
		/*
		 * For a user message, the content is directly in rawData, which is the request parameters object.
		 * For a model message, the content is within the first item of rawData, which is the candidates array.
		 */
		if ( ! Array.isArray( prepared.rawData ) && prepared.rawData.content ) {
			prepared.rawData = {
				...prepared.rawData,
				content: prepared.content,
			};
		} else if (
			Array.isArray( prepared.rawData ) &&
			prepared.rawData[ 0 ]?.content
		) {
			prepared.rawData = [ ...prepared.rawData ];
			prepared.rawData[ 0 ] = {
				...prepared.rawData[ 0 ],
				content: prepared.content,
			};
		}
	}
	return prepared;
};

const parseMessageFromCache = async (
	message: AiPlaygroundMessage
): Promise< AiPlaygroundMessage > => {
	// Migrate old "attachment" property to new "attachments" array on-the-fly.
	if ( ! message.attachments && message.attachment ) {
		let partIndex = message.content.parts.findIndex(
			( part ) => 'inlineData' in part
		);
		if ( partIndex === -1 ) {
			partIndex = 0;
		}
		message = {
			...message,
			attachments: getFreshPartsAttachments(
				message,
				partIndex,
				message.attachment
			),
		};
		delete message.attachment;
	}

	// We can only parse messages with inline media if they have the attachments specified.
	if ( ! message.attachments ) {
		return message;
	}

	const parsed = {
		...message,
		content: await parseContentFromCache(
			message.content,
			message.attachments
		),
	};
	if ( parsed.rawData ) {
		/*
		 * For a user message, the content is directly in rawData, which is the request parameters object.
		 * For a model message, the content is within the first item of rawData, which is the candidates array.
		 */
		if ( ! Array.isArray( parsed.rawData ) && parsed.rawData.content ) {
			parsed.rawData = {
				...parsed.rawData,
				content: parsed.content,
			};
		} else if (
			Array.isArray( parsed.rawData ) &&
			parsed.rawData[ 0 ]?.content
		) {
			parsed.rawData = [ ...parsed.rawData ];
			parsed.rawData[ 0 ] = {
				...parsed.rawData[ 0 ],
				content: parsed.content,
			};
		}
	}
	return parsed;
};

const retrieveMessages = async (): Promise< AiPlaygroundMessage[] > => {
	const history = await helpers
		.historyPersistence()
		.loadHistory( FEATURE_SLUG, HISTORY_SLUG );
	if ( history && history.entries ) {
		const entries = await Promise.all(
			( history.entries as AiPlaygroundMessage[] ).map(
				parseMessageFromCache
			)
		);

		/*
		 * For backward compatibility, populate the additional data for
		 * `foundationalCapability`, `service`, and `model` for each user message that are missing them,
		 * using a best guess based on the subsequent model response.
		 */
		for ( let index = 0; index < entries.length; index++ ) {
			const entry = entries[ index ];
			if (
				entry.type === 'user' &&
				( ! entry.foundationalCapability ||
					! entry.service ||
					! entry.model )
			) {
				const nextEntry = entries[ index + 1 ];
				if ( nextEntry && nextEntry.type === 'model' ) {
					if (
						nextEntry.content.parts?.[ 0 ] &&
						'inlineData' in nextEntry.content.parts[ 0 ]
					) {
						entry.foundationalCapability =
							enums.AiCapability.IMAGE_GENERATION;
					} else {
						entry.foundationalCapability =
							enums.AiCapability.TEXT_GENERATION;
					}
					entry.service = nextEntry.service;
					entry.model = nextEntry.model;
				} else {
					entry.foundationalCapability =
						enums.AiCapability.TEXT_GENERATION;
				}
				entries[ index ] = entry;
			} else if (
				entry.type === 'model' &&
				! entry.foundationalCapability
			) {
				if (
					entry.content.parts?.[ 0 ] &&
					'inlineData' in entry.content.parts[ 0 ]
				) {
					entry.foundationalCapability =
						enums.AiCapability.IMAGE_GENERATION;
				} else {
					entry.foundationalCapability =
						enums.AiCapability.TEXT_GENERATION;
				}
				entries[ index ] = entry;
			}
		}

		return entries;
	}
	return EMPTY_MESSAGE_ARRAY;
};

const storeMessages = async ( messages: AiPlaygroundMessage[] ) => {
	const history = {
		feature: FEATURE_SLUG,
		slug: HISTORY_SLUG,
		lastUpdated: '',
		entries: messages.map( prepareMessageForCache ),
	};
	await helpers.historyPersistence().saveHistory( history );
};

const clearMessages = async () => {
	await helpers
		.historyPersistence()
		.clearHistory( FEATURE_SLUG, HISTORY_SLUG );
};

const formatNewContent = async (
	prompt: string,
	attachments?: WordPressAttachment[],
	includeHistory?: boolean,
	messages?: AiPlaygroundMessage[]
): Promise< Content > => {
	if ( includeHistory ) {
		// See if the prompt is JSON in response to a function call in the last message.
		const lastMessageFunctionCall = getLastMessageFunctionCall( messages );
		if ( lastMessageFunctionCall ) {
			let responseData;
			try {
				responseData = JSON.parse( prompt.trim() );
			} catch ( err ) {
				// Ignore errors.
			}
			if ( responseData ) {
				let functionResponse: FunctionResponsePart[ 'functionResponse' ];
				if ( lastMessageFunctionCall.functionCall.id ) {
					functionResponse = {
						id: lastMessageFunctionCall.functionCall.id,
						response: responseData,
					};
					if ( lastMessageFunctionCall.functionCall.name ) {
						functionResponse.name =
							lastMessageFunctionCall.functionCall.name;
					}
				} else if ( lastMessageFunctionCall.functionCall.name ) {
					functionResponse = {
						name: lastMessageFunctionCall.functionCall.name,
						response: responseData,
					};
				} else {
					throw new Error( 'Invalid function call data.' );
				}
				return {
					role: enums.ContentRole.USER,
					parts: [
						{
							functionResponse,
						},
					],
				};
			}
		}
	}

	if ( attachments && attachments.length ) {
		return helpers.textAndAttachmentsToContent( prompt, attachments );
	}
	return helpers.textToContent( prompt );
};

const formatErrorContent = ( error: unknown ) => {
	return helpers.textToContent(
		errorToString( error ),
		enums.ContentRole.MODEL
	);
};

const getTools = (
	functionDeclarations: FunctionDeclaration[],
	selectedFunctionDeclarationNames: string[],
	additionalCapabilities: AiCapability[]
) => {
	const selectedFunctionDeclarations = functionDeclarations?.filter(
		( declaration ) =>
			selectedFunctionDeclarationNames &&
			selectedFunctionDeclarationNames.includes( declaration.name )
	);

	const tools: Tool[] = [];
	if ( selectedFunctionDeclarations && selectedFunctionDeclarations.length ) {
		tools.push( { functionDeclarations: selectedFunctionDeclarations } );
	}
	if (
		additionalCapabilities &&
		additionalCapabilities.includes( enums.AiCapability.WEB_SEARCH )
	) {
		tools.push( { webSearch: {} } );
	}

	return tools.length > 0 ? tools : null;
};

const getLastMessageFunctionCall = (
	messages: AiPlaygroundMessage[] | undefined
) => {
	if ( ! messages || ! messages.length ) {
		return null;
	}

	const lastMessage = messages[ messages.length - 1 ];
	if ( lastMessage.type !== 'model' ) {
		return null;
	}

	return (
		lastMessage.content?.parts?.find(
			( part ) => 'functionCall' in part
		) || null
	);
};

const generateFilename = (
	partIndex: number,
	mimeType: string,
	serviceSlug?: string,
	modelSlug?: string
) => {
	let extension = mimeType.split( '/' )[ 1 ];
	if ( extension === 'jpeg' ) {
		extension = 'jpg';
	}

	let source = '';
	if ( serviceSlug ) {
		source = `${ serviceSlug }-`;
		if ( modelSlug ) {
			source += `${ modelSlug }-`;
		}
	}

	const now = new Date();
	const dateSuffix = now
		.toISOString()
		.substring( 0, 19 )
		.replace( 'T', '-' )
		.replace( /:/g, '' );

	return `ai-generated-${ partIndex }-${ source }${ dateSuffix }.${ extension }`;
};

const getFreshPartsAttachments = (
	message: AiPlaygroundMessage,
	partIndex: number,
	attachment: WordPressAttachment
) => {
	const attachments = [ ...( message.attachments || [] ) ];
	if ( attachments.length < message.content.parts.length ) {
		const missingIndexes =
			message.content.parts.length - attachments.length;
		for ( let i = 0; i < missingIndexes; i++ ) {
			attachments.push( null );
		}
	}
	attachments[ partIndex ] = attachment;
	return attachments;
};

export enum ActionType {
	Unknown = 'REDUX_UNKNOWN',
	ReceiveMessage = 'RECEIVE_MESSAGE',
	ReceiveMessagesFromCache = 'RECEIVE_MESSAGES_FROM_CACHE',
	ResetMessages = 'RESET_MESSAGES',
	SetActiveMessage = 'SET_ACTIVE_MESSAGE',
	SetMessageAttachment = 'SET_MESSAGE_ATTACHMENT',
	LoadStart = 'LOAD_START',
	LoadFinish = 'LOAD_FINISH',
}

type UnknownAction = Action< ActionType.Unknown >;
type ReceiveMessageAction = Action<
	ActionType.ReceiveMessage,
	{
		type: AiPlaygroundMessage[ 'type' ];
		content: AiPlaygroundMessage[ 'content' ];
		additionalData: AiPlaygroundMessageAdditionalData;
	}
>;
type ReceiveMessagesFromCacheAction = Action<
	ActionType.ReceiveMessagesFromCache,
	{ messages: AiPlaygroundMessage[] }
>;
type ResetMessagesAction = Action< ActionType.ResetMessages >;
type SetActiveMessageAction = Action<
	ActionType.SetActiveMessage,
	{ message: AiPlaygroundMessage }
>;
type SetMessageAttachmentAction = Action<
	ActionType.SetMessageAttachment,
	{
		index: number;
		partIndex: number;
		attachment: WordPressAttachment;
	}
>;
type LoadStartAction = Action< ActionType.LoadStart >;
type LoadFinishAction = Action< ActionType.LoadFinish >;

export type CombinedAction =
	| UnknownAction
	| ReceiveMessageAction
	| ReceiveMessagesFromCacheAction
	| ResetMessagesAction
	| SetActiveMessageAction
	| SetMessageAttachmentAction
	| LoadStartAction
	| LoadFinishAction;

export type State = {
	messages: AiPlaygroundMessage[] | undefined;
	loading: boolean;
	activeMessage: AiPlaygroundMessage | null;
};

export type ActionCreators = typeof actions;
export type Selectors = typeof selectors;

type DispatcherArgs = ThunkArgs<
	State,
	ActionCreators,
	CombinedAction,
	Selectors
>;

const initialState: State = {
	messages: undefined,
	loading: false,
	activeMessage: null,
};

const actions = {
	/**
	 * Sends a message.
	 *
	 * @since 0.4.0
	 * @since 0.6.0 Now expects an array of attachments instead of a single attachment.
	 *
	 * @param prompt         - Message prompt.
	 * @param attachments    - Optional array of attachment objects.
	 * @param includeHistory - Whether to include the message history before the prompt. Default false.
	 * @returns Action creator.
	 */
	sendMessage(
		prompt: string,
		attachments?: WordPressAttachment[],
		includeHistory?: boolean
	) {
		return async ( { registry, dispatch, select }: DispatcherArgs ) => {
			const serviceSlug = registry.select( STORE_NAME ).getService();
			const modelSlug = registry.select( STORE_NAME ).getModel();
			if ( ! serviceSlug || ! modelSlug ) {
				logError( 'No AI service or model selected.' );
				return;
			}

			const modelParams: ModelParams = {
				feature: FEATURE_SLUG,
				model: modelSlug,
			};

			const foundationalCapability = registry
				.select( STORE_NAME )
				.getFoundationalCapability() as AiCapability;
			const additionalCapabilities = registry
				.select( STORE_NAME )
				.getAdditionalCapabilities() as AiCapability[];

			let generationConfig:
				| TextGenerationConfig
				| ImageGenerationConfig
				| TextToSpeechConfig;
			if (
				foundationalCapability === enums.AiCapability.TEXT_TO_SPEECH
			) {
				generationConfig = {} as TextToSpeechConfig;

				const voice = registry
					.select( STORE_NAME )
					.getModelParam( 'voice' ) as string;
				if ( voice ) {
					generationConfig.voice = voice;
				}
			} else if (
				foundationalCapability === enums.AiCapability.IMAGE_GENERATION
			) {
				generationConfig = {} as ImageGenerationConfig;

				const aspectRatio = registry
					.select( STORE_NAME )
					.getModelParam(
						'aspectRatio'
					) as ImageGenerationConfig[ 'aspectRatio' ];
				if ( aspectRatio ) {
					generationConfig.aspectRatio = aspectRatio;
				}
			} else if (
				foundationalCapability === enums.AiCapability.TEXT_GENERATION
			) {
				generationConfig = {} as TextGenerationConfig;

				const paramKeys = [ 'maxOutputTokens', 'temperature', 'topP' ];
				paramKeys.forEach( ( key ) => {
					const value = registry
						.select( STORE_NAME )
						.getModelParam( key );
					if ( value ) {
						generationConfig[ key ] = Number( value );
					}
				} );

				if (
					additionalCapabilities &&
					additionalCapabilities.includes(
						enums.AiCapability.MULTIMODAL_OUTPUT
					)
				) {
					const outputModalities = registry
						.select( STORE_NAME )
						.getModelParam( 'outputModalities' ) as Modality[];
					if (
						Array.isArray( outputModalities ) &&
						outputModalities.length
					) {
						generationConfig.outputModalities = outputModalities;
					}
				}
			} else {
				// Invalid foundational capability - just default to text generation (but doesn't really matter).
				generationConfig = {} as TextGenerationConfig;
			}

			if ( Object.keys( generationConfig ).length ) {
				modelParams.generationConfig = generationConfig;
			}

			const systemInstruction = registry
				.select( STORE_NAME )
				.getSystemInstruction();
			if ( systemInstruction ) {
				modelParams.systemInstruction = systemInstruction;
			}

			if (
				foundationalCapability === enums.AiCapability.TEXT_GENERATION
			) {
				const tools = getTools(
					registry
						.select( STORE_NAME )
						.getFunctionDeclarations() as FunctionDeclaration[],
					registry
						.select( STORE_NAME )
						.getSelectedFunctionDeclarations() as string[],
					additionalCapabilities
				);
				if ( tools ) {
					modelParams.tools = tools;
				}
			}

			const originalMessages = select.getMessages();

			const newContent = await formatNewContent(
				prompt,
				attachments,
				includeHistory,
				originalMessages
			);

			let contentToSend: Content | Content[] = newContent;
			if ( includeHistory ) {
				if ( originalMessages && originalMessages.length ) {
					contentToSend = [
						...originalMessages.map(
							( message ) => message.content
						),
						newContent,
					];
				}
			}

			dispatch( {
				type: ActionType.LoadStart,
				payload: {},
			} );

			if ( registry.select( aiStore ).getServices() === undefined ) {
				await resolveSelect( aiStore ).getServices();
			}

			const service = registry
				.select( aiStore )
				.getAvailableService( serviceSlug );
			const model = service.getModel( modelParams );

			const additionalData: AiPlaygroundMessageAdditionalData = {
				foundationalCapability,
				service: {
					slug: serviceSlug,
					name: service.metadata?.name || serviceSlug,
				},
				model: {
					slug: modelSlug,
					name: model.metadata?.name || modelSlug,
				},
			};

			const additionalPromptData: AiPlaygroundMessageAdditionalData = {
				...additionalData,
				rawData: {
					content: contentToSend,
					modelParams,
				},
			};
			if ( attachments && attachments.length ) {
				additionalPromptData.attachments = [
					null, // Based on `formatNewContent()`, the first part is always text, i.e. no related attachment.
					...attachments,
				];
			}

			dispatch.receiveMessage( 'user', newContent, additionalPromptData );

			let candidates;
			try {
				switch ( foundationalCapability ) {
					case enums.AiCapability.IMAGE_GENERATION:
						candidates = await model.generateImage( contentToSend );
						break;
					case enums.AiCapability.TEXT_TO_SPEECH:
						candidates = await model.textToSpeech( contentToSend );
						break;
					default:
						candidates = await model.generateText( contentToSend );
				}

				const responseContent =
					helpers.getCandidateContents( candidates )[ 0 ];
				dispatch.receiveMessage( 'model', responseContent, {
					...additionalData,
					rawData: candidates,
				} );
			} catch ( error ) {
				dispatch.receiveMessage( 'error', formatErrorContent( error ) );
			}

			dispatch( {
				type: ActionType.LoadFinish,
				payload: {},
			} );

			return candidates;
		};
	},

	/**
	 * Uploads inline data of a specific message to the media library.
	 *
	 * @since 0.5.0
	 *
	 * @param index      - The index of the message.
	 * @param partIndex  - The index of the part within the message.
	 * @param inlineData - The inline data object.
	 * @returns Action creator.
	 */
	uploadAttachment(
		index: number,
		partIndex: number,
		inlineData: InlineDataPart[ 'inlineData' ]
	) {
		return async ( { dispatch, registry, select }: DispatcherArgs ) => {
			const messages = select.getMessages();
			const message = messages?.[ index ];
			if ( ! message ) {
				return;
			}

			// Sanity check that it's the correct message.
			const inlineDataPart = message.content.parts?.[ partIndex ];
			if (
				! ( 'inlineData' in inlineDataPart ) ||
				inlineDataPart.inlineData.data !== inlineData.data
			) {
				return;
			}

			const fileBlob = await helpers.base64DataUrlToBlob(
				helpers.base64DataToBase64DataUrl(
					inlineData.data,
					inlineData.mimeType
				)
			);
			if ( ! fileBlob ) {
				logError( 'Could not transform base64 data URL to blob.' );
				return;
			}

			const file = new File(
				[ fileBlob ],
				generateFilename(
					partIndex,
					fileBlob.type,
					message.service?.slug,
					message.model?.slug
				),
				{
					type: fileBlob.type,
					lastModified: new Date().getTime(),
				}
			);

			const attachmentData: { caption?: string } = {};
			if ( message.type === 'model' ) {
				const previousMessage = messages?.[ index - 1 ];
				if ( previousMessage && previousMessage.type === 'user' ) {
					const prompt = helpers.contentToText(
						previousMessage.content
					);
					if ( prompt ) {
						attachmentData.caption = sprintf(
							/* translators: %s: prompt text */
							_x(
								'Generated for prompt: %s',
								'attachment caption',
								'ai-services'
							),
							prompt
						);
					}
				}
			}

			return new Promise( ( resolve ) => {
				uploadMedia( {
					filesList: [ file ],
					// @ts-expect-error WordPress expecting the `RestAttachment` type here is incorrect.
					additionalData: attachmentData,
					onFileChange: ( [ attachment ] ) => {
						if ( ! attachment ) {
							registry
								.dispatch( noticesStore )
								.createErrorNotice(
									__( 'Saving file failed.', 'ai-services' ),
									{
										id: UPLOAD_ATTACHMENT_NOTICE_ID,
										type: 'snackbar',
										speak: true,
									}
								);
							resolve( null );
							return;
						}
						if ( attachment.id ) {
							dispatch.setMessageAttachment(
								index,
								partIndex,
								attachment as WordPressAttachment
							);
							registry
								.dispatch( noticesStore )
								.createSuccessNotice(
									__(
										'File saved to media library.',
										'ai-services'
									),
									{
										id: UPLOAD_ATTACHMENT_NOTICE_ID,
										type: 'snackbar',
										speak: true,
									}
								);
							resolve( attachment );
						}
					},
					onError: ( err ) => {
						registry.dispatch( noticesStore ).createErrorNotice(
							sprintf(
								/* translators: %s: error message */
								__(
									'Saving file failed with error: %s',
									'ai-services'
								),
								err.message || err
							),
							{
								id: UPLOAD_ATTACHMENT_NOTICE_ID,
								type: 'snackbar',
								speak: true,
							}
						);
						resolve( null );
					},
				} );
			} );
		};
	},

	/**
	 * Receives new content to append to the list of messages.
	 *
	 * @since 0.4.0
	 *
	 * @param type           - Message type. Either 'user', 'model', or 'error'.
	 * @param content        - Message content.
	 * @param additionalData - Additional data to include with the message.
	 * @returns Action creator.
	 */
	receiveMessage(
		type: AiPlaygroundMessage[ 'type' ],
		content: AiPlaygroundMessage[ 'content' ],
		additionalData: AiPlaygroundMessageAdditionalData = {}
	) {
		return ( { dispatch }: DispatcherArgs ) => {
			dispatch( {
				type: ActionType.ReceiveMessage,
				payload: { type, content, additionalData },
			} );
		};
	},

	/**
	 * Receives messages from cache to restore the session.
	 *
	 * @since 0.4.0
	 *
	 * @param messages - Messages to restore.
	 * @returns Action creator.
	 */
	receiveMessagesFromCache( messages: AiPlaygroundMessage[] ) {
		return ( { dispatch }: DispatcherArgs ) => {
			dispatch( {
				type: ActionType.ReceiveMessagesFromCache,
				payload: { messages },
			} );
		};
	},

	/**
	 * Resets all messages, effectively deleting them to start a new session.
	 *
	 * @since 0.4.0
	 *
	 * @returns Action creator.
	 */
	resetMessages() {
		return ( { dispatch }: DispatcherArgs ) => {
			dispatch( {
				type: ActionType.ResetMessages,
				payload: {},
			} );
		};
	},

	/**
	 * Sets the active message (to display a modal for it).
	 *
	 * @since 0.6.0
	 *
	 * @param message - Message to display.
	 * @returns Action creator.
	 */
	setActiveMessage( message: AiPlaygroundMessage ) {
		return ( { dispatch }: DispatcherArgs ) => {
			dispatch( {
				type: ActionType.SetActiveMessage,
				payload: { message },
			} );
		};
	},

	/**
	 * Sets the attachment for a message.
	 *
	 * @since 0.5.0
	 *
	 * @param index      - The index of the message.
	 * @param partIndex  - The index of the part within the message.
	 * @param attachment - The attachment object.
	 * @returns Action creator.
	 */
	setMessageAttachment(
		index: number,
		partIndex: number,
		attachment: WordPressAttachment
	) {
		return ( { dispatch }: DispatcherArgs ) => {
			dispatch( {
				type: ActionType.SetMessageAttachment,
				payload: { index, partIndex, attachment },
			} );
		};
	},
};

/**
 * Reducer for the store mutations.
 *
 * @since 0.4.0
 *
 * @param state  - Current state.
 * @param action - Action object.
 * @returns New state.
 */
function reducer( state: State = initialState, action: CombinedAction ): State {
	switch ( action.type ) {
		case ActionType.ReceiveMessage: {
			const { type, content, additionalData } = action.payload;
			const newMessage: AiPlaygroundMessage = { type, content };
			if ( additionalData ) {
				newMessage.foundationalCapability =
					additionalData.foundationalCapability;
				newMessage.service = additionalData.service;
				newMessage.model = additionalData.model;
				newMessage.rawData = additionalData.rawData;
				if ( additionalData.attachments ) {
					newMessage.attachments = additionalData.attachments;
				}
			}

			const messages = [ ...( state.messages || [] ), newMessage ];
			storeMessages( messages );
			return {
				...state,
				messages,
			};
		}
		case ActionType.ReceiveMessagesFromCache: {
			const { messages } = action.payload;
			return {
				...state,
				messages,
			};
		}
		case ActionType.ResetMessages: {
			clearMessages();
			return {
				...state,
				messages: [],
			};
		}
		case ActionType.SetActiveMessage: {
			const { message } = action.payload;
			return {
				...state,
				activeMessage: message,
			};
		}
		case ActionType.SetMessageAttachment: {
			const { index, partIndex, attachment } = action.payload;
			if ( state.messages?.[ index ] ) {
				const messages = [ ...state.messages ];
				messages[ index ] = {
					...messages[ index ],
					attachments: getFreshPartsAttachments(
						messages[ index ],
						partIndex,
						attachment
					),
				};
				storeMessages( messages );
				return {
					...state,
					messages,
				};
			}
			return state;
		}
		case ActionType.LoadStart: {
			return {
				...state,
				loading: true,
			};
		}
		case ActionType.LoadFinish: {
			return {
				...state,
				loading: false,
			};
		}
	}

	return state;
}

const resolvers = {
	/**
	 * Retrieves messages from session storage.
	 *
	 * @since 0.4.0
	 *
	 * @returns Action creator.
	 */
	getMessages() {
		return async ( { dispatch }: DispatcherArgs ) => {
			const messages = await retrieveMessages();
			dispatch.receiveMessagesFromCache( messages );
		};
	},
};

const selectors = {
	getMessages: ( state: State ) => {
		return state.messages || EMPTY_MESSAGE_ARRAY;
	},

	isLoading: ( state: State ) => {
		return state.loading || state.messages === undefined;
	},

	getActiveMessage: ( state: State ) => {
		return state.activeMessage;
	},
};

const storeConfig: StoreConfig<
	State,
	ActionCreators,
	CombinedAction,
	Selectors
> = {
	initialState,
	actions,
	reducer,
	resolvers,
	selectors,
};

export default storeConfig;
