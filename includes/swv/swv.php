<?php
/**
 * Schema-Woven Validation API
 */

use RockLobsterInc\Swv\{ AbstractRule, CompositeRule, Rules };
use RockLobsterInc\Swv\{ InvalidityException as Invalidity };
use RockLobsterInc\FormDataTree\{ FormDataTreeInterface as FormDataTree };

require_once 'schema-holder.php';
require_once 'script-loader.php';


/**
 * Returns an associative array of SWV rules.
 */
function wpcf7_swv_available_rules() {
	$rules = array(
		'all' => 'Rules\AllRule',
		'any' => 'Rules\AnyRule',
		'date' => 'Rules\DateRule',
		'dayofweek' => 'Rules\DayofweekRule',
		'email' => 'Rules\EmailRule',
		'enum' => 'Rules\EnumRule',
		'file' => 'Rules\FileRule',
		'maxdate' => 'Rules\MaxDateRule',
		'maxfilesize' => 'Rules\MaxFileSizeRule',
		'maxitems' => 'Rules\MaxItemsRule',
		'maxlength' => 'Rules\MaxLengthRule',
		'maxnumber' => 'Rules\MaxNumberRule',
		'mindate' => 'Rules\MinDateRule',
		'minfilesize' => 'Rules\MinFileSizeRule',
		'minitems' => 'Rules\MinItemsRule',
		'minlength' => 'Rules\MinLengthRule',
		'minnumber' => 'Rules\MinNumberRule',
		'number' => 'Rules\NumberRule',
		'required' => 'Rules\RequiredRule',
		'requiredfile' => 'Rules\RequiredFileRule',
		'stepnumber' => 'Rules\StepNumberRule',
		'tel' => 'Rules\TelRule',
		'time' => 'Rules\TimeRule',
		'url' => 'Rules\URLRule',
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
	$rules = wpcf7_swv_available_rules();

	if ( isset( $rules[$rule_name] ) ) {
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
	 * @param iterable $properties Rule properties.
	 */
	public function __construct( iterable $properties = [] ) {
		$this->locale = $properties[ 'locale' ] ?? '';
	}


	/**
 	 * Validates the form data according to the logic defined by this rule.
 	 *
 	 * @param FormDataTree $form_data Form data.
 	 * @param iterable $context Optional context.
 	 */
	public function validate( FormDataTree $form_data, iterable $context = [] ) {
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

}
