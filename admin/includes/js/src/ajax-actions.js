import { addQueryArgs } from '@wordpress/url';

export const init = () => {
  dashboardPrimary();
  sendinblue();
};

const dashboardPrimary = () => {
  const newsArea = document.querySelector(
    '#wpcf7_dashboard_news .cf7com-news'
  );

  if ( newsArea && ! newsArea.querySelector( '.rss-widget' ) ) {
    const url = addQueryArgs( ajaxurl, {
      action: 'wpcf7-dashboard-widgets',
      widget: 'dashboard_primary',
    } );

    fetch( url )
      .then( ( response ) => response.text() )
      .then( ( text ) => {
        newsArea.innerHTML = text;
      } );
  }
};

const sendinblue = () => {
  const url = addQueryArgs( ajaxurl, {
    action: 'wpcf7-sendinblue',
  } );

  fetch( url );
};
