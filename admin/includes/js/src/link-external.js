import { __ } from '@wordpress/i18n';

const externals = [
  'https://contactform7.com/',
  'https://my.sendinblue.com/',
];

export const externalizeAll = () => {
  document.querySelectorAll(
    '#wpcf7-contact-form-editor a[href]'
  ).forEach( anchor => externalize( anchor ) );
};

export const externalize = anchor => {
  if ( ! anchor.matches( 'a[href]' ) ) {
    return false;
  }

  const href = anchor.getAttribute( 'href' );

  if ( ! externals.some( url => href.startsWith( url ) ) ) {
    return false;
  }

  anchor.setAttribute( 'target', '_blank' );
  anchor.setAttribute( 'rel', 'external noreferrer noopener' );

  if ( ! anchor.querySelector( '.dashicons-external' ) ) {
    const dashicon = document.createElement( 'span' );
    dashicon.classList.add( 'dashicons', 'dashicons-external' );
    dashicon.setAttribute( 'aria-hidden', 'true' );
    anchor.append( ' ', dashicon );
  }

  if ( ! anchor.querySelector( '.screen-reader-text' ) ) {
    const screenReaderText = document.createElement( 'span' );
    screenReaderText.classList.add( 'screen-reader-text' );
    screenReaderText.textContent = __( '(opens in a new tab)', 'contact-form-7' );
    anchor.append( screenReaderText );
  }
};
