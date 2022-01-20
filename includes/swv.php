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


function wpcf7_swv_validate( $schema, $context ) {
	$rules = $schema->get_rules();

	foreach ( $rules as $r ) {
		$rule = WPCF7_SWV_Rule::create_instance( $r );

		if ( ! $rule ) {
			continue;
		}

		if ( $rule->match( $context ) ) {
			yield $rule->validate( $context );
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


abstract class WPCF7_SWV_Rule {
	private $properties = array();

	public static function create_instance( $rule ) {
		if ( ! isset( $rule['rule'] ) ) {
			return;
		}

		switch ( $rule['rule'] ) {
			case 'required':
				return new WPCF7_SWV_RequiredRule( $rule );
			case 'email':
				return new WPCF7_SWV_EmailRule( $rule );
			case 'url':
				return new WPCF7_SWV_URLRule( $rule );
			case 'tel':
				return new WPCF7_SWV_TelRule( $rule );
			case 'number':
				return new WPCF7_SWV_NumberRule( $rule );
			case 'date':
				return new WPCF7_SWV_DateRule( $rule );
			case 'file':
				return new WPCF7_SWV_FileRule( $rule );
			case 'minlength':
				return new WPCF7_SWV_MinLengthRule( $rule );
			case 'maxlength':
				return new WPCF7_SWV_MaxLengthRule( $rule );
			case 'minnumber':
				return new WPCF7_SWV_MinNumberRule( $rule );
			case 'maxnumber':
				return new WPCF7_SWV_MaxNumberRule( $rule );
			case 'mindate':
				return new WPCF7_SWV_MinDateRule( $rule );
			case 'maxdate':
				return new WPCF7_SWV_MaxDateRule( $rule );
			case 'maxfilesize':
				return new WPCF7_SWV_MaxFileSizeRule( $rule );
		}
	}

	public function __construct( $properties = '' ) {
		$this->properties = wp_parse_args( $properties, array() );
	}

	private function get_input( $context = 'text' ) {
		$field = isset( $this->properties['field'] )
			? trim( $this->properties['field'] )
			: '';

		if ( '' === $field ) {
			$input = null;
		} elseif ( 'text' === $context ) {
			$input = isset( $_POST[$field] ) ? $_POST[$field] : '';
		} elseif ( 'file' === $context ) {
			$input = isset( $_FILES[$field] ) ? $_FILES[$field] : array();
		} else {
			$input = null;
		}

		return $input;
	}

	private function error( $code, $message = null ) {
		if ( ! isset( $message ) ) {
			$message = isset( $this->properties['message'] )
				? trim( $this->properties['message'] )
				: '';
		}

		return new WP_Error( $code, $message );
	}

	public function match( $context ) {
		if ( ! empty( $context['field'] ) ) {
			if ( empty( $this->properties['field'] ) ) {
				return false;
			}

			if ( $this->properties['field'] !== $context['field'] ) {
				return false;
			}
		}

		return true;
	}

	public function validate( $context ) {
		return true;
	}
}


class WPCF7_SWV_RequiredRule extends WPCF7_SWV_Rule {
	public function validate( $context ) {
		if ( 'text' !== $context and 'file' !== $context ) {
			return;
		}

		$input = $this->get_input( $context );

		if ( 'file' === $context ) {
			$input = isset( $input['tmp_name'] ) ? $input['tmp_name'] : '';
		}

		$input = wpcf7_array_flatten( $input );
		$input = wpcf7_exclude_blank( $input );

		if ( empty( $input ) ) {
			return $this->error( 'wpcf7_invalid_required' );
		}

		return true;
	}
}


class WPCF7_SWV_EmailRule extends WPCF7_SWV_Rule {
	public function validate( $context ) {
		if ( 'text' !== $context ) {
			return;
		}

		$input = (array) $this->get_input( $context );
		$input = wpcf7_array_flatten( $input );
		$input = wpcf7_exclude_blank( $input );

		foreach ( $input as $i ) {
			if ( ! wpcf7_is_email( $i ) ) {
				return $this->error( 'wpcf7_invalid_email' );
			}
		}

		return true;
	}
}


class WPCF7_SWV_URLRule extends WPCF7_SWV_Rule {
	public function validate( $context ) {
		if ( 'text' !== $context ) {
			return;
		}

		$input = (array) $this->get_input( $context );
		$input = wpcf7_array_flatten( $input );
		$input = wpcf7_exclude_blank( $input );

		foreach ( $input as $i ) {
			if ( ! wpcf7_is_url( $i ) ) {
				return $this->error( 'wpcf7_invalid_url' );
			}
		}

		return true;
	}
}


class WPCF7_SWV_TelRule extends WPCF7_SWV_Rule {
	public function validate( $context ) {
		if ( 'text' !== $context ) {
			return;
		}

		$input = (array) $this->get_input( $context );
		$input = wpcf7_array_flatten( $input );
		$input = wpcf7_exclude_blank( $input );

		foreach ( $input as $i ) {
			if ( ! wpcf7_is_tel( $i ) ) {
				return $this->error( 'wpcf7_invalid_tel' );
			}
		}

		return true;
	}
}


class WPCF7_SWV_NumberRule extends WPCF7_SWV_Rule {
	public function validate( $context ) {
		if ( 'text' !== $context ) {
			return;
		}

		$input = (array) $this->get_input( $context );
		$input = wpcf7_array_flatten( $input );
		$input = wpcf7_exclude_blank( $input );

		foreach ( $input as $i ) {
			if ( ! wpcf7_is_number( $i ) ) {
				return $this->error( 'wpcf7_invalid_number' );
			}
		}

		return true;
	}
}


class WPCF7_SWV_DateRule extends WPCF7_SWV_Rule {
	public function validate( $context ) {
		if ( 'text' !== $context ) {
			return;
		}

		$input = (array) $this->get_input( $context );
		$input = wpcf7_array_flatten( $input );
		$input = wpcf7_exclude_blank( $input );

		foreach ( $input as $i ) {
			if ( ! wpcf7_is_date( $i ) ) {
				return $this->error( 'wpcf7_invalid_date' );
			}
		}

		return true;
	}
}


class WPCF7_SWV_FileRule extends WPCF7_SWV_Rule {
	public function validate( $context ) {
	}
}


class WPCF7_SWV_MinLengthRule extends WPCF7_SWV_Rule {
	public function validate( $context ) {
		if ( 'text' !== $context ) {
			return;
		}

		$input = (array) $this->get_input( $context );
		$input = wpcf7_array_flatten( $input );
		$input = wpcf7_exclude_blank( $input );

		if ( empty( $input ) ) {
			return true;
		}

		$total = 0;

		foreach ( $input as $i ) {
			$total += wpcf7_count_code_units( $i );
		}

		$threshold = (int) $this->properties['threshold'];

		if ( $threshold <= $total ) {
			return true;
		} else {
			return $this->error( 'wpcf7_invalid_minlength' );
		}
	}
}


class WPCF7_SWV_MaxLengthRule extends WPCF7_SWV_Rule {
	public function validate( $context ) {
		if ( 'text' !== $context ) {
			return;
		}

		$input = (array) $this->get_input( $context );
		$input = wpcf7_array_flatten( $input );
		$input = wpcf7_exclude_blank( $input );

		if ( empty( $input ) ) {
			return true;
		}

		$total = 0;

		foreach ( $input as $i ) {
			$total += wpcf7_count_code_units( $i );
		}

		$threshold = (int) $this->properties['threshold'];

		if ( $total <= $threshold ) {
			return true;
		} else {
			return $this->error( 'wpcf7_invalid_maxlength' );
		}
	}
}


class WPCF7_SWV_MinNumberRule extends WPCF7_SWV_Rule {
	public function validate( $context ) {
	}
}


class WPCF7_SWV_MaxNumberRule extends WPCF7_SWV_Rule {
	public function validate( $context ) {
	}
}


class WPCF7_SWV_MinDateRule extends WPCF7_SWV_Rule {
	public function validate( $context ) {
	}
}


class WPCF7_SWV_MaxDateRule extends WPCF7_SWV_Rule {
	public function validate( $context ) {
	}
}


class WPCF7_SWV_MaxFileSizeRule extends WPCF7_SWV_Rule {
	public function validate( $context ) {
	}
}
