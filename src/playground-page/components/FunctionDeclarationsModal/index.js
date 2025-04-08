/**
 * External dependencies
 */
import { OptionsFilterSearchControl, Tabs } from '@ai-services/components';
import { Modal } from '@ai-services/interface';

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

const EMPTY_ARRAY = [];
const MIN_FUNCTION_DECLARATIONS_COUNT_FOR_FILTER = 8;

/**
 * Converts an object of parameter schemas keyed by name to an array of objects with name and schema properties.
 *
 * @since 0.5.0
 *
 * @param {Object} parameters The parameters object.
 * @return {Object[]} The array of objects with name and schema properties.
 */
function parametersObjectToArray( parameters ) {
	if ( ! parameters.properties ) {
		return [];
	}
	return Object.entries( parameters.properties ).map(
		( [ name, schema ] ) => ( {
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
 * @param {Object[]} parameters The array of objects with name and schema properties.
 * @return {Object} The parameters object.
 */
function parametersArrayToObject( parameters ) {
	const properties = {};
	parameters.forEach( ( { name, schema } ) => {
		properties[ name ] = schema;
	} );
	return {
		type: 'object',
		properties,
	};
}

/**
 * Renders a pattern validator that shows a notice if the given value does not match the pattern.
 *
 * @since 0.5.0
 *
 * @param {Object} props         The component props.
 * @param {string} props.value   The value to validate.
 * @param {RegExp} props.pattern The pattern to match.
 * @param {string} props.message The message to display if the value does not match the pattern.
 * @return {Component} The component to be rendered.
 */
function PatternValidator( { value, pattern, message } ) {
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

const PARAMETER_TYPES = [ 'string', 'number', 'boolean', 'array', 'object' ];

const PARAMETER_TYPE_OPTIONS = PARAMETER_TYPES.map( ( type ) => ( {
	value: type,
	label: type,
} ) );
const PARAMETER_ITEM_TYPE_OPTIONS = PARAMETER_TYPE_OPTIONS.filter(
	( option ) => option.value !== 'array'
);

/**
 * Renders the group of input fields for a single function parameter.
 *
 * @since 0.5.0
 *
 * @param {Object}   props          The component props.
 * @param {string}   props.name     The parameter name.
 * @param {Object}   props.schema   The parameter schema.
 * @param {Function} props.onChange The function to call when the parameter changes.
 * @param {Function} props.onDelete The function to call when the parameter is deleted.
 * @return {Component} The component to be rendered.
 */
function FunctionParameter( { name, schema, onChange, onDelete } ) {
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
						onChange={ ( value ) =>
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
						onChange={ ( value ) => {
							const newSchema = { ...schema, type: value };
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
						onChange={ ( value ) =>
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
						onChange={ ( value ) =>
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

/**
 * Renders the form for adding or editing a function declaration.
 *
 * @since 0.5.0
 *
 * @param {Object}  props                     The component props.
 * @param {?Object} props.functionDeclaration The function declaration to edit, or null if adding a new function.
 * @return {Component} The component to be rendered.
 */
function FunctionDeclarationForm( { functionDeclaration } ) {
	const {
		setFunctionDeclaration,
		deleteFunctionDeclaration,
		setActiveFunctionDeclaration,
	} = useDispatch( playgroundStore );

	const [ functionName, setFunctionName ] = useState( '' );
	const [ functionDescription, setFunctionDescription ] = useState( '' );
	const [ functionParameters, setFunctionParameters ] =
		useState( EMPTY_ARRAY );

	useEffect( () => {
		if ( ! functionDeclaration ) {
			setFunctionName( '' );
			setFunctionDescription( '' );
			setFunctionParameters( EMPTY_ARRAY );
			return;
		}

		setFunctionName( functionDeclaration.name );
		setFunctionDescription( functionDeclaration.description || '' );
		setFunctionParameters(
			functionDeclaration.parameters
				? parametersObjectToArray( functionDeclaration.parameters )
				: EMPTY_ARRAY
		);
	}, [
		functionDeclaration,
		setFunctionName,
		setFunctionDescription,
		setFunctionParameters,
	] );

	const addFunctionParameter = () => {
		setFunctionParameters( [
			...functionParameters,
			{ name: '', schema: { type: 'string', description: '' } },
		] );
	};

	const editFunctionParameter = ( newParameter, index ) => {
		const newParameters = [ ...functionParameters ];
		newParameters[ index ] = newParameter;
		setFunctionParameters( newParameters );
	};

	const deleteFunctionParameter = ( index ) => {
		const newParameters = [ ...functionParameters ];
		newParameters.splice( index, 1 );
		setFunctionParameters( newParameters );
	};

	const formSubmitDisabled = useMemo( () => {
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

	const saveChanges = () => {
		if ( formSubmitDisabled ) {
			return;
		}

		const existingFunctionName = functionDeclaration?.name;

		setFunctionDeclaration(
			functionName,
			functionDescription,
			parametersArrayToObject( functionParameters ),
			existingFunctionName
		);

		// If this is a new function or the function name changed, set the new function as active.
		if ( functionName !== existingFunctionName ) {
			setActiveFunctionDeclaration( functionName );
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
			onSubmit={ ( event ) => {
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
								deleteFunctionDeclaration(
									functionDeclaration.name
								);
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
						onChange={ ( newParameter ) =>
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

const getTabId = ( functionName ) => {
	if ( functionName ) {
		return `function-${ functionName }`;
	}
	return 'add-new-function';
};

const getFunctionNameFromTabId = ( tabId ) => {
	if ( tabId === 'add-new-function' ) {
		return '';
	}
	return tabId.replace( /^function-/, '' );
};

const SEARCH_FIELDS = [ 'name' ];

/**
 * Renders the modal for managing the available function declarations.
 *
 * @since 0.5.0
 *
 * @return {Component} The component to be rendered.
 */
export default function FunctionDeclarationsModal() {
	const { availableFunctionDeclarations, activeFunctionDeclaration } =
		useSelect( ( select ) => {
			const { getFunctionDeclarations, getActiveFunctionDeclaration } =
				select( playgroundStore );

			const available = getFunctionDeclarations();

			let activeName = getActiveFunctionDeclaration();
			if ( typeof activeName !== 'string' ) {
				activeName = available.length > 0 ? available[ 0 ].name : '';
			}

			return {
				availableFunctionDeclarations: available,
				activeFunctionDeclaration: activeName
					? available.find( ( { name } ) => name === activeName )
					: null,
			};
		} );

	const { setActiveFunctionDeclaration } = useDispatch( playgroundStore );

	const [ filteredFunctionDeclarations, setFilteredFunctionDeclarations ] =
		useState( availableFunctionDeclarations );

	const showFilter =
		availableFunctionDeclarations.length >=
		MIN_FUNCTION_DECLARATIONS_COUNT_FOR_FILTER;

	const functionDeclarationsToRender = showFilter
		? filteredFunctionDeclarations
		: availableFunctionDeclarations;

	return (
		<Modal
			identifier="function-declarations"
			title={ __( 'Manage function declarations', 'ai-services' ) }
			className="ai-services-playground-function-declarations-modal"
		>
			<div className="ai-services-playground-function-declarations-modal__content">
				<Tabs
					selectedTabId={ getTabId(
						activeFunctionDeclaration?.name
					) }
					onSelect={ ( tabId ) => {
						setActiveFunctionDeclaration(
							getFunctionNameFromTabId( tabId )
						);
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
								options={ availableFunctionDeclarations }
								onFilter={ setFilteredFunctionDeclarations }
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
							( functionDeclaration ) => (
								<Tabs.TabPanel
									key={ functionDeclaration.name }
									tabId={ getTabId(
										functionDeclaration.name
									) }
									className="ai-services-playground-function-declarations-modal__function-declaration-panel"
								>
									<FunctionDeclarationForm
										functionDeclaration={
											functionDeclaration
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
