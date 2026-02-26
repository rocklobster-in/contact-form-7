<?php

namespace RockLobsterInc\Swv;

use function RockLobsterInc\Functions\{ canonicalize_newline };


/**
 * Returns the number of code units in a string.
 *
 * @link http://www.w3.org/TR/html5/infrastructure.html#code-unit-length
 *
 * @param string $input Input string in the UTF-8 encoding.
 * @return int|false The number of code units, or false if mbstring is
 *                   not available.
 */
function count_code_units( string $input ): int|bool {
	if ( ! extension_loaded( 'mbstring' ) ) {
		return false;
	}

	if ( '' === $input ) {
		return 0;
	}

	$input = canonicalize_newline( $input );
	$input = mb_convert_encoding( $input, 'UTF-16', 'UTF-8' );

	return intdiv( mb_strlen( $input, '8bit' ), 2 );
}
