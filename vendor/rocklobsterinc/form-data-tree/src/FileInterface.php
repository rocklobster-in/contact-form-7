<?php

namespace RockLobsterInc\FormDataTree;

/**
 * Interface that represents an uploaded file.
 */
interface FileInterface {

	/**
	 * Returns the original name of the file on the client machine.
	 */
	public function name(): string;


	/**
	 * Returns the size, in bytes, of the file.
	 */
	public function size(): int;


	/**
	 * Returns the full path of the file in which the uploaded file was
	 * stored on the server.
	 */
	public function temporaryFilePath(): string;


	/**
	 * Returns the error code associated with this file upload.
	 */
	public function error(): int;

}
