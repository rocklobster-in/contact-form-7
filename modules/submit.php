<?php
/**
** A base module for [submit]
**/

/* form_tag handler */

add_action( 'wpcf7_init', 'wpcf7_add_form_tag_submit', 10, 0 );

function wpcf7_add_form_tag_submit() {
	wpcf7_add_form_tag( 'submit', 'wpcf7_submit_form_tag_handler' );
}

function wpcf7_submit_form_tag_handler( $tag ) {
	$class = wpcf7_form_controls_class( $tag->type, 'has-spinner' );

	$atts = array();

	$atts['class'] = $tag->get_class_option( $class );
	$atts['id'] = $tag->get_id_option();
	$atts['tabindex'] = $tag->get_option( 'tabindex', 'signed_int', true );

	$value = isset( $tag->values[0] ) ? $tag->values[0] : '';

	if ( empty( $value ) ) {
		$value = __( 'Send', 'contact-form-7' );
	}

	$atts['type'] = 'submit';
	$atts['value'] = $value;

	$atts = wpcf7_format_atts( $atts );

	$html = sprintf( '<input %1$s />', $atts );

	return $html;
}


/* Tag generator */

add_action( 'wpcf7_admin_init', 'wpcf7_add_tag_generator_submit', 55, 0 );

function wpcf7_add_tag_generator_submit() {
	$tag_generator = WPCF7_TagGenerator::get_instance();

	$tag_generator->add( 'submit', __( 'submit', 'contact-form-7' ),
		'wpcf7_tag_generator_submit',
		array( 'version' => '2' )
	);
}

function wpcf7_tag_generator_submit( $contact_form, $options ) {
	$field_types = array(
		'submit' => array(
			'display_name' => __( 'Submit button', 'contact-form-7' ),
			'heading' => __( 'Submit button form-tag generator', 'contact-form-7' ),
			'description' => __( 'Generates a form-tag for a <a href="https://contactform7.com/submit-button/">submit button</a>.', 'contact-form-7' ),
		),
	);

	$tgg = new WPCF7_TagGeneratorGenerator( $options['content'] );

?>
<header class="description-box">
	<h3><?php
		echo esc_html( $field_types['submit']['heading'] );
	?></h3>

	<p><?php
		$description = wp_kses(
			$field_types['submit']['description'],
			array(
				'a' => array( 'href' => true ),
				'strong' => array(),
			),
			array( 'http', 'https' )
		);

		echo $description;
	?></p>
</header>

<div class="control-box">
	<?php
		$tgg->print( 'field_type', array(
			'select_options' => array(
				'submit' => $field_types['submit']['display_name'],
			),
		) );

		$tgg->print( 'class_attr' );

		$tgg->print( 'default_value', array(
			'title' => __( 'Label', 'contact-form-7' ),
		) );
	?>
</div>

<footer class="insert-box">
	<?php
		$tgg->print( 'insert_box_content' );
	?>
</footer>
<?php
}
