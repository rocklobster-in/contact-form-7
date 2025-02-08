<?php
/**
** A base module for the following types of tags:
** 	[number] and [number*]		# Number
** 	[range] and [range*]		# Range
**/

/* form_tag handler */

add_action( 'wpcf7_init', 'wpcf7_add_form_tag_number', 10, 0 );

function wpcf7_add_form_tag_number() {
	wpcf7_add_form_tag( array( 'number', 'number*', 'range', 'range*' ),
		'wpcf7_number_form_tag_handler',
		array(
			'name-attr' => true,
		)
	);
}

function wpcf7_number_form_tag_handler( $tag ) {
	if ( empty( $tag->name ) ) {
		return '';
	}

	$validation_error = wpcf7_get_validation_error( $tag->name );

	$class = wpcf7_form_controls_class( $tag->type );

	$class .= ' wpcf7-validates-as-number';

	if ( $validation_error ) {
		$class .= ' wpcf7-not-valid';
	}

	$atts = array();

	$atts['class'] = $tag->get_class_option( $class );
	$atts['id'] = $tag->get_id_option();
	$atts['tabindex'] = $tag->get_option( 'tabindex', 'signed_int', true );
	$atts['min'] = $tag->get_option( 'min', 'signed_num', true );
	$atts['max'] = $tag->get_option( 'max', 'signed_num', true );
	$atts['step'] = $tag->get_option( 'step', 'num', true );
	$atts['readonly'] = $tag->has_option( 'readonly' );

	$atts['autocomplete'] = $tag->get_option(
		'autocomplete', '[-0-9a-zA-Z]+', true
	);

	if ( $tag->is_required() ) {
		$atts['aria-required'] = 'true';
	}

	if ( $validation_error ) {
		$atts['aria-invalid'] = 'true';
		$atts['aria-describedby'] = wpcf7_get_validation_error_reference(
			$tag->name
		);
	} else {
		$atts['aria-invalid'] = 'false';
	}

	$value = (string) reset( $tag->values );

	if ( $tag->has_option( 'placeholder' )
	or $tag->has_option( 'watermark' ) ) {
		$atts['placeholder'] = $value;
		$value = '';
	}

	$value = $tag->get_default_option( $value );

	$value = wpcf7_get_hangover( $tag->name, $value );

	$atts['value'] = $value;

	if ( 'range' === $tag->basetype ) {
		if ( ! wpcf7_is_number( $atts['min'] ) ) {
			$atts['min'] = '0';
		}

		if ( ! wpcf7_is_number( $atts['max'] ) ) {
			$atts['max'] = '100';
		}

		if ( '' === $atts['value'] ) {
			if ( $atts['min'] < $atts['max'] ) {
				$atts['value'] = ( $atts['min'] + $atts['max'] ) / 2;
			} else {
				$atts['value'] = $atts['min'];
			}
		}
	}

	$atts['type'] = $tag->basetype;
	$atts['name'] = $tag->name;

	return sprintf(
		'<span class="wpcf7-form-control-wrap" data-name="%1$s"><input %2$s />%3$s</span>',
		esc_attr( $tag->name ),
		wpcf7_format_atts( $atts ),
		$validation_error
	);
}


add_action(
	'wpcf7_swv_create_schema',
	'wpcf7_swv_add_number_rules',
	10, 2
);

function wpcf7_swv_add_number_rules( $schema, $contact_form ) {
	$tags = $contact_form->scan_form_tags( array(
		'basetype' => array( 'number', 'range' ),
	) );

	foreach ( $tags as $tag ) {
		if ( $tag->is_required() ) {
			$schema->add_rule(
				wpcf7_swv_create_rule( 'required', array(
					'field' => $tag->name,
					'error' => wpcf7_get_message( 'invalid_required' ),
				) )
			);
		}

		$schema->add_rule(
			wpcf7_swv_create_rule( 'number', array(
				'field' => $tag->name,
				'error' => wpcf7_get_message( 'invalid_number' ),
			) )
		);

		$min = $tag->get_option( 'min', 'signed_num', true );
		$max = $tag->get_option( 'max', 'signed_num', true );

		if ( 'range' === $tag->basetype ) {
			if ( ! wpcf7_is_number( $min ) ) {
				$min = '0';
			}

			if ( ! wpcf7_is_number( $max ) ) {
				$max = '100';
			}
		}

		if ( wpcf7_is_number( $min ) ) {
			$schema->add_rule(
				wpcf7_swv_create_rule( 'minnumber', array(
					'field' => $tag->name,
					'threshold' => $min,
					'error' => wpcf7_get_message( 'number_too_small' ),
				) )
			);
		}

		if ( wpcf7_is_number( $max ) ) {
			$schema->add_rule(
				wpcf7_swv_create_rule( 'maxnumber', array(
					'field' => $tag->name,
					'threshold' => $max,
					'error' => wpcf7_get_message( 'number_too_large' ),
				) )
			);
		}
	}
}


/* Messages */

add_filter( 'wpcf7_messages', 'wpcf7_number_messages', 10, 1 );

function wpcf7_number_messages( $messages ) {
	return array_merge( $messages, array(
		'invalid_number' => array(
			'description' => __( "Number format that the sender entered is invalid", 'contact-form-7' ),
			'default' => __( "Please enter a number.", 'contact-form-7' ),
		),

		'number_too_small' => array(
			'description' => __( "Number is smaller than minimum limit", 'contact-form-7' ),
			'default' => __( "This field has a too small number.", 'contact-form-7' ),
		),

		'number_too_large' => array(
			'description' => __( "Number is larger than maximum limit", 'contact-form-7' ),
			'default' => __( "This field has a too large number.", 'contact-form-7' ),
		),
	) );
}


/* Tag generator */

add_action( 'wpcf7_admin_init', 'wpcf7_add_tag_generator_number', 18, 0 );

function wpcf7_add_tag_generator_number() {
	$tag_generator = WPCF7_TagGenerator::get_instance();

	$tag_generator->add( 'number', __( 'number', 'contact-form-7' ),
		'wpcf7_tag_generator_number',
	 	array( 'version' => '2' )
	);
}

function wpcf7_tag_generator_number( $contact_form, $options ) {
	$field_types = array(
		'number' => array(
			'display_name' => __( 'Number field', 'contact-form-7' ),
			'heading' => __( 'Number field form-tag generator', 'contact-form-7' ),
			'description' => __( 'Generates a form-tag for a <a href="https://contactform7.com/number-fields/">number input field</a>.', 'contact-form-7' ),
		),
	);

	$tgg = new WPCF7_TagGeneratorGenerator( $options['content'] );

?>
<header class="description-box">
	<h3><?php
		echo esc_html( $field_types['number']['heading'] );
	?></h3>

	<p><?php
		echo wp_kses(
			$field_types['number']['description'],
			array(
				'a' => array( 'href' => true ),
				'strong' => array(),
			),
			array( 'http', 'https' )
		);
	?></p>
</header>

<div class="control-box">
	<?php
		$tgg->print( 'field_type', array(
			'with_required' => true,
			'select_options' => array(
				'number' => __( 'Spinbox', 'contact-form-7' ),
				'range' => __( 'Slider', 'contact-form-7' ),
			),
		) );

		$tgg->print( 'field_name' );

		$tgg->print( 'class_attr' );

		$tgg->print( 'min_max', array(
			'title' => __( 'Range', 'contact-form-7' ),
			'min_option' => 'min:',
			'max_option' => 'max:',
		) );

		$tgg->print( 'default_value', array(
			'type' => 'number',
			'with_placeholder' => false,
		) );
	?>
</div>

<footer class="insert-box">
	<?php
		$tgg->print( 'insert_box_content' );

		$tgg->print( 'mail_tag_tip' );
	?>
</footer>
<?php
}
