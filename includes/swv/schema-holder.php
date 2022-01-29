<?php

trait WPCF7_SWV_SchemaHolder {

	protected $schema;


	/**
	 * Retrieves SWV schema for this contact form.
	 *
	 * @return WPCF7_SWV_Schema The schema object.
	 */
	public function get_schema() {
		if ( isset( $this->schema ) ) {
			return $this->schema;
		}

		$schema = new WPCF7_SWV_Schema();
		$tags = $this->scan_form_tags();

		do_action( 'wpcf7_swv_pre_add_rules', $schema, $tags );

		foreach ( $tags as $tag ) {
			do_action( "wpcf7_swv_add_rules_for_{$tag->type}", $schema, $tag );
		}

		do_action( 'wpcf7_swv_add_rules', $schema, $tags );

		return $this->schema = $schema;
	}


	public function validate_schema( $context ) {
		$this->schema->validate( $context );
	}

}
