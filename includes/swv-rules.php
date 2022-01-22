<?php

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

	protected function get_property( $name ) {
		if ( isset( $this->properties[$name] ) ) {
			return $this->properties[$name];
		}
	}

	protected function get_input( $context ) {
		$field = $this->get_property( 'field' );

		if ( ! empty( $context['text'] ) ) {
			return isset( $_POST[$field] ) ? $_POST[$field] : '';
		}

		if ( ! empty( $context['file'] ) ) {
			return isset( $_FILES[$field] ) ? $_FILES[$field] : array();
		}
	}

	protected function error( $code, $message = null ) {
		if ( ! isset( $message ) ) {
			$message = $this->get_property( 'message' );
		}

		return new WP_Error( $code, $message );
	}

	public function match( $context ) {
		$field = $this->get_property( 'field' );

		if ( isset( $context['validity'][$field] ) ) {
			$validity = $context['validity'][$field];

			if ( is_wp_error( $validity ) or ! $validity ) {
				return false;
			}
		}

		if ( ! empty( $context['field'] )
		and ! in_array( $field, (array) $context['field'], true ) ) {
			return false;
		}

		return true;
	}

	public function validate( $context ) {
		return true;
	}
}


class WPCF7_SWV_RequiredRule extends WPCF7_SWV_Rule {

	public function match( $context ) {
		if ( false === parent::match( $context ) ) {
			return false;
		}

		if ( empty( $context['text'] ) and empty( $context['file'] ) ) {
			return false;
		}

		return true;
	}

	public function validate( $context ) {
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

	public function match( $context ) {
		if ( false === parent::match( $context ) ) {
			return false;
		}

		if ( empty( $context['text'] ) ) {
			return false;
		}

		return true;
	}

	public function validate( $context ) {
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

	public function match( $context ) {
		if ( false === parent::match( $context ) ) {
			return false;
		}

		if ( empty( $context['text'] ) ) {
			return false;
		}

		return true;
	}

	public function validate( $context ) {
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

	public function match( $context ) {
		if ( false === parent::match( $context ) ) {
			return false;
		}

		if ( empty( $context['text'] ) ) {
			return false;
		}

		return true;
	}

	public function validate( $context ) {
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

	public function match( $context ) {
		if ( false === parent::match( $context ) ) {
			return false;
		}

		if ( empty( $context['text'] ) ) {
			return false;
		}

		return true;
	}

	public function validate( $context ) {
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

	public function match( $context ) {
		if ( false === parent::match( $context ) ) {
			return false;
		}

		if ( empty( $context['text'] ) ) {
			return false;
		}

		return true;
	}

	public function validate( $context ) {
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

	public function match( $context ) {
		if ( false === parent::match( $context ) ) {
			return false;
		}

		if ( empty( $context['text'] ) ) {
			return false;
		}

		return true;
	}

	public function validate( $context ) {
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

	public function match( $context ) {
		if ( false === parent::match( $context ) ) {
			return false;
		}

		if ( empty( $context['text'] ) ) {
			return false;
		}

		return true;
	}

	public function validate( $context ) {
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
