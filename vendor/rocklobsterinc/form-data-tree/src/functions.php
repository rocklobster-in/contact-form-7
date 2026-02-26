<?php

namespace RockLobsterInc\FormDataTree;

use function RockLobsterInc\Functions\{ strip_whitespaces };


/**
 * Returns components of the given name.
 *
 * @param string $name Field name, such as 'abc', 'abc[de]', or 'abc[]'.
 * @return array Single dimension array of name components.
 */
function dissolve_name( string $name ): array {
	$name = strip_whitespaces( $name );

	$s = '[\x09-\x0D\x20\xA0\x{1680}\x{2000}-\x{200A}\x{2028}\x{2029}\x{202F}\x{205F}\x{3000}\x{FEFF}]*';

	$pattern = '/^([a-z][0-9a-z:_-]*)' .
		'((?:\[' . $s . '[a-z][0-9a-z:_-]*' . $s . '\])*)$/iu';

	if ( ! preg_match( $pattern, $name, $matches ) ) {
		return [];
	}

	$core = $matches[ 1 ];
	$layers = $matches[ 2 ];

	preg_match_all(
		'/\[' . $s . '([a-z][0-9a-z:_-]*)' . $s . '\]/iu',
		$layers,
		$matches
	);

	return [ $core, ...$matches[ 1 ] ];
}


/**
 * Converts a scalar value into a map with a specified key. The original
 * array structure will be preserved.
 *
 * @param string $key Map key.
 * @param mixed $value Original value.
 * @return array Array.
 */
function scalar_to_map( string $key, mixed $value ): array {
	if ( is_scalar( $value ) ) {
		return [ $key => $value ];
	}

	if ( is_array( $value ) ) {
		return array_map( static function ( $item ) use ( $key ) {
			return scalar_to_map( $key, $item );
		}, $value );
	}

	return [];
}
