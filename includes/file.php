<?php

/**
 * Validates uploaded files and moves them to the temporary directory.
 *
 * @param array $file An item of `$_FILES`.
 * @param string|array $args Optional. Arguments to control behavior.
 * @return array|WP_Error Array of file paths, or WP_Error if validation fails.
 */
function wpcf7_unship_uploaded_file( $file, $args = '' ) {
	$args = wp_parse_args( $args, array(
		'required' => false,
		'filetypes' => '',
		'limit' => MB_IN_BYTES,
	) );

	foreach ( array( 'name', 'size', 'tmp_name', 'error' ) as $key ) {
		if ( ! isset( $file[$key] ) ) {
			$file[$key] = array();
		}
	}

	$names = wpcf7_array_flatten( $file['name'] );
	$sizes = wpcf7_array_flatten( $file['size'] );
	$tmp_names = wpcf7_array_flatten( $file['tmp_name'] );
	$errors = wpcf7_array_flatten( $file['error'] );

	foreach ( $errors as $error ) {
		if ( ! empty( $error ) and UPLOAD_ERR_NO_FILE !== $error ) {
			return new WP_Error( 'wpcf7_upload_failed_php_error',
				wpcf7_get_message( 'upload_failed_php_error' )
			);
		}
	}

	if ( $args['required'] and ! array_filter( $tmp_names ) ) {
		return new WP_Error( 'wpcf7_invalid_required',
			wpcf7_get_message( 'invalid_required' )
		);
	}

	// File type validation
	$file_type_pattern = wpcf7_acceptable_filetypes(
		$args['filetypes'], 'regex'
	);

	$file_type_pattern = '/\.(' . $file_type_pattern . ')$/i';

	foreach ( $names as $name ) {
		if ( ! empty( $name ) and ! preg_match( $file_type_pattern, $name ) ) {
			return new WP_Error( 'wpcf7_upload_file_type_invalid',
				wpcf7_get_message( 'upload_file_type_invalid' )
			);
		}
	}

	// File size validation
	$total_size = array_sum( $sizes );

	if ( $args['limit'] < $total_size ) {
		return new WP_Error( 'wpcf7_upload_file_too_large',
			wpcf7_get_message( 'upload_file_too_large' )
		);
	}

	// Move uploaded file to tmp dir
	$uploads_dir = wpcf7_upload_tmp_dir();
	$uploads_dir = wpcf7_maybe_add_random_dir( $uploads_dir );

	$uploaded_files = array();

	foreach ( $names as $key => $name ) {
		$tmp_name = $tmp_names[$key];

		if ( empty( $tmp_name ) or ! is_uploaded_file( $tmp_name ) ) {
			continue;
		}

		$filename = $name;
		$filename = wpcf7_canonicalize( $filename, array( 'strto' => 'as-is' ) );
		$filename = wpcf7_antiscript_file_name( $filename );

		$filename = apply_filters( 'wpcf7_upload_file_name',
			$filename, $name, $args
		);

		$filename = wp_unique_filename( $uploads_dir, $filename );
		$new_file = path_join( $uploads_dir, $filename );

		if ( false === @move_uploaded_file( $tmp_name, $new_file ) ) {
			return new WP_Error( 'wpcf7_upload_failed',
				wpcf7_get_message( 'upload_failed' )
			);
		}

		// Make sure the uploaded file is only readable for the owner process
		chmod( $new_file, 0400 );

		$uploaded_files[] = $new_file;
	}

	return $uploaded_files;
}


add_filter(
	'wpcf7_messages',
	'wpcf7_file_messages',
	10, 1
);

/**
 * A wpcf7_messages filter callback that adds messages for
 * file-uploading fields.
 */
function wpcf7_file_messages( $messages ) {
	return array_merge( $messages, array(
		'upload_failed' => array(
			'description' => __( "Uploading a file fails for any reason", 'contact-form-7' ),
			'default' => __( "There was an unknown error uploading the file.", 'contact-form-7' )
		),

		'upload_file_type_invalid' => array(
			'description' => __( "Uploaded file is not allowed for file type", 'contact-form-7' ),
			'default' => __( "You are not allowed to upload files of this type.", 'contact-form-7' )
		),

		'upload_file_too_large' => array(
			'description' => __( "Uploaded file is too large", 'contact-form-7' ),
			'default' => __( "The file is too big.", 'contact-form-7' )
		),

		'upload_failed_php_error' => array(
			'description' => __( "Uploading a file fails for PHP error", 'contact-form-7' ),
			'default' => __( "There was an error uploading the file.", 'contact-form-7' )
		)
	) );
}


add_filter(
	'wpcf7_form_enctype',
	'wpcf7_file_form_enctype_filter',
	10, 1
);

/**
 * A wpcf7_form_enctype filter callback that sets the enctype attribute
 * to multipart/form-data if the form has file-uploading fields.
 */
function wpcf7_file_form_enctype_filter( $enctype ) {
	$multipart = (bool) wpcf7_scan_form_tags( array(
		'feature' => 'file-uploading',
	) );

	if ( $multipart ) {
		$enctype = 'multipart/form-data';
	}

	return $enctype;
}


/**
 * Returns a formatted list of acceptable filetypes.
 *
 * @param string|array $types Optional. Array of filetypes.
 * @param string $format Optional. Pre-defined format designator.
 * @return string Formatted list of acceptable filetypes.
 */
function wpcf7_acceptable_filetypes( $types = 'default', $format = 'regex' ) {
	if ( 'default' === $types
	or empty( $types ) ) {
		$types = array(
			'jpg',
			'jpeg',
			'png',
			'gif',
			'pdf',
			'doc',
			'docx',
			'ppt',
			'pptx',
			'odt',
			'avi',
			'ogg',
			'm4a',
			'mov',
			'mp3',
			'mp4',
			'mpg',
			'wav',
			'wmv',
		);
	} else {
		$types_tmp = (array) $types;
		$types = array();

		foreach ( $types_tmp as $val ) {
			if ( is_string( $val ) ) {
				$val = preg_split( '/[\s|,]+/', $val );
			}

			$types = array_merge( $types, (array) $val );
		}
	}

	$types = array_unique( array_filter( $types ) );

	$output = '';

	foreach ( $types as $type ) {
		$type = trim( $type, ' ,.|' );

		$type = str_replace(
			array( '.', '+', '*', '?' ),
			array( '\.', '\+', '\*', '\?' ),
			$type
		);

		if ( '' === $type ) {
			continue;
		}

		if ( 'attr' === $format
		or 'attribute' === $format ) {
			$output .= sprintf( '.%s', $type );
			$output .= ',';
		} else {
			$output .= $type;
			$output .= '|';
		}
	}

	return trim( $output, ' ,|' );
}


add_action(
	'wpcf7_init',
	'wpcf7_init_uploads',
	10, 0
);

/**
 * Initializes the temporary directory for uploaded files.
 */
function wpcf7_init_uploads() {
	$dir = wpcf7_upload_tmp_dir();

	if ( is_dir( $dir ) and is_writable( $dir ) ) {
		$htaccess_file = path_join( $dir, '.htaccess' );

		if ( ! file_exists( $htaccess_file )
		and $handle = @fopen( $htaccess_file, 'w' ) ) {
			fwrite( $handle, "Deny from all\n" );
			fclose( $handle );
		}
	}
}


/**
 * Creates a child directory with a randomly generated name.
 *
 * @param string $dir The parent directory path.
 * @return string The child directory path if created, otherwise the parent.
 */
function wpcf7_maybe_add_random_dir( $dir ) {
	do {
		$rand_max = mt_getrandmax();
		$rand = zeroise( mt_rand( 0, $rand_max ), strlen( $rand_max ) );
		$dir_new = path_join( $dir, $rand );
	} while ( file_exists( $dir_new ) );

	if ( wp_mkdir_p( $dir_new ) ) {
		return $dir_new;
	}

	return $dir;
}


/**
 * Returns the directory path for uploaded files.
 *
 * @return string Directory path.
 */
function wpcf7_upload_tmp_dir() {
	if ( defined( 'WPCF7_UPLOADS_TMP_DIR' ) ) {
		$dir = path_join( WP_CONTENT_DIR, WPCF7_UPLOADS_TMP_DIR );
		wp_mkdir_p( $dir );

		if ( wpcf7_is_file_path_in_content_dir( $dir ) ) {
			return $dir;
		}
	}

	$dir = path_join( wpcf7_upload_dir( 'dir' ), 'wpcf7_uploads' );
	wp_mkdir_p( $dir );
	return $dir;
}


add_action(
	'template_redirect',
	'wpcf7_cleanup_upload_files',
	20, 0
);

/**
 * Cleans up files in the temporary directory for uploaded files.
 *
 * @param int $seconds Files older than this are removed. Default 60.
 * @param int $max Maximum number of files to be removed in a function call.
 *                 Default 100.
 */
function wpcf7_cleanup_upload_files( $seconds = 60, $max = 100 ) {
	if ( is_admin()
	or 'GET' != $_SERVER['REQUEST_METHOD']
	or is_robots()
	or is_feed()
	or is_trackback() ) {
		return;
	}

	$dir = trailingslashit( wpcf7_upload_tmp_dir() );

	if ( ! is_dir( $dir )
	or ! is_readable( $dir )
	or ! wp_is_writable( $dir ) ) {
		return;
	}

	$seconds = absint( $seconds );
	$max = absint( $max );
	$count = 0;

	if ( $handle = opendir( $dir ) ) {
		while ( false !== ( $file = readdir( $handle ) ) ) {
			if ( '.' == $file
			or '..' == $file
			or '.htaccess' == $file ) {
				continue;
			}

			$mtime = @filemtime( path_join( $dir, $file ) );

			if ( $mtime and time() < $mtime + $seconds ) { // less than $seconds old
				continue;
			}

			wpcf7_rmdir_p( path_join( $dir, $file ) );
			$count += 1;

			if ( $max <= $count ) {
				break;
			}
		}

		closedir( $handle );
	}
}


add_action(
	'wpcf7_admin_warnings',
	'wpcf7_file_display_warning_message',
	10, 3
);

/**
 * Displays warning messages about file-uploading fields.
 */
function wpcf7_file_display_warning_message( $page, $action, $object ) {
	if ( $object instanceof WPCF7_ContactForm ) {
		$contact_form = $object;
	} else {
		return;
	}

	$has_tags = (bool) $contact_form->scan_form_tags( array(
		'feature' => 'file-uploading',
	) );

	if ( ! $has_tags ) {
		return;
	}

	$uploads_dir = wpcf7_upload_tmp_dir();

	if ( ! is_dir( $uploads_dir ) or ! wp_is_writable( $uploads_dir ) ) {
		$message = sprintf(
			/* translators: %s: the path of the temporary folder */
			__( 'This contact form has file uploading fields, but the temporary folder for the files (%s) does not exist or is not writable. You can create the folder or change its permission manually.', 'contact-form-7' ),
			$uploads_dir
		);

		echo sprintf(
			'<div class="notice notice-warning"><p>%s</p></div>',
			esc_html( $message )
		);
	}
}
