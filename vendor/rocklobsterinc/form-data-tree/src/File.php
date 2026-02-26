<?php

namespace RockLobsterInc\FormDataTree;

use function RockLobsterInc\Functions\{ strip_whitespaces };

/**
 * Class that represents a standard file data in $_FILES.
 */
class File implements FileInterface {

	private string $name;
	private int $size;
	private string $temporaryFilePath;
	private int $error;


	/**
	 * Constructor.
	 *
	 * @param array $properties Properties of the object.
	 */
	private function __construct( array $properties = [] ) {
		$this->name = $properties[ 'name' ];
		$this->size = $properties[ 'size' ];
		$this->temporaryFilePath = $properties[ 'temporaryFilePath' ];
		$this->error = $properties[ 'error' ];
	}


	/**
	 * Walks through an array and creates File objects if there are
	 * necessary properties.
	 *
	 * @param mixed $array Where to walk.
	 */
	private static function walkToFindSelf( mixed $array ) {
		if (
			isset( $array[ 'name' ] ) and
			isset( $array[ 'size' ] ) and
			isset( $array[ 'tmp_name' ] ) and
			isset( $array[ 'error' ] )
		) {
			$name = strip_whitespaces( (string) $array[ 'name' ] );
			$size = (int) $array[ 'size' ];
			$tmp_name = strip_whitespaces( (string) $array[ 'tmp_name' ] );
			$error = (int) $array[ 'error' ];

			if ( '' !== $name and 0 !== $size ) {
				return new self( [
					'name' => $name,
					'size' => $size,
					'temporaryFilePath' => $tmp_name,
					'error' => $error,
				] );
			} else {
				return;
			}
		}

		if ( is_array( $array ) ) {
			return array_map( 'self::walkToFindSelf', $array );
		}
	}


	/**
	 * Creates a tree structure based on the $_FILES superglobal. A terminal
	 * node of the tree must be a File object.
	 *
	 * @return array Tree created based on $_FILES.
	 */
	public static function buildTree(): array {
		static $output = [];

		if ( ! empty( $output ) ) {
			return $output;
		}

		$original = $_FILES;

		foreach ( $original as $name => $props ) {
			$in_process_array = [];

			foreach ( $props as $key => $value ) {
				$in_process_array[] = scalar_to_map( $key, $value );
			}

			$in_process_array = array_replace_recursive( ...$in_process_array );

			$output[ $name ] = self::walkToFindSelf( $in_process_array );
		}

		return $output;
	}


	/**
	 * Returns the original name of the file on the client machine.
	 */
	public function name(): string {
		return $this->name;
	}


	/**
	 * Returns the size, in bytes, of the file.
	 */
	public function size(): int {
		return $this->size;
	}


	/**
	 * Returns the full path of the file in which the uploaded file was
	 * stored on the server.
	 */
	public function temporaryFilePath(): string {
		return $this->temporaryFilePath;
	}


	/**
	 * Returns the error code associated with this file upload.
	 */
	public function error(): int {
		return $this->error;
	}

}
