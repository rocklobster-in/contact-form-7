<?php
/**
 * Schema-Woven Validation API
 */


function wpcf7_swv_generate_schema( WPCF7_ContactForm $contact_form ) {
	$generator = new WPCF7_SWV_SchemaGenerator();

	$tags = $contact_form->scan_form_tags();

	do_action(
		'wpcf7_swv_pre_add_rules',
		$generator,
		$tags
	);

	foreach ( $tags as $tag ) {
		$type = $tag->type;

		do_action(
			"wpcf7_swv_add_rules_for_{$type}",
			$generator,
			$tag
		);
	}

	do_action(
		'wpcf7_swv_add_rules',
		$generator,
		$tags
	);

	return $generator->generate_schema();
}


add_action(
	'wpcf7_swv_pre_add_rules',
	'wpcf7_swv_add_common_rules',
	10, 2
);

function wpcf7_swv_add_common_rules( $generator, $tags ) {
	foreach ( $tags as $tag ) {

		if ( $tag->is_required() ) {
			$generator->add_rule( $tag->name, 'required', array(
				'message' => wpcf7_get_message( 'invalid_required' ),
			) );
		}
	}
}


class WPCF7_SWV_SchemaGenerator {

	private $rules = array();

	public function __construct() {
	}

	public function add_rule( $field, $rule, $args = '' ) {
		$args = wp_parse_args( $args, array(
			'message' => __( "Invalid value.", 'contact-form-7' ),
		) );

		if ( ! isset( $this->rules[$field] ) ) {
			$this->rules[$field] = array();
		}

		$this->rules[$field][] = array(
			'rule' => sanitize_key( $rule ),
		) + $args;
	}

	public function generate_schema() {
		return array(
			'rules' => $this->rules,
		);
	}

}
