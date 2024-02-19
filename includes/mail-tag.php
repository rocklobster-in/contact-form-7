<?php

/**
 * Class that represents a mail-tag.
 */
class WPCF7_MailTag {

	private $tag;
	private $tagname = '';
	private $name = '';
	private $options = array();
	private $values = array();
	private $form_tag = null;


	/**
	 * The constructor method.
	 */
	public function __construct( $tag, $tagname, $values ) {
		$this->tag = $tag;
		$this->name = $this->tagname = $tagname;

		$this->options = array(
			'do_not_heat' => false,
			'format' => '',
		);

		if ( ! empty( $values ) ) {
			preg_match_all( '/"[^"]*"|\'[^\']*\'/', $values, $matches );
			$this->values = wpcf7_strip_quote_deep( $matches[0] );
		}

		if ( preg_match( '/^_raw_(.+)$/', $tagname, $matches ) ) {
			$this->name = trim( $matches[1] );
			$this->options['do_not_heat'] = true;
		}

		if ( preg_match( '/^_format_(.+)$/', $tagname, $matches ) ) {
			$this->name = trim( $matches[1] );
			$this->options['format'] = $this->values[0];
		}
	}


	/**
	 * Returns the name part of this mail-tag.
	 */
	public function tag_name() {
		return $this->tagname;
	}


	/**
	 * Returns the form field name corresponding to this mail-tag.
	 */
	public function field_name() {
		return strtr( $this->name, '.', '_' );
	}


	/**
	 * Returns the value of the specified option.
	 */
	public function get_option( $option ) {
		return $this->options[$option];
	}


	/**
	 * Returns the values part of this mail-tag.
	 */
	public function values() {
		return $this->values;
	}


	/**
	 * Retrieves the WPCF7_FormTag object that corresponds to this mail-tag.
	 */
	public function corresponding_form_tag() {
		if ( $this->form_tag instanceof WPCF7_FormTag ) {
			return $this->form_tag;
		}

		if ( $submission = WPCF7_Submission::get_instance() ) {
			$contact_form = $submission->get_contact_form();
			$tags = $contact_form->scan_form_tags( array(
				'name' => $this->field_name(),
				'feature' => '! zero-controls-container',
			) );

			if ( $tags ) {
				$this->form_tag = $tags[0];
			}
		}

		return $this->form_tag;
	}

}


use Contactable\SWV;

class WPCF7_MailTag_OutputCalculator {

	const email = 0b100;
	const text = 0b010;
	const blank = 0b001;

	private $contact_form;

	public function __construct( WPCF7_ContactForm $contact_form ) {
		$this->contact_form = $contact_form;
	}

	public function calc_output( WPCF7_MailTag $mail_tag ) {
		return $this->calc_swv_result(
			$this->contact_form->get_schema(),
			$mail_tag->field_name()
		);
	}

	private function calc_swv_result( SWV\Rule $rule, $target_field ) {
		$result = self::email | self::text | self::blank;

		if ( $rule instanceof WPCF7_SWV_Schema or $rule instanceof SWV\AllRule ) {
			foreach ( $rule->rules() as $child_rule ) {
				$result &= $this->calc_swv_result( $child_rule, $target_field );
			}

			return $result;
		} elseif ( $rule instanceof SWV\AnyRule ) {
			foreach ( $rule->rules() as $child_rule ) {
				$result |= $this->calc_swv_result( $child_rule, $target_field );
			}

			return $result;
		} elseif ( $rule instanceof SWV\CompositeRule ) {
			return $result;
		}

		$field_prop = $rule->get_property( 'field' );

		if ( empty( $field_prop ) or $field_prop !== $target_field ) {
			return self::email | self::text | self::blank;
		}

		if ( $rule instanceof SWV\RequiredRule ) {
			return ~ self::blank;
		}

		if ( $rule instanceof SWV\EmailRule ) {
			return self::email | self::blank;
		}

		if ( $rule instanceof SWV\EnumRule ) {
			$acceptable_values = (array) $rule->get_property( 'accept' );
			$acceptable_values = array_map( 'strval', $acceptable_values );
			$acceptable_values = array_filter( $acceptable_values );
			$acceptable_values = array_unique( $acceptable_values );

			$email_values = array_filter( $acceptable_values, 'wpcf7_is_email' );

			if ( count( $email_values ) === count( $acceptable_values ) ) {
				return self::email | self::blank;
			} else {
				return self::text | self::blank;
			}
		}

		return self::email | self::text | self::blank;
	}

}
