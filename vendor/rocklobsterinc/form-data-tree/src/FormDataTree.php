<?php

namespace RockLobsterInc\FormDataTree;

use function RockLobsterInc\Functions\{ strip_whitespaces, exclude_blank };

/**
 * A class that implements FormDataTreeInterface. Wraps the PHP superglobals.
 */
class FormDataTree implements FormDataTreeInterface {

	/**
	 * Returns the values associated with a given field name.
	 *
	 * @param string $name Field name.
	 * @return iterable Iterator of the values.
	 */
	public function getAll( string $name ): iterable {
		$name_parts = dissolve_name( $name );

		if ( empty( $name_parts ) ) {
			return [];
		}

		$posted_value = $_POST;

		while ( $next = array_shift( $name_parts ) ) {
			if (
				preg_match( '/^[0-9]*$/', $next ) or
				! isset( $posted_value[ $next ] )
			) {
				return [];
			}

			$posted_value = $posted_value[ $next ];
		}

		if ( ! is_array( $posted_value ) ) {
			$posted_value = [ $posted_value ];
		}

		$posted_value = strip_whitespaces( $posted_value );
		$posted_value = exclude_blank( $posted_value );

		return $posted_value;
	}


	/**
	 * Returns the file objects associated with a given field name.
	 *
	 * @param string $name Field name.
	 * @return iterable Iterator of the FileInterface objects.
	 */
	public function getAllFiles( string $name ): iterable {
		$name_parts = dissolve_name( $name );

		if ( empty( $name_parts ) ) {
			return [];
		}

		$files_tree = File::buildTree();

		while ( $next = array_shift( $name_parts ) ) {
			if (
				preg_match( '/^[0-9]*$/', $next ) or
				! isset( $files_tree[ $next ] )
			) {
				return [];
			}

			$files_tree = $files_tree[ $next ];
		}

		if ( ! is_array( $files_tree ) ) {
			$files_tree = [ $files_tree ];
		}

		$files_tree = exclude_blank( $files_tree );

		return $files_tree;
	}

}
