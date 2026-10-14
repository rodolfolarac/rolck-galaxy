import { createContext, useContext } from '@wordpress/element';

export const SettingsContext = createContext( null );

/** { boot, get(path), getDefault(path), set(path, value) } */
export const useSettings = () => useContext( SettingsContext );
