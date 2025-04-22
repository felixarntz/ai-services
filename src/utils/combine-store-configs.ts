/**
 * Internal dependencies
 */
import type {
	StoreConfig,
	ActionCreator,
	Control,
	Resolver,
	Selector,
} from '../utils/store-types';

type AnyAction = {
	type: string;
	payload?: object;
};

/**
 * Finds all duplicate items in an array and throws an error if there are any.
 *
 * @param keys    - Array of keys to check for duplicates.
 * @param keyType - Type of keys (e.g. 'initialState', 'actions', etc.). Used only for the error message.
 */
function findDuplicates( keys: string[], keyType: string ): void {
	const duplicates = [];
	const counts: Record< string, number > = {};

	for ( let i = 0; i < keys.length; i++ ) {
		const item = keys[ i ];
		counts[ item ] = counts[ item ] >= 1 ? counts[ item ] + 1 : 1;
		if ( counts[ item ] > 1 ) {
			duplicates.push( item );
		}
	}

	if ( duplicates.length ) {
		throw new Error(
			`combineStoreConfigs() was called with ${ keyType } with duplicate keys. The duplicate keys are: ${ duplicates.join(
				', '
			) }. Check your partial data store configs for duplicates.`
		);
	}
}

type MapOf< T > = { [ name: string ]: T };

/**
 * Combines multiple store configs into one.
 *
 * While in principle this function could be implemented accepting an array of store configs, such an implementation
 * would not be type safe. The current implementation looks a bit cumbersome, but it is type safe as it treats every
 * store config individually, as their types are different.
 *
 * This also means that the function cannot be used to combine an arbitrary number of store configs, but only up to a
 * limit. For now, this limit is set to 6, which should be sufficient for most use cases.
 *
 * @param storeConfig1 - The first store config.
 * @param storeConfig2 - Optional. The second store config. Default is none (undefined).
 * @param storeConfig3 - Optional. The third store config. Default is none (undefined).
 * @param storeConfig4 - Optional. The fourth store config. Default is none (undefined).
 * @param storeConfig5 - Optional. The fifth store config. Default is none (undefined).
 * @param storeConfig6 - Optional. The sixth store config. Default is none (undefined).
 * @returns The combined store config.
 */
export default function combineStoreConfigs<
	A extends object,
	B extends MapOf< ActionCreator >,
	C extends AnyAction,
	D extends MapOf< Selector >,
	E extends object,
	F extends MapOf< ActionCreator >,
	G extends AnyAction,
	H extends MapOf< Selector >,
	I extends object,
	J extends MapOf< ActionCreator >,
	K extends AnyAction,
	L extends MapOf< Selector >,
	M extends object,
	N extends MapOf< ActionCreator >,
	O extends AnyAction,
	P extends MapOf< Selector >,
	Q extends object,
	R extends MapOf< ActionCreator >,
	S extends AnyAction,
	T extends MapOf< Selector >,
	U extends object,
	V extends MapOf< ActionCreator >,
	W extends AnyAction,
	X extends MapOf< Selector >,
>(
	storeConfig1: StoreConfig< A, B, C, D >,
	storeConfig2?: StoreConfig< E, F, G, H >,
	storeConfig3?: StoreConfig< I, J, K, L >,
	storeConfig4?: StoreConfig< M, N, O, P >,
	storeConfig5?: StoreConfig< Q, R, S, T >,
	storeConfig6?: StoreConfig< U, V, W, X >
): StoreConfig<
	A & E & I & M & Q & U,
	B & F & J & N & R & V,
	C | G | K | O | S | W,
	D & H & L & P & T & X
> {
	// Ensure there are no duplicate keys in any of the store configs' inner objects.
	findDuplicates(
		[
			...Object.keys( storeConfig1.initialState || {} ),
			...Object.keys( storeConfig2?.initialState || {} ),
			...Object.keys( storeConfig3?.initialState || {} ),
			...Object.keys( storeConfig4?.initialState || {} ),
			...Object.keys( storeConfig5?.initialState || {} ),
			...Object.keys( storeConfig6?.initialState || {} ),
		],
		'initialState'
	);
	findDuplicates(
		[
			...Object.keys( storeConfig1.actions || {} ),
			...Object.keys( storeConfig2?.actions || {} ),
			...Object.keys( storeConfig3?.actions || {} ),
			...Object.keys( storeConfig4?.actions || {} ),
			...Object.keys( storeConfig5?.actions || {} ),
			...Object.keys( storeConfig6?.actions || {} ),
		],
		'actions'
	);
	findDuplicates(
		[
			...Object.keys( storeConfig1.controls || {} ),
			...Object.keys( storeConfig2?.controls || {} ),
			...Object.keys( storeConfig3?.controls || {} ),
			...Object.keys( storeConfig4?.controls || {} ),
			...Object.keys( storeConfig5?.controls || {} ),
			...Object.keys( storeConfig6?.controls || {} ),
		],
		'controls'
	);
	findDuplicates(
		[
			...Object.keys( storeConfig1.resolvers || {} ),
			...Object.keys( storeConfig2?.resolvers || {} ),
			...Object.keys( storeConfig3?.resolvers || {} ),
			...Object.keys( storeConfig4?.resolvers || {} ),
			...Object.keys( storeConfig5?.resolvers || {} ),
			...Object.keys( storeConfig6?.resolvers || {} ),
		],
		'resolvers'
	);
	findDuplicates(
		[
			...Object.keys( storeConfig1.selectors || {} ),
			...Object.keys( storeConfig2?.selectors || {} ),
			...Object.keys( storeConfig3?.selectors || {} ),
			...Object.keys( storeConfig4?.selectors || {} ),
			...Object.keys( storeConfig5?.selectors || {} ),
			...Object.keys( storeConfig6?.selectors || {} ),
		],
		'selectors'
	);

	// Merge all store configs' inner objects.
	const mergedInitialState: A & E & I & M & Q & U = {
		...( storeConfig1.initialState || {} ),
		...( storeConfig2?.initialState || {} ),
		...( storeConfig3?.initialState || {} ),
		...( storeConfig4?.initialState || {} ),
		...( storeConfig5?.initialState || {} ),
		...( storeConfig6?.initialState || {} ),
	} as A & E & I & M & Q & U;
	const mergedActions: B & F & J & N & R & V = {
		...( storeConfig1.actions || {} ),
		...( storeConfig2?.actions || {} ),
		...( storeConfig3?.actions || {} ),
		...( storeConfig4?.actions || {} ),
		...( storeConfig5?.actions || {} ),
		...( storeConfig6?.actions || {} ),
	} as B & F & J & N & R & V;
	const mergedControls: MapOf< Control > = {
		...( storeConfig1.controls || {} ),
		...( storeConfig2?.controls || {} ),
		...( storeConfig3?.controls || {} ),
		...( storeConfig4?.controls || {} ),
		...( storeConfig5?.controls || {} ),
		...( storeConfig6?.controls || {} ),
	} as MapOf< Control >;
	const mergedResolvers: MapOf< Resolver > = {
		...( storeConfig1.resolvers || {} ),
		...( storeConfig2?.resolvers || {} ),
		...( storeConfig3?.resolvers || {} ),
		...( storeConfig4?.resolvers || {} ),
		...( storeConfig5?.resolvers || {} ),
		...( storeConfig6?.resolvers || {} ),
	};
	const mergedSelectors: D & H & L & P & T & X = {
		...( storeConfig1.selectors || {} ),
		...( storeConfig2?.selectors || {} ),
		...( storeConfig3?.selectors || {} ),
		...( storeConfig4?.selectors || {} ),
		...( storeConfig5?.selectors || {} ),
		...( storeConfig6?.selectors || {} ),
	} as D & H & L & P & T & X;

	// Merge all store configs' reducers.
	const mergedReducer = (
		state: A & E & I & M & Q & U,
		action: C | G | K | O | S | W
	): A & E & I & M & Q & U => {
		let sliceOfState = state as A & E & I & M & Q & U;

		const nextSlice1 = storeConfig1.reducer( sliceOfState, action as C );
		if ( nextSlice1 !== sliceOfState ) {
			sliceOfState = { ...sliceOfState, ...nextSlice1 };
		}
		if ( storeConfig2 !== undefined ) {
			const nextSlice2 = storeConfig2.reducer(
				sliceOfState,
				action as G
			);
			if ( nextSlice2 !== sliceOfState ) {
				sliceOfState = { ...sliceOfState, ...nextSlice2 };
			}
		}
		if ( storeConfig3 !== undefined ) {
			const nextSlice3 = storeConfig3.reducer(
				sliceOfState,
				action as K
			);
			if ( nextSlice3 !== sliceOfState ) {
				sliceOfState = { ...sliceOfState, ...nextSlice3 };
			}
		}
		if ( storeConfig4 !== undefined ) {
			const nextSlice4 = storeConfig4.reducer(
				sliceOfState,
				action as O
			);
			if ( nextSlice4 !== sliceOfState ) {
				sliceOfState = { ...sliceOfState, ...nextSlice4 };
			}
		}
		if ( storeConfig5 !== undefined ) {
			const nextSlice5 = storeConfig5.reducer(
				sliceOfState,
				action as S
			);
			if ( nextSlice5 !== sliceOfState ) {
				sliceOfState = { ...sliceOfState, ...nextSlice5 };
			}
		}
		if ( storeConfig6 !== undefined ) {
			const nextSlice6 = storeConfig6.reducer(
				sliceOfState,
				action as W
			);
			if ( nextSlice6 !== sliceOfState ) {
				sliceOfState = { ...sliceOfState, ...nextSlice6 };
			}
		}

		return sliceOfState;
	};

	// Return the combined result, omitting unnecessary properties.
	return {
		initialState:
			Object.keys( mergedInitialState ).length > 0
				? mergedInitialState
				: undefined,
		actions:
			Object.keys( mergedActions ).length > 0 ? mergedActions : undefined,
		controls:
			Object.keys( mergedControls ).length > 0
				? mergedControls
				: undefined,
		reducer: mergedReducer,
		resolvers:
			Object.keys( mergedResolvers ).length > 0
				? mergedResolvers
				: undefined,
		selectors:
			Object.keys( mergedSelectors ).length > 0
				? mergedSelectors
				: undefined,
	};
}
