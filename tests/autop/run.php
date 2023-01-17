<?php

$test_cases = array(
	'default',
);

foreach ( $test_cases as $test_case ) {
	$dir = path_join( __DIR__, $test_case );

	file_put_contents(
		path_join( $dir, 'output.html' ),
		wpcf7_autop(
			file_get_contents( path_join( $dir, 'input.html' ) )
		)
	);
}
