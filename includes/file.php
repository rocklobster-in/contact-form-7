<?php

class WPCF7_UploadedFileHandler {

	public function unship( $file, $args = '' ) {
		$args = wp_parse_args( $args, array(
			'required' => false,
			'filetypes' => '',
			'limit' => MB_IN_BYTES,
		) );

		if ( ! empty( $file['error'] ) and UPLOAD_ERR_NO_FILE !== $file['error'] ) {
			return new WP_Error( 'wpcf7_upload_failed_php_error',
				wpcf7_get_message( 'upload_failed_php_error' )
			);
		}

		if ( empty( $file['tmp_name'] ) and $args['required'] ) {
			return new WP_Error( 'wpcf7_invalid_required',
				wpcf7_get_message( 'invalid_required' )
			);
		}

		if ( empty( $file['tmp_name'] )
		or ! is_uploaded_file( $file['tmp_name'] ) ) {
			return null;
		}

		/* File type validation */

		$file_type_pattern = wpcf7_acceptable_filetypes(
			$args['filetypes'], 'regex'
		);

		$file_type_pattern = '/\.(' . $file_type_pattern . ')$/i';

		if ( empty( $file['name'] )
		or ! preg_match( $file_type_pattern, $file['name'] ) ) {
			return new WP_Error( 'wpcf7_upload_file_type_invalid',
				wpcf7_get_message( 'upload_file_type_invalid' )
			);
		}

		/* File size validation */

		if ( ! empty( $file['size'] ) and $args['limit'] < $file['size'] ) {
			return new WP_Error( 'wpcf7_upload_file_too_large',
				wpcf7_get_message( 'upload_file_too_large' )
			);
		}

		wpcf7_init_uploads(); // Confirm upload dir
		$uploads_dir = wpcf7_upload_tmp_dir();
		$uploads_dir = wpcf7_maybe_add_random_dir( $uploads_dir );

		$filename = $file['name'];
		$filename = wpcf7_canonicalize( $filename, 'as-is' );
		$filename = wpcf7_antiscript_file_name( $filename );

		$filename = apply_filters( 'wpcf7_upload_file_name', $filename,
			$file['name'], $args
		);

		$filename = wp_unique_filename( $uploads_dir, $filename );
		$new_file = path_join( $uploads_dir, $filename );

		if ( false === @move_uploaded_file( $file['tmp_name'], $new_file ) ) {
			return new WP_Error( 'wpcf7_upload_failed',
				wpcf7_get_message( 'upload_failed' )
			);
		}

		// Make sure the uploaded file is only readable for the owner process
		chmod( $new_file, 0400 );

		return $new_file;
	}

}


add_filter( 'wpcf7_messages', 'wpcf7_file_messages', 10, 1 );

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


add_filter( 'wpcf7_form_enctype', 'wpcf7_file_form_enctype_filter', 10, 1 );

function wpcf7_file_form_enctype_filter( $enctype ) {
	$multipart = (bool) wpcf7_scan_form_tags( array(
		'feature' => 'file-uploading',
	) );

	if ( $multipart ) {
		$enctype = 'multipart/form-data';
	}

	return $enctype;
}


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
			$type );

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


function wpcf7_init_uploads() {
	$dir = wpcf7_upload_tmp_dir();
	wp_mkdir_p( $dir );

	$htaccess_file = path_join( $dir, '.htaccess' );

	if ( file_exists( $htaccess_file ) ) {
		return;
	}

	if ( $handle = fopen( $htaccess_file, 'w' ) ) {
		fwrite( $handle, "Deny from all\n" );
		fclose( $handle );
	}
}


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


function wpcf7_upload_tmp_dir() {
	if ( defined( 'WPCF7_UPLOADS_TMP_DIR' ) ) {
		return WPCF7_UPLOADS_TMP_DIR;
	} else {
		return path_join( wpcf7_upload_dir( 'dir' ), 'wpcf7_uploads' );
	}
}


add_action( 'template_redirect', 'wpcf7_cleanup_upload_files', 20, 0 );

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
