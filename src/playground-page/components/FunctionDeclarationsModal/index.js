/**
 * External dependencies
 */
import { OptionsFilterSearchControl } from '@ai-services/components';
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
import { __, _x } from '@wordpress/i18n';
import { trash } from '@wordpress/icons';

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
 * @since n.e.x.t
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
 * @since n.e.x.t
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
 * @since n.e.x.t
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
 * @since n.e.x.t
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
					/>
					<Button
						icon={ trash }
						label={ __( 'Delete parameter', 'ai-services' ) }
						variant="secondary"
						isDestructive
						onClick={ onDelete }
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
					/>
				) }
			</Flex>
		</div>
	);
}

/**
 * Renders the modal for managing the available function declarations.
 *
 * @since n.e.x.t
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
		if ( ! activeFunctionDeclaration ) {
			setFunctionName( '' );
			setFunctionDescription( '' );
			setFunctionParameters( EMPTY_ARRAY );
			return;
		}

		setFunctionName( activeFunctionDeclaration.name );
		setFunctionDescription( activeFunctionDeclaration.description || '' );
		setFunctionParameters(
			activeFunctionDeclaration.parameters
				? parametersObjectToArray(
						activeFunctionDeclaration.parameters
				  )
				: EMPTY_ARRAY
		);
	}, [
		activeFunctionDeclaration,
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

		const existingFunctionName = activeFunctionDeclaration?.name;

		setFunctionDeclaration(
			functionName,
			functionDescription,
			parametersArrayToObject( functionParameters ),
			existingFunctionName
		);

		// If this is a new function or the function name changed, set the new function as active.
		if ( functionName !== existingFunctionName ) {
			setActiveFunctionDeclaration( functionName );
		}
	};

	// Get option objects for available function declarations to render in the checkbox list.
	const functionDeclarationOptions = useMemo( () => {
		return availableFunctionDeclarations.map( ( functionDeclaration ) => {
			return {
				value: functionDeclaration.name,
				label: `${ functionDeclaration.name }()`,
			};
		} );
	}, [ availableFunctionDeclarations ] );
	const [
		filteredFunctionDeclarationOptions,
		setFilteredFunctionDeclarationOptions,
	] = useState( functionDeclarationOptions );

	const showFilter =
		availableFunctionDeclarations.length >=
		MIN_FUNCTION_DECLARATIONS_COUNT_FOR_FILTER;

	const functionDeclarationOptionsToRender = showFilter
		? filteredFunctionDeclarationOptions
		: functionDeclarationOptions;

	return (
		<Modal
			identifier="function-declarations"
			title={ __( 'Manage function declarations', 'ai-services' ) }
			className="ai-services-playground-function-declarations-modal"
		>
			<div className="ai-services-playground-function-declarations-modal__content">
				<div className="ai-services-playground-function-declarations-modal__sidebar">
					{ showFilter && (
						<OptionsFilterSearchControl
							label={ __( 'Search functions', 'ai-services' ) }
							className="ai-services-playground-function-declarations-modal__search-control"
							options={ functionDeclarationOptions }
							onFilter={ setFilteredFunctionDeclarationOptions }
						/>
					) }
					<div className="ai-services-playground-function-declarations-modal__function-declarations-list">
						{ functionDeclarationOptionsToRender.map(
							( { value, label } ) => (
								<Button
									key={ value }
									label={
										__( 'Edit function:', 'ai-services' ) +
										' ' +
										label
									}
									className="ai-services-playground-function-declarations-modal__function-declaration"
									isPressed={
										activeFunctionDeclaration?.name ===
										value
									}
									onClick={ () => {
										setActiveFunctionDeclaration( value );
									} }
								>
									{ label }
								</Button>
							)
						) }
					</div>
					<Button
						className="ai-services-playground-function-declarations-modal__add-function-declaration"
						onClick={ () => {
							setActiveFunctionDeclaration( '' );
						} }
						variant="link"
					>
						{ __( 'Add new function', 'ai-services' ) }
					</Button>
				</div>
				<div className="ai-services-playground-function-declarations-modal__main">
					<form
						className="ai-services-playground-function-declarations-modal__form"
						onSubmit={ ( event ) => {
							event.preventDefault();
							saveChanges();
						} }
					>
						<Flex gap="4">
							<h2 className="ai-services-playground-function-declarations-modal__title">
								{ !! activeFunctionDeclaration ? (
									<>
										{ __(
											'Edit function:',
											'ai-services'
										) + ' ' }
										<span>{ `${ functionName }()` }</span>
									</>
								) : (
									__( 'Add new function', 'ai-services' )
								) }
							</h2>
							<div className="ai-services-playground-function-declarations-modal__actions">
								{ !! activeFunctionDeclaration && (
									<Button
										icon={ trash }
										label={ __(
											'Delete function',
											'ai-services'
										) }
										className="ai-services-playground-function-declarations-modal__delete"
										variant="secondary"
										isDestructive
										onClick={ () => {
											deleteFunctionDeclaration(
												activeFunctionDeclaration.name
											);
										} }
									/>
								) }
								<Button
									type="submit"
									variant="primary"
									className="ai-services-playground-function-declarations-modal__submit"
									disabled={ formSubmitDisabled }
								>
									{ !! activeFunctionDeclaration
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
								label={ __(
									'Function description',
									'ai-services'
								) }
								className="ai-services-playground-function-declarations-modal__function-description"
								value={ functionDescription }
								onChange={ setFunctionDescription }
								__nextHasNoMarginBottom
							/>
							{ functionParameters.map( ( parameter, index ) => (
								<FunctionParameter
									key={ index }
									name={ parameter.name }
									schema={ parameter.schema }
									onChange={ ( newParameter ) =>
										editFunctionParameter(
											newParameter,
											index
										)
									}
									onDelete={ () =>
										deleteFunctionParameter( index )
									}
								/>
							) ) }
							<Flex gap="4">
								<Button
									type="button"
									variant="secondary"
									className="ai-services-playground-function-declarations-modal__add-parameter"
									onClick={ addFunctionParameter }
								>
									{ __( 'Add parameter', 'ai-services' ) }
								</Button>
								<Button
									type="submit"
									variant="primary"
									className="ai-services-playground-function-declarations-modal__submit"
									disabled={ formSubmitDisabled }
								>
									{ !! activeFunctionDeclaration
										? __( 'Save changes', 'ai-services' )
										: __( 'Save', 'ai-services' ) }
								</Button>
							</Flex>
						</Flex>
					</form>
				</div>
			</div>
		</Modal>
	);
}
