<?php

class WPCF7_HTMLParser {

	const text = 0;
	const opening_tag = 1;
	const closing_tag = 2;
	const comment = 3;

	protected $input = '';
	protected $stack = '';
	protected $mode = 0;
	protected $position = 0;

	public function __construct( string $input ) {
		$this->input = $input;
	}

	public function parse() {
		$input_bytelength = strlen( $this->input );

		while ( $this->position < $input_bytelength ) {
			$this->stack .= $this->input[$this->position];

			if ( $this->mode === self::text ) {
				if ( str_starts_with( $this->stack, '<!--' ) ) {
					$this->mode = self::comment;
				} elseif ( str_starts_with( $this->stack, '</' ) ) {
					$this->mode = self::closing_tag;
				} elseif ( str_starts_with( $this->stack, '<' ) ) {
					$this->mode = self::opening_tag;
				}
			} elseif ( $this->mode === self::opening_tag ) {
				if ( str_ends_with( $this->stack, '>' ) ) {
					$this->mode = self::text;
					$this->stack = '';
				}
			} elseif ( $this->mode === self::closing_tag ) {
				if ( str_ends_with( $this->stack, '>' ) ) {
					$this->mode = self::text;
					$this->stack = '';
				}
			} elseif ( $this->mode === self::comment ) {
				if ( str_ends_with( $this->stack, '-->' ) ) {
					$this->mode = self::text;
					$this->stack = '';
				}
			}

			$this->position++;
		}
	}

}
