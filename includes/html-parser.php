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
	protected $callback = null;

	public function __construct( string $input, callable $callback ) {
		$this->input = $input;
		$this->callback = $callback;
	}

	public function parse() {
		$input_bytelength = strlen( $this->input );

		while ( $this->position < $input_bytelength ) {
			$current_byte = $this->input[$this->position];

			$this->stack .= $current_byte;

			if ( 1 === $input_bytelength - $this->position ) { // End of string.
				$this->trigger();
				break;
			}

			if ( '<' === $current_byte ) {
				$this->trigger();
				$this->stack = '<';
				$this->mode = self::opening_tag;
				$this->position++;
				continue;
			}

			if ( $this->mode === self::opening_tag ) {
				if ( str_starts_with( $this->stack, '<!--' ) ) {
					$this->mode = self::comment;
				} elseif ( str_starts_with( $this->stack, '</' ) ) {
					$this->mode = self::closing_tag;
				}
			}

			if ( $this->mode === self::opening_tag ) {
				if ( str_ends_with( $this->stack, '>' ) ) {
					$this->trigger();
					$this->stack = '';
					$this->mode = self::text;
					$this->position++;
					continue;
				}
			}

			if ( $this->mode === self::closing_tag ) {
				if ( str_ends_with( $this->stack, '>' ) ) {
					$this->trigger();
					$this->stack = '';
					$this->mode = self::text;
					$this->position++;
					continue;
				}
			}

			if ( $this->mode === self::comment ) {
				if ( str_ends_with( $this->stack, '-->' ) ) {
					$this->trigger();
					$this->stack = '';
					$this->mode = self::text;
					$this->position++;
					continue;
				}
			}

			$this->position++;
		}
	}

	protected function trigger() {
		if ( '' === $this->stack ) {
			return;
		}

		call_user_func( $this->callback, array(
			'position' => $this->position - strlen( $this->stack ),
			'type' => $this->mode,
			'content' => $this->stack,
		) );
	}

}
