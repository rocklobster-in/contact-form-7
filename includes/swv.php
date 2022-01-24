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
			$schema->add_rule(
				wpcf7_swv_create_rule( 'required', array(
					'field' => $tag->name,
					'message' => wpcf7_get_message( 'invalid_required' ),
				) )
			);
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


function wpcf7_swv_create_rule( $rule_name, $properties = '' ) {
	switch ( $rule_name ) {
		case 'required':
			return new WPCF7_SWV_RequiredRule( $properties );
		case 'email':
			return new WPCF7_SWV_EmailRule( $properties );
		case 'url':
			return new WPCF7_SWV_URLRule( $properties );
		case 'tel':
			return new WPCF7_SWV_TelRule( $properties );
		case 'number':
			return new WPCF7_SWV_NumberRule( $properties );
		case 'date':
			return new WPCF7_SWV_DateRule( $properties );
		case 'file':
			return new WPCF7_SWV_FileRule( $properties );
		case 'minlength':
			return new WPCF7_SWV_MinLengthRule( $properties );
		case 'maxlength':
			return new WPCF7_SWV_MaxLengthRule( $properties );
		case 'minnumber':
			return new WPCF7_SWV_MinNumberRule( $properties );
		case 'maxnumber':
			return new WPCF7_SWV_MaxNumberRule( $properties );
		case 'mindate':
			return new WPCF7_SWV_MinDateRule( $properties );
		case 'maxdate':
			return new WPCF7_SWV_MaxDateRule( $properties );
		case 'maxfilesize':
			return new WPCF7_SWV_MaxFileSizeRule( $properties );
	}
}


abstract class WPCF7_SWV_Rule {

	protected $properties = array();

	public function __construct( $properties = '' ) {
		$this->properties = wp_parse_args( $properties, array() );
	}

	public function match( $context ) {
		return true;
	}

	public function validate( $context ) {
		return true;
	}

	public function to_array() {
		return (array) $this->properties;
	}

}


abstract class WPCF7_SWV_CompositeRule extends WPCF7_SWV_Rule {

	protected $rules = array();

	public function add_rule( $rule ) {
		if ( $rule instanceof WPCF7_SWV_Rule ) {
			$this->rules[] = $rule;
		}
	}

	public function match( $context ) {
		return true;
	}

	public function validate( $context ) {
		foreach ( $this->rules as $rule ) {
			if ( $rule->match( $context ) ) {
				yield $rule->validate( $context );
			}
		}
	}

	public function to_array() {
		$rules_arrays = array_map(
			function ( $rule ) {
				return $rule->to_array();
			},
			$this->rules
		);

		return array(
			'rules' => $rules_arrays,
		);
	}

}


class WPCF7_SWV_Schema extends WPCF7_SWV_CompositeRule {

}
