<?php

namespace RockLobsterInc\Swv\Rules;

use RockLobsterInc\FormDataTree\{ FormDataTreeInterface as FormDataTree };
use RockLobsterInc\Swv\{ AbstractRule, InvalidityException as Invalidity };
use function RockLobsterInc\Functions\{ array_flatten };

final class MinFilesizeRule extends AbstractRule {

	const RULE_NAME = 'minfilesize';


	/**
	 * Rule properties.
	 */
	public readonly string $field;
	public readonly string $error;
	public readonly string $threshold;


	/**
	 * Constructor.
	 *
	 * @param array $properties Rule properties.
	 */
	public function __construct( array $properties = [] ) {
		$this->field = $properties[ 'field' ] ?? '';
		$this->error = $properties[ 'error' ] ?? '';
		$this->threshold = $properties[ 'threshold' ] ?? '';
	}


	/**
	 * Returns true if this rule matches the given context.
	 *
	 * @param array $context Context.
	 */
	public function matches( array $context ): bool {
		if ( false === parent::matches( $context ) ) {
			return false;
		}

		if ( empty( $context[ 'file' ] ) ) {
			return false;
		}

		return true;
	}


	/**
	 * Validates the form data according to the logic defined by this rule.
	 *
	 * @param FormDataTree $form_data Form data.
	 * @param array $context Optional context.
	 */
	public function validate( FormDataTree $form_data, array $context = [] ) {
		$files = $form_data->getAllFiles( $this->field );
		$files = array_flatten( $files );

		if ( empty( $files ) ) {
			return true;
		}

		$file_size = array_reduce( $files, static function ( $carry, $item ) {
			$carry += $item->size();
			return $carry;
		}, 0 );

		if ( $file_size < (int) $this->threshold ) {
			throw new Invalidity( $this, [ 'cause' => $file_size ] );
		}

		return true;
	}


	/**
	 * Returns an array that represents the rule properties.
	 *
	 * @return array Array of rule properties.
	 */
	public function toArray(): array {
		return [
			'rule' => self::RULE_NAME,
			'field' => $this->field,
			'error' => $this->error,
			'threshold' => $this->threshold,
		];
	}

}
