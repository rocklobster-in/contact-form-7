<?php
/**
 * Schema-Woven Validation API
 */

require_once WPCF7_PLUGIN_DIR . '/includes/swv/schema-holder.php';


function wpcf7_swv_load_rules() {
	$rules = array(
		'required',
		'email',
		'url',
		'tel',
		'number',
		'date',
		'file',
		'minlength',
		'maxlength',
		'minnumber',
		'maxnumber',
		'mindate',
		'maxdate',
		'maxfilesize',
	);

	foreach ( $rules as $rule ) {
		$file = sprintf( '%s.php', $rule );
		$path = path_join( WPCF7_PLUGIN_DIR . '/includes/swv/rules', $file );

		if ( file_exists( $path ) ) {
			include_once $path;
		}
	}
}


function wpcf7_swv_create_rule( $rule_name, $properties = '' ) {
	wpcf7_swv_load_rules();

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


abstract class WPCF7_SWV_Rule {

	protected $properties = array();

	public function __construct( $properties = '' ) {
		$this->properties = wp_parse_args( $properties, array() );
	}

	public function match( $context ) {
		if ( isset( $context['validity'] )
		and $context['validity'] instanceof WPCF7_Validation ) {
			$field = $this->get_property( 'field' );

			if ( $field and ! $context['validity']->is_valid( $field ) ) {
				return false;
			}
		}

		return true;
	}

	public function validate( $context ) {
		return true;
	}

	public function to_array() {
		return (array) $this->properties;
	}

	protected function get_property( $name ) {
		if ( isset( $this->properties[$name] ) ) {
			return $this->properties[$name];
		}
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
		if ( false === parent::match( $context ) ) {
			return false;
		}

		return true;
	}

	public function validate( $context ) {
		if ( isset( $context['validity'] )
		and $context['validity'] instanceof WPCF7_Validation ) {
			$validity = $context['validity'];
		}

		foreach ( $this->rules as $rule ) {
			if ( $rule->match( $context ) ) {
				$results = $rule->validate( $context );

				if ( $results instanceof Iterator ) {
					foreach ( $results as $field => $result ) {
						if ( isset( $validity ) and is_wp_error( $result ) ) {
							$validity->invalidate( $field, $result );
						}

						yield $field => $result;
					}
				}
			}
		}

		return true;
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
