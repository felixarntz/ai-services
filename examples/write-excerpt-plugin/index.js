/*
 * Props to https://github.com/swissspidy, since most of this code is copied or at least heavily inspired from his code
 * in https://github.com/swissspidy/ai-experiments/blob/main/packages/editor/src/commands.tsx.
 */

const { useCommandLoader } = wp.commands;
const { Icon } = wp.components;
const { useDispatch, useSelect } = wp.data;
const { createElement, useState } = wp.element;
const { store: blockEditorStore } = wp.blockEditor;
const { store: editorStore } = wp.editor;
const { helpers, store: aiStore } = window.aiServices.ai;
const { serialize } = wp.blocks;
const { registerPlugin } = wp.plugins;
const { __ } = wp.i18n;

const AI_CAPABILITIES = [ 'text_generation' ];

function useWriteExcerptCommandLoader() {
	const { editPost } = useDispatch( editorStore );

	const [ isLoading ] = useState( false );

	const { postContent } = useSelect( ( select ) => {
		const content =
			new window.DOMParser().parseFromString(
				serialize( select( blockEditorStore ).getBlocks() ),
				'text/html'
			).body.textContent || '';
		return {
			postContent: content,
		};
	}, [] );

	const service = useSelect( ( select ) =>
		select( aiStore ).getAvailableService( AI_CAPABILITIES )
	);
	if ( ! service ) {
		return {
			commands: [],
			isLoading: true,
		};
	}

	const commands = [
		{
			name: 'write-excerpt-plugin/write-excerpt',
			label: __( 'Write excerpt', 'write-excerpt-plugin' ),
			icon: createElement( Icon, { icon: 'edit' } ),
			// @ts-ignore
			callback: async ( { close } ) => {
				close();

				let candidates;
				try {
					candidates = await service.generateText(
						__(
							'Summarize the following text in full sentences in less than 300 characters:',
							'write-excerpt-plugin'
						) +
							' ' +
							postContent,
						{ feature: 'write-excerpt-plugin' }
					);
				} catch ( error ) {
					window.console.error( error );
					return;
				}

				const excerpt = helpers
					.getTextFromContents(
						helpers.getCandidateContents( candidates )
					)
					.replaceAll( '\n\n\n\n', '\n\n' );
				editPost( { excerpt } );
			},
		},
	];

	return {
		commands,
		isLoading,
	};
}

function RenderPlugin() {
	useCommandLoader( {
		name: 'write-excerpt-plugin/commands',
		hook: useWriteExcerptCommandLoader,
	} );
	return null;
}

registerPlugin( 'write-excerpt-plugin', {
	render: RenderPlugin,
} );
