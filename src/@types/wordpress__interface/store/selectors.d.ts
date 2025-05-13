/**
 * Returns the complementary area that is active in a given scope.
 *
 * @param state - Global application state.
 * @param scope - Item scope.
 * @returns The complementary area that is active in the given scope.
 */
export function getActiveComplementaryArea(
	state: Object,
	scope: string
): string | null | undefined;

/**
 * Returns a boolean indicating if the complementary area is loading or not.
 *
 * @param state - Global application state.
 * @param scope - Scope.
 * @returns True if the area is loading and false otherwise.
 */
export function isComplementaryAreaLoading(
	state: Object,
	scope: string
): boolean;

/**
 * Returns a boolean indicating if an item is pinned or not.
 *
 * @param state - Global application state.
 * @param scope - Scope.
 * @param item  - Item to check.
 * @returns True if the item is pinned and false otherwise.
 */
export function isItemPinned(
	state: Object,
	scope: string,
	item: string
): boolean;

/**
 * Returns true if a modal is active, or false otherwise.
 *
 * @param state     - Global application state.
 * @param modalName - A string that uniquely identifies the modal.
 * @returns Whether the modal is active.
 */
export function isModalActive( state: Object, modalName: string ): boolean;
