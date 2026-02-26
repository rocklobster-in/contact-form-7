<?php

namespace RockLobsterInc\Functions;


/**
 * Converts multi-dimensional array to a flat array.
 *
 * @param mixed $input Array or item of array.
 * @return array Flatten array.
 */
function array_flatten( mixed $input ): array {
	if ( ! is_array( $input ) ) {
		return [ $input ];
	}

	return array_reduce( $input, static function ( $carry, $item ) {
		return array_merge( $carry, array_flatten( $item ) );
	}, [] );
}


/**
 * Strips surrounding whitespaces.
 *
 * @param string|array $input Input text or array of text.
 * @return string|array Output text or array of text.
 */
function strip_whitespaces( string|array $input ): string|array {
	if ( is_array( $input ) ) {
		return array_map( static function ( $item ) {
			return strip_whitespaces( $item );
		}, $input );
	}

	$whitespaces = '\x09-\x0D\x20\xA0\x{1680}\x{2000}-\x{200A}\x{2028}\x{2029}\x{202F}\x{205F}\x{3000}\x{FEFF}';

	$input = preg_replace(
		sprintf( '/^[%s]+/u', $whitespaces ),
		'',
		$input
	);

	$input = preg_replace(
		sprintf( '/[%s]+$/u', $whitespaces ),
		'',
		$input
	);

	return $input;
}


/**
 * Converts all newline characters into line feed (LF).
 *
 * @param string|array $input Input text or array of text.
 * @return string|array Output text or array of text.
 */
function canonicalize_newline( string|array $input ): string|array {
	if ( is_array( $input ) ) {
		return array_map( static function ( $item ) {
			return canonicalize_newline( $item );
		}, $input );
	}

	return str_replace( [ "\r\n", "\r", "\n" ], "\n", $input );
}


/**
 * Excludes unset or blank text values from the given array.
 *
 * @param array $input The array.
 * @return array Array without blank text values.
 */
function exclude_blank( array $input ): array {
	$output = [];

	foreach ( $input as $key => $item ) {
		if ( is_array( $item ) ) {
			$item = exclude_blank( $item );

			if ( ! empty( $item ) ) {
				$output[ $key ] = $item;
			}
		} elseif ( isset( $item ) ) {
			if ( '' !== $item ) {
				$output[ $key ] = $item;
			}
		}
	}

	return $output;
}
