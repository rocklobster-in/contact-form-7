<?php

function wpcf7_parse_html( string $input ) {
	$input_bytelength = strlen( $input );
	$position = 0;

	while ( $position < $input_bytelength ) {
		$next_lt = strpos( $input, '<', $position );

		if ( false === $next_lt ) {
			yield array(
				'position' => $position,
				'type' => 'text',
				'chunk' => substr( $input, $position ),
			);

			break;
		}

		$text_node = substr( $input, $position, $next_lt - $position );

		if ( '' !== $text_node ) {
			yield array(
				'position' => $position,
				'type' => 'text',
				'chunk' => $text_node,
			);
		}

		if ( '<!--' === substr( $input, $next_lt, 4 ) ) { // Comment
			$counterpart = strpos( $input, '-->', $next_lt );

			if ( false === $counterpart ) {
				yield array(
					'position' => $next_lt,
					'type' => 'comment',
					'chunk' => substr( $input, $next_lt ),
				);

				break;
			} else {
				yield array(
					'position' => $next_lt,
					'type' => 'comment',
					'chunk' => substr( $input, $next_lt, $counterpart + 3 - $next_lt ),
				);

				$position = $counterpart + 3;
				continue;
			}
		}

		if ( preg_match( '/^<(\/?)[a-z].*?>/i', $input, $matches, 0, $next_lt ) ) {
			yield array(
				'position' => $next_lt,
				'type' => ( '/' === $matches[1] ) ? 'closing_tag' : 'opening_tag',
				'chunk' => $matches[0],
			);

			$position = $next_lt + strlen( $matches[0] );
			continue;
		}

		// Floating '<', not component of a tag or comment.
		yield array(
			'position' => $next_lt,
			'type' => 'text',
			'chunk' => '<',
		);

		$position = $next_lt + 1;
	}
}
