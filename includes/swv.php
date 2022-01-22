<?php
/**
 * Schema-Woven Validation API
 */


function wpcf7_swv_generate_schema( WPCF7_ContactForm $contact_form ) {
	$schema = new WPCF7_SWV_Schema();

	$tags = $contact_form->scan_form_tags();

	do_action(
		'wpcf7_swv_pre_add_rules',
		$schema,
		$tags
	);

	foreach ( $tags as $tag ) {
		$type = $tag->type;

		do_action(
			"wpcf7_swv_add_rules_for_{$type}",
			$schema,
			$tag
		);
	}

	do_action(
		'wpcf7_swv_add_rules',
		$schema,
		$tags
	);

	return $schema;
}


add_action(
	'wpcf7_swv_pre_add_rules',
	'wpcf7_swv_add_common_rules',
	10, 2
);

function wpcf7_swv_add_common_rules( $schema, $tags ) {
	foreach ( $tags as $tag ) {

		if ( $tag->is_required() ) {
			$schema->add_rule( $tag->name, 'required', array(
				'message' => wpcf7_get_message( 'invalid_required' ),
			) );
		}
	}
}


function wpcf7_swv_validate( $rules, $context = '' ) {
	$context = wp_parse_args( $context, array(
		'text' => false,
		'file' => false,
		'field' => array(),
		'validity' => array(),
	) );

	foreach ( $rules as $r ) {
		$rule = WPCF7_SWV_Rule::create_instance( $r );

		if ( ! $rule ) {
			continue;
		}

		if ( $rule->match( $context ) ) {
			$result = $rule->validate( $context );

			if ( isset( $r['field'] ) ) {
				$context['validity'][$r['field']] = $result;
			}

			yield $result;
		}
	}
}


class WPCF7_SWV_Schema {

	private $rules = array();

	public function __construct() {
	}

	public function add_rule( $field, $rule, $args = '' ) {
		$args = wp_parse_args( $args, array(
			'message' => __( "Invalid value.", 'contact-form-7' ),
		) );

		$this->rules[] = array(
			'field' => $field,
			'rule' => sanitize_key( $rule ),
		) + $args;
	}

	public function get_rules( $cond = '' ) {
		$cond = wp_parse_args( $cond, array(
			'field' => '',
		) );

		$rules = array_filter( $this->rules,
			function ( $rule ) use ( $cond ) {
				if ( $cond['field'] ) {
					if ( ! isset( $rule['field'] )
					or $rule['field'] !== $cond['field'] ) {
						return false;
					}
				}

				return true;
			}
		);

		return $rules;
	}

	public function to_array() {
		return array(
			'rules' => $this->rules,
		);
	}
}
