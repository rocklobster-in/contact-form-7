<?php
/**
 * Schema-Woven Validation API
 */

use RockLobsterInc\Swv\{ AbstractRule, CompositeRule };
use RockLobsterInc\Swv\{ InvalidityException as Invalidity };
use RockLobsterInc\FormDataTree\{ FormDataTreeInterface as FormDataTree };

require_once 'schema-holder.php';
require_once 'script-loader.php';


/**
 * Returns an associative array of SWV rules.
 */
function wpcf7_swv_available_rules() {
	$rules = array(
		'all' => '\RockLobsterInc\Swv\Rules\AllRule',
		'any' => '\RockLobsterInc\Swv\Rules\AnyRule',
		'date' => '\RockLobsterInc\Swv\Rules\DateRule',
		'dayofweek' => '\RockLobsterInc\Swv\Rules\DayofweekRule',
		'email' => '\RockLobsterInc\Swv\Rules\EmailRule',
		'enum' => '\RockLobsterInc\Swv\Rules\EnumRule',
		'file' => '\RockLobsterInc\Swv\Rules\FileRule',
		'maxdate' => '\RockLobsterInc\Swv\Rules\MaxDateRule',
		'maxfilesize' => '\RockLobsterInc\Swv\Rules\MaxFileSizeRule',
		'maxitems' => '\RockLobsterInc\Swv\Rules\MaxItemsRule',
		'maxlength' => '\RockLobsterInc\Swv\Rules\MaxLengthRule',
		'maxnumber' => '\RockLobsterInc\Swv\Rules\MaxNumberRule',
		'mindate' => '\RockLobsterInc\Swv\Rules\MinDateRule',
		'minfilesize' => '\RockLobsterInc\Swv\Rules\MinFileSizeRule',
		'minitems' => '\RockLobsterInc\Swv\Rules\MinItemsRule',
		'minlength' => '\RockLobsterInc\Swv\Rules\MinLengthRule',
		'minnumber' => '\RockLobsterInc\Swv\Rules\MinNumberRule',
		'number' => '\RockLobsterInc\Swv\Rules\NumberRule',
		'required' => '\RockLobsterInc\Swv\Rules\RequiredRule',
		'requiredfile' => '\RockLobsterInc\Swv\Rules\RequiredFileRule',
		'stepnumber' => '\RockLobsterInc\Swv\Rules\StepNumberRule',
		'tel' => '\RockLobsterInc\Swv\Rules\TelRule',
		'time' => '\RockLobsterInc\Swv\Rules\TimeRule',
		'url' => '\RockLobsterInc\Swv\Rules\URLRule',
	);

	return apply_filters( 'wpcf7_swv_available_rules', $rules );
}


/**
 * Creates an SWV rule object.
 *
 * @param string $rule_name Rule name.
 * @param string|array $properties Optional. Rule properties.
 * @return AbstractRule|null The rule object, or null if it failed.
 */
function wpcf7_swv_create_rule( $rule_name, $properties = '' ) {
	$properties = wp_parse_args( $properties );

	$rules = wpcf7_swv_available_rules();

	if ( isset( $rules[$rule_name] ) and class_exists( $rules[$rule_name] ) ) {
		return new $rules[$rule_name]( $properties );
	}
}


/**
 * Returns an associative array of JSON Schema for Contact Form 7 SWV.
 */
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
						'base' => array(
							'type' => 'string',
						),
						'interval' => array(
							'type' => 'number',
							'minimum' => 0,
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


/**
 * The schema class as a composite rule.
 */
class WPCF7_SWV_Schema extends CompositeRule {

	/**
	 * The human-readable version of the schema.
	 */
	const version = 'Contact Form 7 SWV Schema 2024-10';


	/**
	 * Rule properties.
	 */
	public readonly string $locale;


	/**
	 * Constructor.
	 *
	 * @param array $properties Rule properties.
	 */
	public function __construct( array $properties = [] ) {
		$this->locale = $properties[ 'locale' ] ?? '';
	}


	/**
 	 * Validates the form data according to the logic defined by this rule.
 	 *
 	 * @param FormDataTree $form_data Form data.
 	 * @param array $context Optional context.
 	 */
	public function validate( FormDataTree $form_data, array $context = [] ) {
		foreach ( $this->rules() as $rule ) {
			if ( $rule->matches( $context ) ) {
				try {
					$rule->validate( $form_data, $context );
				} catch ( Invalidity $error ) {
					yield new WP_Error( 'swv', $error->getMessage(), $error );
				}
			}
		}

		return true;
	}


	/**
 	 * Wrapper function for addRule.
	 *
	 * @param AbstractRule $rule Sub-rule to be added.
 	 */
	public function add_rule( AbstractRule $rule ) {
		return $this->addRule( $rule );
	}


	/**
	 * Wrapper function for toArray.
	 *
	 * @return array Array of rule properties.
	 */
	public function to_array() {
		$rules_arrays = array();

		foreach ( $this->rules() as $rule ) {
			$rules_arrays[] = $rule->toArray();
		}

		return array(
			'version' => self::version,
			'locale' => $this->locale,
			'rules' => $rules_arrays,
		);
	}

}
