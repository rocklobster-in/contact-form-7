<?php

namespace Contactable\SWV;

class URLRule extends Rule {

	const rule_name = 'url';

	public function matches( $context ) {
		if ( false === parent::matches( $context ) ) {
			return false;
		}

		if ( empty( $context['text'] ) ) {
			return false;
		}

		return true;
	}

	public function validate( $context ) {
		$input = $this->get_default_input();
		$input = wpcf7_array_flatten( $input );
		$input = wpcf7_exclude_blank( $input );

		foreach ( $input as $i ) {
			if ( ! wpcf7_is_url( $i ) ) {
				return $this->create_error();
			}
		}

		return true;
	}

}
