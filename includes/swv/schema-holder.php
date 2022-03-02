<?php

trait WPCF7_SWV_SchemaHolder {

	protected $schema;


	/**
	 * Retrieves SWV schema for this holder object (contact form).
	 *
	 * @return WPCF7_SWV_Schema The schema object.
	 */
	public function get_schema() {
		if ( isset( $this->schema ) ) {
			return $this->schema;
		}

		$locale = is_callable( array( $this, 'locale' ) ) ? $this->locale() : '';

		$schema = new WPCF7_SWV_Schema( array(
			'locale' => $locale,
		) );

		$tags = $this->scan_form_tags();

		do_action( 'wpcf7_swv_pre_add_rules', $schema, $tags );

		foreach ( $tags as $tag ) {
			do_action( "wpcf7_swv_add_rules_for_{$tag->type}", $schema, $tag );
		}

		do_action( 'wpcf7_swv_add_rules', $schema, $tags );

		return $this->schema = $schema;
	}


	/**
	 * Validates form inputs based on the schema and given context.
	 */
	public function validate_schema( $context, WPCF7_Validation $validity ) {
		$callback = function ( $rule ) use ( &$callback, $context, $validity ) {
			if ( ! $rule->match( $context ) ) {
				return;
			}

			if ( $rule instanceof WPCF7_SWV_CompositeRule ) {
				foreach ( $rule->rules() as $child_rule ) {
					call_user_func( $callback, $child_rule );
				}
			} else {
				$field = $rule->get_property( 'field' );

				if ( $validity->is_valid( $field ) ) {
					$result = $rule->validate( $context );

					if ( is_wp_error( $result ) ) {
						$validity->invalidate( $field, $result );
					}
				}
			}
		};

		call_user_func( $callback, $this->get_schema() );
	}

}
