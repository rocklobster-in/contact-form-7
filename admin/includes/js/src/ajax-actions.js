import { addQueryArgs } from '@wordpress/url';

export const init = () => {
  dashboardPrimary();
  sendinblueContactLists();
  sendinblueEmailTemplate();
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

const sendinblueContactLists = () => {
  const id = document.querySelector( '[name="post_ID"]' )?.value ?? '';

  const url = addQueryArgs( ajaxurl, {
    action: 'wpcf7-sendinblue-contact-lists',
    id,
  } );

  fetch( url );
};

const sendinblueEmailTemplate = () => {
  const id = document.querySelector( '[name="post_ID"]' )?.value ?? '';

  const url = addQueryArgs( ajaxurl, {
    action: 'wpcf7-sendinblue-email-template',
    id,
  } );

  fetch( url );
};
