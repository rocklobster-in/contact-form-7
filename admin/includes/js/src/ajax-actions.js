import { addQueryArgs } from '@wordpress/url';

import { externalizeAll } from './link-external.js';

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
  const id = document.querySelector( '[name="post_ID"]' )?.value;
  const td = document.querySelector( '#wpcf7-sendinblue-editor-lists' );

  if ( id && td ) {
    const url = addQueryArgs( ajaxurl, {
      action: 'wpcf7-sendinblue-contact-lists',
      id,
    } );

    fetch( url )
      .then( ( response ) => response.text() )
      .then( ( text ) => {
        td.innerHTML = text;
      } )
      .then( externalizeAll );
  }
};

const sendinblueEmailTemplate = () => {
  const id = document.querySelector( '[name="post_ID"]' )?.value;
  const td = document.querySelector( '#wpcf7-sendinblue-editor-templates' );

  if ( id && td ) {
    const url = addQueryArgs( ajaxurl, {
      action: 'wpcf7-sendinblue-email-template',
      id,
    } );

    fetch( url )
      .then( ( response ) => response.text() )
      .then( ( text ) => {
        td.innerHTML = text;
      } )
      .then( externalizeAll );
  }
};
