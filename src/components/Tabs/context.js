/**
 * WordPress dependencies
 */
import { createContext, useContext } from '@wordpress/element';

export const TabsContext = createContext( undefined );

export const useTabsContext = () => useContext( TabsContext );
