/**
 * Toggles a preference.
 *
 * @param scope - The preference scope (e.g. core/edit-post).
 * @param name  - The preference name.
 * @returns Action creator.
 */
export function toggle( scope: string, name: string ): void;

/**
 * Sets a preference to a value
 *
 * @param scope - The preference scope (e.g. core/edit-post).
 * @param name  - The preference name.
 * @param value - The value to set.
 * @returns Action creator.
 */
export function set( scope: string, name: string, value: unknown ): void;

/**
 * Sets preference defaults.
 *
 * @param scope    - The preference scope (e.g. core/edit-post).
 * @param defaults - A key/value map of preference names to values.
 * @returns Action creator.
 */
export function setDefaults(
	scope: string,
	defaults: Record< string, unknown >
): void;

export type WPPreferencesPersistenceLayerGet = () => Promise< Object >;
export type WPPreferencesPersistenceLayerSet = ( data: Object ) => void;
export type WPPreferencesPersistenceLayer = {
	get: WPPreferencesPersistenceLayerGet;
	set: WPPreferencesPersistenceLayerSet;
};

/**
 * Sets the persistence layer.
 *
 * When a persistence layer is set, the preferences store will:
 * - call `get` immediately and update the store state to the value returned.
 * - call `set` with all preferences whenever a preference changes value.
 *
 * `setPersistenceLayer` should ideally be dispatched at the start of an
 * application's lifecycle, before any other actions have been dispatched to
 * the preferences store.
 *
 * @param persistenceLayer - The persistence layer.
 * @returns Action creator.
 */
export function setPersistenceLayer(
	persistenceLayer: WPPreferencesPersistenceLayer
): Promise< void >;
