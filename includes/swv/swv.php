<?php
/**
 * Schema-Woven Validation API
 */

require_once WPCF7_PLUGIN_DIR . '/includes/swv/schema-holder.php';


function wpcf7_swv_available_rules() {
	$rules = array(
		'required' => 'WPCF7_SWV_RequiredRule',
		'requiredfile' => 'WPCF7_SWV_RequiredFileRule',
		'email' => 'WPCF7_SWV_EmailRule',
		'url' => 'WPCF7_SWV_URLRule',
		'tel' => 'WPCF7_SWV_TelRule',
		'number' => 'WPCF7_SWV_NumberRule',
		'date' => 'WPCF7_SWV_DateRule',
		'file' => 'WPCF7_SWV_FileRule',
		'minlength' => 'WPCF7_SWV_MinLengthRule',
		'maxlength' => 'WPCF7_SWV_MaxLengthRule',
		'minnumber' => 'WPCF7_SWV_MinNumberRule',
		'maxnumber' => 'WPCF7_SWV_MaxNumberRule',
		'mindate' => 'WPCF7_SWV_MinDateRule',
		'maxdate' => 'WPCF7_SWV_MaxDateRule',
		'maxfilesize' => 'WPCF7_SWV_MaxFileSizeRule',
	);

	return apply_filters( 'wpcf7_swv_available_rules', $rules );
}


add_action( 'wpcf7_init', 'wpcf7_swv_load_rules', 10, 0 );

function wpcf7_swv_load_rules() {
	$rules = wpcf7_swv_available_rules();

	foreach ( array_keys( $rules ) as $rule ) {
		$file = sprintf( '%s.php', $rule );
		$path = path_join( WPCF7_PLUGIN_DIR . '/includes/swv/rules', $file );

		if ( file_exists( $path ) ) {
			include_once $path;
		}
	}
}


function wpcf7_swv_create_rule( $rule_name, $properties = '' ) {
	$rules = wpcf7_swv_available_rules();

	if ( isset( $rules[$rule_name] ) ) {
		return new $rules[$rule_name]( $properties );
	}
}


function wpcf7_swv_get_meta_schema() {
	return array(
		'$schema' => 'https://json-schema.org/draft/2020-12/schema',
		'title' => 'Contact Form 7 SWV',
		'description' => 'Contact Form 7 SWV meta-schema',
		'type' => 'object',
		'properties' => array(
			'version' => array(
				'type' => 'string',
			),
			'locale' => array(
				'type' => 'string',
			),
			'rules' => array(
				'type' => 'array',
				'items' => array(
					'type' => 'object',
					'properties' => array(
						'rule' => array(
							'type' => 'string',
							'enum' => array_keys( wpcf7_swv_available_rules() ),
						),
						'field' => array(
							'type' => 'string',
							'pattern' => '^[A-Za-z][-A-Za-z0-9_:]*$',
						),
						'error' => array(
							'type' => 'string',
						),
						'accept' => array(
							'type' => 'array',
							'items' => array(
								'type' => 'string',
							),
						),
						'threshold' => array(
							'type' => 'string',
						),
					),
					'required' => array( 'rule' ),
				),
			),
		),
	);
}


abstract class WPCF7_SWV_Rule {

	protected $properties = array();

	public function __construct( $properties = '' ) {
		$this->properties = wp_parse_args( $properties, array() );
	}

	public function match( $context ) {
		$field = $this->get_property( 'field' );

		if ( ! empty( $context['field'] ) ) {
			if ( $field and ! in_array( $field, (array) $context['field'], true ) ) {
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

	public function get_property( $name ) {
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

	public function rules() {
		foreach ( $this->rules as $rule ) {
			yield $rule;
		}
	}

	public function match( $context ) {
		return true;
	}

	public function validate( $context ) {
		foreach ( $this->rules() as $rule ) {
			if ( $rule->match( $context ) ) {
				$result = $rule->validate( $context );

				if ( is_wp_error( $result ) ) {
					return $result;
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

		return array_merge(
			parent::to_array(),
			array(
				'rules' => $rules_arrays,
			)
		);
	}

}


class WPCF7_SWV_Schema extends WPCF7_SWV_CompositeRule {

	const version = 'Contact Form 7 SWV Schema 2022-03';

	public function __construct( $properties = '' ) {
		$this->properties = wp_parse_args( $properties, array(
			'version' => self::version,
		) );
	}

}
