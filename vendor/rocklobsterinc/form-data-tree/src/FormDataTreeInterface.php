<?php

namespace RockLobsterInc\FormDataTree;

/**
 * An interface that represents form submission data in the key/value pairs
 * format.
 */
interface FormDataTreeInterface {

	/**
	 * Returns the values associated with a given field name.
	 *
	 * @param string $name Field name.
	 * @return iterable Iterator of the values.
	 */
	public function getAll( string $name ): iterable;


	/**
	 * Returns the file objects associated with a given field name.
	 *
	 * @param string $name Field name.
	 * @return iterable Iterator of the file objects.
	 */
	public function getAllFiles( string $name ): iterable;

}
