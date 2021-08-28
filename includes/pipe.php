<?php

class WPCF7_Pipe {

	public $before = '';
	public $after = '';

	public function __construct( $text ) {
		$text = (string) $text;

		$pipe_pos = strpos( $text, '|' );

		if ( false === $pipe_pos ) {
			$this->before = $this->after = trim( $text );
		} else {
			$this->before = trim( substr( $text, 0, $pipe_pos ) );
			$this->after = trim( substr( $text, $pipe_pos + 1 ) );
		}
	}
}

class WPCF7_Pipes {

	private $pipes = array();

	public function __construct( array $texts ) {
		foreach ( $texts as $text ) {
			$this->add_pipe( $text );
		}
	}

	private function add_pipe( $text ) {
		$pipe = new WPCF7_Pipe( $text );
		$this->pipes[] = $pipe;
	}

	public function do_pipe( $input ) {
		$input_canonical = wpcf7_canonicalize( $input, array(
			'strto' => 'as-is',
		) );

		foreach ( $this->pipes as $pipe ) {

			list( $before, $after ) = array_map(
				function ( $item ) {
					return wpcf7_canonicalize( $item, array(
						'strto' => 'as-is',
					) );
				},
				array( $pipe->before, $pipe->after )
			);

			if ( $input_canonical === $before ) {
				return $after;
			}
		}

		return $input;
	}

	public function collect_befores() {
		$befores = array();

		foreach ( $this->pipes as $pipe ) {
			$befores[] = $pipe->before;
		}

		return $befores;
	}

	public function collect_afters() {
		$afters = array();

		foreach ( $this->pipes as $pipe ) {
			$afters[] = $pipe->after;
		}

		return $afters;
	}

	public function zero() {
		return empty( $this->pipes );
	}

	public function random_pipe() {
		if ( $this->zero() ) {
			return null;
		}

		return $this->pipes[array_rand( $this->pipes )];
	}

	public function to_array() {
		return array_map(
			function( WPCF7_Pipe $pipe ) {
				return array(
					$pipe->before,
					$pipe->after,
				);
			},
			$this->pipes
		);
	}
}
