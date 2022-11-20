<?php

class WPCF7_HTMLIterator {

	const text = 0;
	const opening_tag = 1;
	const closing_tag = 2;
	const comment = 3;

	private $input = '';

	public function __construct( string $input ) {
		$this->input = $input;
	}

	public function iterate() {
		$input = &$this->input;
		$input_bytelength = strlen( $input );
		$position = 0;

		while ( $position < $input_bytelength ) {
			$next_tag = preg_match(
				'/(?:<!--.*?-->|<(?:\/?)[a-z].*?>)/i',
				$input,
				$matches,
				PREG_OFFSET_CAPTURE,
				$position
			);

			if ( ! $next_tag ) {
				yield array(
					'position' => $position,
					'type' => self::text,
					'content' => substr( $input, $position ),
				);

				break;
			}

			$next_tag = $matches[0][0];
			$next_tag_position = $matches[0][1];

			if ( $position < $next_tag_position ) {
				yield array(
					'position' => $position,
					'type' => self::text,
					'content' => substr(
						$input,
						$position,
						$next_tag_position - $position
					),
				);
			}

			if ( '<!' === substr( $next_tag, 0, 2 ) ) {
				$next_tag_type = self::comment;
			} elseif ( '</' === substr( $next_tag, 0, 2 ) ) {
				$next_tag_type = self::closing_tag;
			} else {
				$next_tag_type = self::opening_tag;
			}

			yield array(
				'position' => $next_tag_position,
				'type' => $next_tag_type,
				'content' => substr(
					$input,
					$next_tag_position,
					strlen( $next_tag )
				),
			);

			$position = $next_tag_position + strlen( $next_tag );
		}
	}

}
