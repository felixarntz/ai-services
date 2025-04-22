/**
 * WordPress dependencies
 */
import type { createRegistry } from '@wordpress/data';

type WPDataRegistry = ReturnType< typeof createRegistry >;

export interface Action< Type = string, Payload = Record< string, never > > {
	type: Type;
	payload: Payload;
}

type CurriedState< State extends object, F > = F extends (
	state: State,
	...args: infer P
) => infer R
	? ( ...args: P ) => R
	: F;

export type ThunkArgs<
	State extends object,
	ActionCreators extends MapOf< ActionCreator >,
	CombinedAction,
	Selectors extends MapOf< Selector >,
> = {
	select: {
		[ key in keyof Selectors ]: CurriedState< State, Selectors[ key ] >;
	};
	dispatch: ActionCreators & ( ( action: CombinedAction ) => void );
	registry: WPDataRegistry;
};

export type Dispatcher< DispatcherArgs > = (
	t: DispatcherArgs
) => void | Promise< void >;

type MapOf< T > = { [ name: string ]: T };

// eslint-disable-next-line @typescript-eslint/no-explicit-any
export type ActionCreator = ( ...args: any[] ) => any | Generator;
// eslint-disable-next-line @typescript-eslint/ban-types
export type Control = Function;
// eslint-disable-next-line @typescript-eslint/ban-types
export type Resolver = Function | Generator;
// eslint-disable-next-line @typescript-eslint/ban-types
export type Selector = Function;

// This is compatible with the `ReduxStoreConfig` type from '@wordpress/data'.
export interface StoreConfig<
	State extends object,
	ActionCreators extends MapOf< ActionCreator >,
	CombinedAction,
	Selectors extends MapOf< Selector >,
> {
	initialState?: State;
	actions?: ActionCreators;
	controls?: MapOf< Control >;
	reducer: ( state: State, action: CombinedAction ) => State;
	resolvers?: MapOf< Resolver >;
	selectors?: Selectors;
}
