<?php

/**
 * Checks whether a string is a valid NAME token.
 *
 * ID and NAME tokens must begin with a letter ([A-Za-z])
 * and may be followed by any number of letters, digits ([0-9]),
 * hyphens ("-"), underscores ("_"), colons (":"), and periods (".").
 *
 * @see http://www.w3.org/TR/html401/types.html#h-6.2
 *
 * @return bool True if it is a valid name, false if not.
 */
function wpcf7_is_name( $string ) {
	return preg_match( '/^[A-Za-z][-A-Za-z0-9_:.]*$/', $string );
}

function wpcf7_is_email( $email ) {
	$result = is_email( $email );
	return apply_filters( 'wpcf7_is_email', $result, $email );
}

function wpcf7_is_url( $url ) {
	$result = ( false !== filter_var( $url, FILTER_VALIDATE_URL ) );
	return apply_filters( 'wpcf7_is_url', $result, $url );
}

function wpcf7_is_tel( $tel ) {
	$pattern = '%^[+]?' // + sign
		. '(?:\([0-9]+\)|[0-9]+)' // (1234) or 1234
		. '(?:[/ -]*' // delimiter
		. '(?:\([0-9]+\)|[0-9]+)' // (1234) or 1234
		. ')*$%';

	$result = preg_match( $pattern, trim( $tel ) );
	return apply_filters( 'wpcf7_is_tel', $result, $tel );
}

function wpcf7_is_number( $number ) {
	$result = is_numeric( $number );
	return apply_filters( 'wpcf7_is_number', $result, $number );
}

function wpcf7_is_date( $date ) {
	$result = preg_match( '/^([0-9]{4,})-([0-9]{2})-([0-9]{2})$/', $date, $matches );

	if ( $result ) {
		$result = checkdate( $matches[2], $matches[3], $matches[1] );
	}

	return apply_filters( 'wpcf7_is_date', $result, $date );
}

function wpcf7_is_mailbox_list( $mailbox_list ) {
	if ( ! is_array( $mailbox_list ) ) {
		$mailbox_text = (string) $mailbox_list;
		$mailbox_text = wp_unslash( $mailbox_text );

		$mailbox_text = preg_replace( '/\\\\(?:\"|\')/', 'esc-quote',
			$mailbox_text );

		$mailbox_text = preg_replace( '/(?:\".*?\"|\'.*?\')/', 'quoted-string',
			$mailbox_text );

		$mailbox_list = explode( ',', $mailbox_text );
	}

	$addresses = array();

	foreach ( $mailbox_list as $mailbox ) {
		if ( ! is_string( $mailbox ) ) {
			return false;
		}

		$mailbox = trim( $mailbox );

		if ( preg_match( '/<(.+)>$/', $mailbox, $matches ) ) {
			$addr_spec = $matches[1];
		} else {
			$addr_spec = $mailbox;
		}

		if ( ! wpcf7_is_email( $addr_spec ) ) {
			return false;
		}

		$addresses[] = $addr_spec;
	}

	return $addresses;
}

function wpcf7_is_email_in_domain( $email, $domain ) {
	$email_list = wpcf7_is_mailbox_list( $email );
	$domain = strtolower( $domain );

	foreach ( $email_list as $email ) {
		$email_domain = substr( $email, strrpos( $email, '@' ) + 1 );
		$email_domain = strtolower( $email_domain );
		$domain_parts = explode( '.', $domain );

		do {
			$site_domain = implode( '.', $domain_parts );

			if ( $site_domain == $email_domain ) {
				continue 2;
			}

			array_shift( $domain_parts );
		} while ( $domain_parts );

		return false;
	}

	return true;
}

function wpcf7_is_email_in_site_domain( $email ) {
	if ( wpcf7_is_localhost() ) {
		return true;
	}

	$site_domain = strtolower( $_SERVER['SERVER_NAME'] );

	if ( preg_match( '/^[0-9.]+$/', $site_domain ) ) { // 123.456.789.012
		return true;
	}

	if ( wpcf7_is_email_in_domain( $email, $site_domain ) ) {
		return true;
	}

	$home_url = home_url();

	// for interoperability with WordPress MU Domain Mapping plugin
	if ( is_multisite()
	and function_exists( 'domain_mapping_siteurl' ) ) {
		$domain_mapping_siteurl = domain_mapping_siteurl( false );

		if ( $domain_mapping_siteurl ) {
			$home_url = $domain_mapping_siteurl;
		}
	}

	if ( preg_match( '%^https?://([^/]+)%', $home_url, $matches ) ) {
		$site_domain = strtolower( $matches[1] );

		if ( $site_domain != strtolower( $_SERVER['SERVER_NAME'] )
		and wpcf7_is_email_in_domain( $email, $site_domain ) ) {
			return true;
		}
	}

	return false;
}

function wpcf7_is_file_path_in_content_dir( $path ) {
	if ( 0 === strpos( realpath( $path ), realpath( WP_CONTENT_DIR ) ) ) {
		return true;
	}

	if ( defined( 'UPLOADS' )
	and 0 === strpos( realpath( $path ), realpath( ABSPATH . UPLOADS ) ) ) {
		return true;
	}

	return false;
}
