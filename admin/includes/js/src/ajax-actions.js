import { addQueryArgs } from '@wordpress/url';

export const init = () => {
  dashboardPrimary();
};

const dashboardPrimary = () => {
  const url = addQueryArgs( ajaxurl, {
    action: 'wpcf7-dashboard-widgets',
    widget: 'dashboard_primary',
  } );

 	fetch( new Request( url, {
		method: 'GET',
	} ) );
};
