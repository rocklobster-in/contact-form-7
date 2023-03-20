<?php

$test_cases = array(
	'default',
	'nest',
	'nonstandard',
	'br',
	'inheader',
);

foreach ( $test_cases as $test_case ) {
	$dir = path_join( __DIR__, $test_case );

	$input = file_get_contents( path_join( $dir, 'input.html' ) );
	$output = wpcf7_autop( $input );

	file_put_contents(
		path_join( $dir, 'output.html' ),
		$output
	);
}
