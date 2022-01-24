<?php

class WPCF7_SWV_RequiredRule extends WPCF7_SWV_Rule {

	const rule_name = 'required';

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

	public function to_array() {
		return array( 'rule' => self::rule_name ) + (array) $this->properties;
	}
}


class WPCF7_SWV_EmailRule extends WPCF7_SWV_Rule {

	const rule_name = 'email';

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

	public function to_array() {
		return array( 'rule' => self::rule_name ) + (array) $this->properties;
	}
}


class WPCF7_SWV_URLRule extends WPCF7_SWV_Rule {

	const rule_name = 'url';

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

	public function to_array() {
		return array( 'rule' => self::rule_name ) + (array) $this->properties;
	}
}


class WPCF7_SWV_TelRule extends WPCF7_SWV_Rule {

	const rule_name = 'tel';

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

	public function to_array() {
		return array( 'rule' => self::rule_name ) + (array) $this->properties;
	}
}


class WPCF7_SWV_NumberRule extends WPCF7_SWV_Rule {

	const rule_name = 'number';

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

	public function to_array() {
		return array( 'rule' => self::rule_name ) + (array) $this->properties;
	}
}


class WPCF7_SWV_DateRule extends WPCF7_SWV_Rule {

	const rule_name = 'date';

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

	public function to_array() {
		return array( 'rule' => self::rule_name ) + (array) $this->properties;
	}
}


class WPCF7_SWV_FileRule extends WPCF7_SWV_Rule {

	const rule_name = 'file';

	public function validate( $context ) {
	}

	public function to_array() {
		return array( 'rule' => self::rule_name ) + (array) $this->properties;
	}
}


class WPCF7_SWV_MinLengthRule extends WPCF7_SWV_Rule {

	const rule_name = 'minlength';

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

	public function to_array() {
		return array( 'rule' => self::rule_name ) + (array) $this->properties;
	}
}


class WPCF7_SWV_MaxLengthRule extends WPCF7_SWV_Rule {

	const rule_name = 'maxlength';

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

	public function to_array() {
		return array( 'rule' => self::rule_name ) + (array) $this->properties;
	}
}


class WPCF7_SWV_MinNumberRule extends WPCF7_SWV_Rule {

	const rule_name = 'minnumber';

	public function validate( $context ) {
	}

	public function to_array() {
		return array( 'rule' => self::rule_name ) + (array) $this->properties;
	}
}


class WPCF7_SWV_MaxNumberRule extends WPCF7_SWV_Rule {

	const rule_name = 'maxnumber';

	public function validate( $context ) {
	}

	public function to_array() {
		return array( 'rule' => self::rule_name ) + (array) $this->properties;
	}
}


class WPCF7_SWV_MinDateRule extends WPCF7_SWV_Rule {

	const rule_name = 'mindate';

	public function validate( $context ) {
	}

	public function to_array() {
		return array( 'rule' => self::rule_name ) + (array) $this->properties;
	}
}


class WPCF7_SWV_MaxDateRule extends WPCF7_SWV_Rule {

	const rule_name = 'maxdate';

	public function validate( $context ) {
	}

	public function to_array() {
		return array( 'rule' => self::rule_name ) + (array) $this->properties;
	}
}


class WPCF7_SWV_MaxFileSizeRule extends WPCF7_SWV_Rule {

	const rule_name = 'maxfilesize';

	public function validate( $context ) {
	}

	public function to_array() {
		return array( 'rule' => self::rule_name ) + (array) $this->properties;
	}
}
