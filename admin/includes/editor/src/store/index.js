import { createReduxStore, register } from '@wordpress/data';

import { STORE_NAME } from './constants';

export const store = createReduxStore( STORE_NAME, {
	reducer: ( state, action ) => state,
} );

register( store );
