/**
 * Returns the value for the given preference and the given scope.
 *
 * @param state - The store state.
 * @param scope - The scope of the feature (e.g. core/edit-post).
 * @param name  - The name of the feature.
 * @returns Is the feature enabled?
 */
export function get( state: Object, scope: string, name: string ): unknown;
