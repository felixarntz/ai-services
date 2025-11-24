/**
 * External dependencies
 */
import type { FunctionDeclaration } from '@ai-services/ai/types';
import { OptionsFilterSearchControl, Tabs } from 'wp-admin-components';
import { Modal } from 'wp-interface';

/**
 * WordPress dependencies
 */
import {
	Flex,
	Button,
	TextControl,
	Notice,
	SelectControl,
} from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { useEffect, useMemo, useState } from '@wordpress/element';
import { __, _x, sprintf } from '@wordpress/i18n';
import { trash } from '@wordpress/icons';
import { speak } from '@wordpress/a11y';

/**
 * Internal dependencies
 */
import { store as playgroundStore } from '../../store';
import './style.scss';

const EMPTY_PARAMETER_ARRAY: Parameter[] = [];
const MIN_FUNCTION_DECLARATIONS_COUNT_FOR_FILTER = 8;

type ParameterSchema = {
	type: string;
	description?: string;
	items?: { type: string };
	properties?: Record< string, ParameterSchema >;
	required?: string[];
	[ key: string ]: unknown;
};

type Parameter = {
	name: string;
	schema: ParameterSchema;
};

type ParametersObject = {
	properties: Record< string, ParameterSchema >;
};

/**
 * Converts an object of parameter schemas keyed by name to an array of objects with name and schema properties.
 *
 * @since 0.5.0
 *
 * @param parameters - The parameters object.
 * @returns The array of objects with name and schema properties.
 */
function parametersObjectToArray( parameters: ParametersObject ): Parameter[] {
	if ( ! parameters.properties ) {
		return [];
	}
	return Object.entries( parameters.properties ).map(
		( [ name, schema ] ): Parameter => ( {
			name,
			schema,
		} )
	);
}

/**
 * Converts an array of objects with name and schema properties to an object of parameter schemas keyed by name.
 *
 * @since 0.5.0
 *
 * @param parameters - The array of objects with name and schema properties.
 * @returns The parameters object.
 */
function parametersArrayToObject( parameters: Parameter[] ): ParameterSchema {
	const properties: Record< string, ParameterSchema > = {};
	parameters.forEach( ( { name, schema } ) => {
		properties[ name ] = schema;
	} );
	return {
		type: 'object',
		properties,
	};
}

type PatternValidatorProps = {
	value: string;
	pattern: RegExp;
	message: string;
};

/**
 * Renders a pattern validator that shows a notice if the given value does not match the pattern.
 *
 * @since 0.5.0
 *
 * @param props - The component props.
 * @returns The component to be rendered.
 */
function PatternValidator( props: PatternValidatorProps ) {
	const { value, pattern, message } = props;

	if ( value && ! value.match( pattern ) ) {
		return (
			<Notice
				spokenMessage={ null }
				status="warning"
				isDismissible={ false }
			>
				{ message }
			</Notice>
		);
	}

	return null;
}

const PARAMETER_TYPES: string[] = [
	'string',
	'number',
	'boolean',
	'array',
	'object',
];

type SelectOption = {
	value: string;
	label: string;
};

const PARAMETER_TYPE_OPTIONS: SelectOption[] = PARAMETER_TYPES.map(
	( type ) => ( {
		value: type,
		label: type,
	} )
);
const PARAMETER_ITEM_TYPE_OPTIONS: SelectOption[] =
	PARAMETER_TYPE_OPTIONS.filter( ( option ) => option.value !== 'array' );

type FunctionParameterProps = {
	name: string;
	schema: ParameterSchema;
	onChange: ( newParameter: Parameter ) => void;
	onDelete: () => void;
};

/**
 * Renders the group of input fields for a single function parameter.
 *
 * @since 0.5.0
 *
 * @param props - The component props.
 * @returns The component to be rendered.
 */
function FunctionParameter( props: FunctionParameterProps ) {
	const { name, schema, onChange, onDelete } = props;

	return (
		<div
			className="ai-services-playground-function-declarations-modal__function-parameter"
			role="group"
		>
			<Flex direction="column" gap="2">
				<Flex gap="2" align="flex-end">
					<TextControl
						label={ _x( 'Name', 'parameter name', 'ai-services' ) }
						className="ai-services-playground-function-declarations-modal__function-parameter__name"
						value={ name }
						onChange={ ( value: string ) =>
							onChange( { name: value, schema } )
						}
						__nextHasNoMarginBottom
						__next40pxDefaultSize
					/>
					<SelectControl
						label={ _x( 'Type', 'parameter type', 'ai-services' ) }
						className="ai-services-playground-function-declarations-modal__function-parameter__type"
						value={ schema.type || 'string' }
						options={ PARAMETER_TYPE_OPTIONS }
						onChange={ ( value: string ) => {
							const newSchema: ParameterSchema = {
								...schema,
								type: value,
							};
							if ( value === 'array' && ! newSchema.items ) {
								newSchema.items = { type: 'string' };
							} else if ( value !== 'array' && newSchema.items ) {
								delete newSchema.items;
							}
							onChange( { name, schema: newSchema } );
						} }
						__nextHasNoMarginBottom
						__next40pxDefaultSize
					/>
					<TextControl
						label={ _x(
							'Description',
							'parameter description',
							'ai-services'
						) }
						className="ai-services-playground-function-declarations-modal__function-parameter__description"
						value={ schema.description || '' }
						onChange={ ( value: string ) =>
							onChange( {
								name,
								schema: { ...schema, description: value },
							} )
						}
						__nextHasNoMarginBottom
						__next40pxDefaultSize
					/>
					<Button
						icon={ trash }
						label={ __( 'Delete parameter', 'ai-services' ) }
						variant="secondary"
						isDestructive
						onClick={ onDelete }
						__next40pxDefaultSize
					/>
				</Flex>
				{ schema.type === 'array' && (
					<SelectControl
						label={ _x(
							'Items type',
							'parameter items type',
							'ai-services'
						) }
						className="ai-services-playground-function-declarations-modal__function-parameter__items-type"
						value={ schema.items?.type || 'string' }
						options={ PARAMETER_ITEM_TYPE_OPTIONS }
						onChange={ ( value: string ) =>
							onChange( {
								name,
								schema: { ...schema, items: { type: value } },
							} )
						}
						__nextHasNoMarginBottom
						__next40pxDefaultSize
					/>
				) }
			</Flex>
		</div>
	);
}

type FunctionDeclarationFormProps = {
	functionDeclaration: FunctionDeclaration | null;
};

/**
 * Renders the form for adding or editing a function declaration.
 *
 * @since 0.5.0
 *
 * @param props - The component props.
 * @returns The component to be rendered.
 */
function FunctionDeclarationForm( props: FunctionDeclarationFormProps ) {
	const { functionDeclaration } = props;

	const {
		setFunctionDeclaration: setStoreFunctionDeclaration,
		deleteFunctionDeclaration: deleteStoreFunctionDeclaration,
		setActiveFunctionDeclaration: setStoreActiveFunctionDeclaration,
	} = useDispatch( playgroundStore );

	const [ functionName, setFunctionName ] = useState< string >( '' );
	const [ functionDescription, setFunctionDescription ] =
		useState< string >( '' );
	const [ functionParameters, setFunctionParameters ] = useState<
		Parameter[]
	>( EMPTY_PARAMETER_ARRAY );

	useEffect( () => {
		if ( ! functionDeclaration ) {
			setFunctionName( '' );
			setFunctionDescription( '' );
			setFunctionParameters( EMPTY_PARAMETER_ARRAY );
			return;
		}

		setFunctionName( functionDeclaration.name );
		setFunctionDescription( functionDeclaration.description || '' );
		setFunctionParameters(
			'parameters' in functionDeclaration &&
				functionDeclaration.parameters !== undefined
				? parametersObjectToArray(
						functionDeclaration.parameters as {
							properties: Record< string, ParameterSchema >;
						}
				  )
				: EMPTY_PARAMETER_ARRAY
		);
	}, [
		functionDeclaration,
		setFunctionName,
		setFunctionDescription,
		setFunctionParameters,
	] );

	const addFunctionParameter = (): void => {
		setFunctionParameters( [
			...functionParameters,
			{ name: '', schema: { type: 'string', description: '' } },
		] );
	};

	const editFunctionParameter = (
		newParameter: Parameter,
		index: number
	): void => {
		const newParameters = [ ...functionParameters ];
		newParameters[ index ] = newParameter;
		setFunctionParameters( newParameters );
	};

	const deleteFunctionParameter = ( index: number ): void => {
		const newParameters = [ ...functionParameters ];
		newParameters.splice( index, 1 );
		setFunctionParameters( newParameters );
	};

	const formSubmitDisabled: boolean = useMemo( () => {
		// Check if any of the required values are empty.
		if (
			! functionName ||
			! functionDescription ||
			functionParameters?.find( ( { name } ) => ! name )
		) {
			return true;
		}

		return false;
	}, [ functionName, functionDescription, functionParameters ] );

	const saveChanges = (): void => {
		if ( formSubmitDisabled ) {
			return;
		}

		const existingFunctionName = functionDeclaration?.name;

		setStoreFunctionDeclaration(
			functionName,
			functionDescription,
			parametersArrayToObject( functionParameters ),
			existingFunctionName || ''
		);

		// If this is a new function or the function name changed, set the new function as active.
		if ( functionName !== existingFunctionName ) {
			setStoreActiveFunctionDeclaration( functionName );
			if ( ! existingFunctionName ) {
				speak(
					sprintf(
						/* translators: %s: function name */
						__(
							'Function %s added. You are now editing the function.',
							'ai-services'
						),
						`${ functionName }()`
					),
					'assertive'
				);
			} else {
				speak(
					sprintf(
						/* translators: 1: function name, 2: original function name */
						__(
							'Function %1$s saved and renamed from %2$s. You are now editing the function.',
							'ai-services'
						),
						`${ functionName }()`,
						`${ existingFunctionName }()`
					),
					'assertive'
				);
			}
		} else {
			speak(
				sprintf(
					/* translators: %s: function name */
					__( 'Function %s saved.', 'ai-services' ),
					`${ functionName }()`
				),
				'assertive'
			);
		}
	};

	return (
		<form
			className="ai-services-playground-function-declarations-modal__form"
			onSubmit={ ( event: React.FormEvent< HTMLFormElement > ) => {
				event.preventDefault();
				saveChanges();
			} }
		>
			<Flex gap="4">
				<h2 className="ai-services-playground-function-declarations-modal__title">
					{ !! functionDeclaration ? (
						<>
							{ __( 'Edit function:', 'ai-services' ) + ' ' }
							<span>{ `${ functionName }()` }</span>
						</>
					) : (
						__( 'Add new function', 'ai-services' )
					) }
				</h2>
				<div className="ai-services-playground-function-declarations-modal__actions">
					{ !! functionDeclaration && (
						<Button
							icon={ trash }
							label={ __( 'Delete function', 'ai-services' ) }
							className="ai-services-playground-function-declarations-modal__delete"
							variant="secondary"
							isDestructive
							onClick={ () => {
								if ( functionDeclaration?.name ) {
									deleteStoreFunctionDeclaration(
										functionDeclaration.name
									);
								}
							} }
							__next40pxDefaultSize
						/>
					) }
					<Button
						type="submit"
						variant="primary"
						className="ai-services-playground-function-declarations-modal__submit"
						disabled={ formSubmitDisabled }
						__next40pxDefaultSize
					>
						{ !! functionDeclaration
							? __( 'Save changes', 'ai-services' )
							: __( 'Save', 'ai-services' ) }
					</Button>
				</div>
			</Flex>
			<Flex direction="column" gap="4">
				<TextControl
					label={ __( 'Function name', 'ai-services' ) }
					className="ai-services-playground-function-declarations-modal__function-name"
					value={ functionName }
					onChange={ setFunctionName }
					pattern="[a-zA-Z][a-zA-Z0-9_]*"
					__nextHasNoMarginBottom
					__next40pxDefaultSize
				/>
				<PatternValidator
					value={ functionName }
					pattern={ /^[a-zA-Z][a-zA-Z0-9_]*$/ }
					message={ __(
						'The function name must start with a letter and can only contain letters, numbers, and underscores.',
						'ai-services'
					) }
				/>
				<TextControl
					label={ __( 'Function description', 'ai-services' ) }
					className="ai-services-playground-function-declarations-modal__function-description"
					value={ functionDescription }
					onChange={ setFunctionDescription }
					__nextHasNoMarginBottom
					__next40pxDefaultSize
				/>
				{ functionParameters.map( ( parameter, index ) => (
					<FunctionParameter
						key={ index }
						name={ parameter.name }
						schema={ parameter.schema }
						onChange={ ( newParameter: Parameter ) =>
							editFunctionParameter( newParameter, index )
						}
						onDelete={ () => deleteFunctionParameter( index ) }
					/>
				) ) }
				<Flex gap="4">
					<Button
						type="button"
						variant="secondary"
						className="ai-services-playground-function-declarations-modal__add-parameter"
						onClick={ addFunctionParameter }
						__next40pxDefaultSize
					>
						{ __( 'Add parameter', 'ai-services' ) }
					</Button>
					<Button
						type="submit"
						variant="primary"
						className="ai-services-playground-function-declarations-modal__submit"
						disabled={ formSubmitDisabled }
						__next40pxDefaultSize
					>
						{ !! functionDeclaration
							? __( 'Save changes', 'ai-services' )
							: __( 'Save', 'ai-services' ) }
					</Button>
				</Flex>
			</Flex>
		</form>
	);
}

const getTabId = ( functionName?: string | false ): string => {
	if ( functionName ) {
		return `function-${ functionName }`;
	}
	return 'add-new-function';
};

const getFunctionNameFromTabId = ( tabId: string ): string => {
	if ( tabId === 'add-new-function' ) {
		return '';
	}
	return tabId.replace( /^function-/, '' );
};

const SEARCH_FIELDS: string[] = [ 'name' ];

type FunctionDeclarationsModalSelectProps = {
	availableFunctionDeclarations: FunctionDeclaration[];
	activeFunctionDeclaration: FunctionDeclaration | null;
};

/**
 * Renders the modal for managing the available function declarations.
 *
 * @since 0.5.0
 *
 * @returns The component to be rendered.
 */
export default function FunctionDeclarationsModal() {
	const {
		availableFunctionDeclarations,
		activeFunctionDeclaration,
	}: FunctionDeclarationsModalSelectProps = useSelect( ( select ) => {
		const {
			getFunctionDeclarations,
			getActiveFunctionDeclaration: getStoreActiveFunctionDeclaration,
		} = select( playgroundStore );

		const available: FunctionDeclaration[] = getFunctionDeclarations();

		let activeName = getStoreActiveFunctionDeclaration();
		if ( typeof activeName !== 'string' ) {
			activeName = available.length > 0 ? available[ 0 ].name : null;
		}

		return {
			availableFunctionDeclarations: available,
			activeFunctionDeclaration:
				available.find( ( { name } ) => name === activeName ) || null,
		};
	}, [] );

	const { setActiveFunctionDeclaration } = useDispatch( playgroundStore );

	const [ filteredFunctionDeclarations, setFilteredFunctionDeclarations ] =
		useState< FunctionDeclaration[] >( availableFunctionDeclarations );

	useEffect( () => {
		setFilteredFunctionDeclarations( availableFunctionDeclarations );
	}, [ availableFunctionDeclarations ] );

	const showFilter: boolean =
		availableFunctionDeclarations.length >=
		MIN_FUNCTION_DECLARATIONS_COUNT_FOR_FILTER;

	const functionDeclarationsToRender: FunctionDeclaration[] = showFilter
		? filteredFunctionDeclarations
		: availableFunctionDeclarations;

	return (
		<Modal
			identifier="function-declarations"
			title={ __( 'Manage function declarations', 'ai-services' ) }
			closeButtonLabel={ __( 'Close modal', 'ai-services' ) }
			className="ai-services-playground-function-declarations-modal"
		>
			<div className="ai-services-playground-function-declarations-modal__content">
				<Tabs
					selectedTabId={ getTabId(
						activeFunctionDeclaration
							? activeFunctionDeclaration.name
							: false
					) }
					onSelect={ ( tabId: string | null | undefined ) => {
						if ( typeof tabId === 'string' ) {
							setActiveFunctionDeclaration(
								getFunctionNameFromTabId( tabId )
							);
						}
					} }
					orientation="vertical"
				>
					<div className="ai-services-playground-function-declarations-modal__sidebar">
						{ showFilter && (
							<OptionsFilterSearchControl
								label={ __(
									'Search functions',
									'ai-services'
								) }
								className="ai-services-playground-function-declarations-modal__search-control"
								options={ availableFunctionDeclarations.map(
									( item ) => ( {
										value: item.name,
										label: `${ item.name }()`,
									} )
								) }
								onFilter={ (
									filteredOptions: SelectOption[]
								) => {
									setFilteredFunctionDeclarations(
										availableFunctionDeclarations.filter(
											( decl ) =>
												filteredOptions.some(
													( opt ) =>
														opt.value === decl.name
												)
										)
									);
								} }
								searchFields={ SEARCH_FIELDS }
							/>
						) }
						<Tabs.TabList className="ai-services-playground-function-declarations-modal__function-declarations-list">
							{ functionDeclarationsToRender.map(
								( { name } ) => (
									<Tabs.Tab
										key={ name }
										tabId={ getTabId( name ) }
										title={
											__(
												'Edit function:',
												'ai-services'
											) +
											' ' +
											`${ name }()`
										}
										className="ai-services-playground-function-declarations-modal__function-declaration components-button"
									>
										{ `${ name }()` }
									</Tabs.Tab>
								)
							) }
							<Tabs.Tab
								tabId={ getTabId( false ) }
								title={ __(
									'Add new function',
									'ai-services'
								) }
								className="ai-services-playground-function-declarations-modal__add-function-declaration components-button is-link"
							>
								{ __( 'Add new function', 'ai-services' ) }
							</Tabs.Tab>
						</Tabs.TabList>
					</div>
					<div className="ai-services-playground-function-declarations-modal__main">
						{ functionDeclarationsToRender.map(
							( functionDeclarationItem ) => (
								<Tabs.TabPanel
									key={ functionDeclarationItem.name }
									tabId={ getTabId(
										functionDeclarationItem.name
									) }
									className="ai-services-playground-function-declarations-modal__function-declaration-panel"
								>
									<FunctionDeclarationForm
										functionDeclaration={
											functionDeclarationItem
										}
									/>
								</Tabs.TabPanel>
							)
						) }
						<Tabs.TabPanel
							tabId={ getTabId( false ) }
							className="ai-services-playground-function-declarations-modal__add-function-declaration-panel"
						>
							<FunctionDeclarationForm
								functionDeclaration={ null }
							/>
						</Tabs.TabPanel>
					</div>
				</Tabs>
			</div>
		</Modal>
	);
}
