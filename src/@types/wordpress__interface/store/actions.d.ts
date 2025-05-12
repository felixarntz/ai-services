/**
 * Sets a default complementary area.
 *
 * @param scope - Complementary area scope.
 * @param area  - Area identifier.
 * @returns Action creator.
 */
export function setDefaultComplementaryArea(
	scope: string,
	area: string
): void;

/**
 * Enables the complementary area.
 *
 * @param scope - Complementary area scope.
 * @param area  - Area identifier.
 */
export function enableComplementaryArea( scope: string, area: string ): void;

/**
 * Disables the complementary area.
 *
 * @param scope - Complementary area scope.
 */
export function disableComplementaryArea( scope: string ): void;

/**
 * Pins an item.
 *
 * @param scope - Item scope.
 * @param item  - Item identifier.
 * @returns Action creator.
 */
export function pinItem( scope: string, item: string ): void;

/**
 * Unpins an item.
 *
 * @param scope - Item scope.
 * @param item  - Item identifier.
 * @returns Action creator.
 */
export function unpinItem( scope: string, item: string ): void;

/**
 * Opens a modal.
 *
 * @param name - A string that uniquely identifies the modal.
 * @returns Action creator.
 */
export function openModal( name: string ): void;

/**
 * Closes a modal.
 *
 * @returns Action creator.
 */
export function closeModal(): void;
