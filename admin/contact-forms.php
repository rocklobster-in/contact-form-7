<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$list_table = WPCF7_List_Table::get_instance();
$list_table->prepare_items();

$formatter = new WPCF7_HTMLFormatter( array(
	'allowed_html' => array_merge( wpcf7_kses_allowed_html(), array(
		'form' => array(
			'method' => true,
		),
	) ),
) );

$formatter->append_start_tag( 'div', array(
	'class' => 'wrap',
	'id' => 'wpcf7-contact-form-list-table',
) );

$formatter->append_start_tag( 'h1', array(
	'class' => 'wp-heading-inline',
) );

$formatter->append_preformatted(
	esc_html( __( 'Contact Forms', 'contact-form-7' ) )
);

$formatter->end_tag( 'h1' );

if ( current_user_can( 'wpcf7_edit_contact_forms' ) ) {
	$formatter->append_preformatted(
		wpcf7_link(
			menu_page_url( 'wpcf7-new', false ),
			__( 'Add Contact Form', 'contact-form-7' ),
			array( 'class' => 'page-title-action' )
		)
	);
}

if ( $search_keyword = wpcf7_superglobal_request( 's' ) ) {
	$formatter->append_start_tag( 'span', array(
		'class' => 'subtitle',
	) );

	$formatter->append_preformatted(
		sprintf(
			/* translators: %s: Search query. */
			__( 'Search results for: <strong>%s</strong>', 'contact-form-7' ),
			esc_html( $search_keyword )
		)
	);

	$formatter->end_tag( 'span' );
}

$formatter->append_start_tag( 'hr', array(
	'class' => 'wp-header-end',
) );

$formatter->call_user_func( static function () {
	do_action( 'wpcf7_admin_warnings',
		'wpcf7', wpcf7_current_action(), null
	);

	wpcf7_welcome_panel();

	do_action( 'wpcf7_admin_notices',
		'wpcf7', wpcf7_current_action(), null
	);
} );

$formatter->append_start_tag( 'form', array(
	'method' => 'get',
) );

$formatter->append_start_tag( 'input', array(
	'type' => 'hidden',
	'name' => 'page',
	'value' => wpcf7_superglobal_request( 'page' ),
) );

$formatter->call_user_func( static function () use ( $list_table ) {
	$list_table->search_box(
		__( 'Search Contact Forms', 'contact-form-7' ),
		'wpcf7-contact'
	);

	$list_table->display();
} );

$formatter->print();
