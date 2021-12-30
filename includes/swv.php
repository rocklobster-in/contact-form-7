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
				'context' => 'all',
			) );
		}
	}
}


add_filter(
	'wpcf7_validate',
	'wpcf7_swv_validate',
	10, 2
);

function wpcf7_swv_validate( $result, $tags ) {
	$submission = WPCF7_Submission::get_instance();

	if ( ! $submission ) {
		return $result;
	}

	$contact_form = $submission->get_contact_form();
	$schema = $contact_form->get_schema();

	foreach ( $schema->validate( $_POST ) as $error ) {
		$result->invalidate( $error['field'], $error['message'] );
	}

	return $result;
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
			'context' => 'default',
		) );

		$rules = array_filter( $this->rules,
			function ( $rule ) use ( $cond ) {
				if ( $cond['field'] ) {
					if ( ! isset( $rule['field'] )
					or $rule['field'] !== $cond['field'] ) {
						return false;
					}
				}

				if ( $cond['context'] ) {
					if ( 'all' === $cond['context'] or 'all' === $rule['context'] ) {
						// continue
					} else {
						if ( 'default' === $cond['context'] ) {
							$cond['context'] = 'text';
						}

						if ( ! isset( $rule['context'] )
						or 'default' === $rule['context'] ) {
							$rule['context'] = 'text';
						}

						if ( $cond['context'] !== $rule['context'] ) {
							return false;
						}
					}
				}

				return true;
			}
		);

		return $rules;
	}

	public function validate( $input ) {
		$invalid_fields = array();

		foreach ( $this->rules as $rule ) {
			if ( isset( $rule['field'] )
			and in_array( $rule['field'], $invalid_fields, true ) ) {
				continue;
			}

			if ( ! isset( $rule['rule'] ) ) {
				continue;
			}

			// Todo: Implement error creation
			$error = $rule;

			if ( ! empty( $error['field'] ) ) {
				$invalid_fields[] = $error['field'];
			}

			yield $error;
		}
	}

	public function to_array() {
		return array(
			'rules' => $this->rules,
		);
	}
}


class WPCF7_SWV_Validation {

	public static function required( $input ) {
		$input = wpcf7_array_flatten( $input );

		return (bool) array_filter( $input,
			function ( $i ) {
				return isset( $i ) && '' !== $i;
			}
		);
	}

	public static function email( $input ) {
		$input = wpcf7_array_flatten( $input );

		foreach ( $input as $i ) {
			if ( '' !== $i and ! wpcf7_is_email( $i ) ) {
				return false;
			}
		}

		return true;
	}

	public static function url( $input ) {
		$input = wpcf7_array_flatten( $input );

		foreach ( $input as $i ) {
			if ( '' !== $i and ! wpcf7_is_url( $i ) ) {
				return false;
			}
		}

		return true;
	}

	public static function tel( $input ) {
		$input = wpcf7_array_flatten( $input );

		foreach ( $input as $i ) {
			if ( '' !== $i and ! wpcf7_is_tel( $i ) ) {
				return false;
			}
		}

		return true;
	}

	public static function number( $input ) {
		$input = wpcf7_array_flatten( $input );

		foreach ( $input as $i ) {
			if ( '' !== $i and ! wpcf7_is_number( $i ) ) {
				return false;
			}
		}

		return true;
	}

	public static function date( $input ) {
		$input = wpcf7_array_flatten( $input );

		foreach ( $input as $i ) {
			if ( '' !== $i and ! wpcf7_is_date( $i ) ) {
				return false;
			}
		}

		return true;
	}

	public static function file( $input ) {

	}

	public static function minlength( $input, $threshold ) {
		$input = wpcf7_array_flatten( $input );

		$input = array_filter( $input,
			function ( $i ) {
				return isset( $i ) && '' !== $i;
			}
		);

		if ( empty( $input ) ) {
			return true;
		}

		$total = 0;

		foreach ( $input as $i ) {
			$total += wpcf7_count_code_units( $i );
		}

		return $threshold <= $total;
	}

	public static function maxlength( $input, $threshold ) {
		$input = wpcf7_array_flatten( $input );

		$input = array_filter( $input,
			function ( $i ) {
				return isset( $i ) && '' !== $i;
			}
		);

		if ( empty( $input ) ) {
			return true;
		}

		$total = 0;

		foreach ( $input as $i ) {
			$total += wpcf7_count_code_units( $i );
		}

		return $total <= $threshold;
	}

	public static function minnumber( $input, $threshold ) {

	}

	public static function maxnumber( $input, $threshold ) {

	}

	public static function mindate( $input, $threshold ) {

	}

	public static function maxdate( $input, $threshold ) {

	}

	public static function maxfilesize( $input, $threshold ) {

	}

}
