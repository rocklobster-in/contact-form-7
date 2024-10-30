<?php

// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

$save_button = sprintf(
	'<input %s />',
	wpcf7_format_atts( array(
		'type' => 'submit',
		'class' => 'button-primary',
		'name' => 'wpcf7-save',
		'value' => __( 'Save', 'contact-form-7' ),
	) )
);

?>

<div class="wrap" id="wpcf7-contact-form-editor">

<?php

echo sprintf(
	'<h1 class="wp-heading-inline">%1$s</h1> %2$s',
	esc_html(
		$post->initial()
		? __( 'Add New Contact Form', 'contact-form-7' )
		: __( 'Edit Contact Form', 'contact-form-7' )
	),
	( $post->initial() || ! current_user_can( 'wpcf7_edit_contact_forms' ) )
	? ''
	: wpcf7_link(
		menu_page_url( 'wpcf7-new', false ),
		__( 'Add New', 'contact-form-7' ),
		array( 'class' => 'page-title-action' )
	)
);

?>

<hr class="wp-header-end">

<?php

do_action( 'wpcf7_admin_warnings',
	$post->initial() ? 'wpcf7-new' : 'wpcf7',
	wpcf7_current_action(),
	$post
);

do_action( 'wpcf7_admin_notices',
	$post->initial() ? 'wpcf7-new' : 'wpcf7',
	wpcf7_current_action(),
	$post
);

if ( $post ) :

	echo sprintf(
		'<form %s>',
		wpcf7_format_atts( array(
			'method' => 'post',
			'action' => esc_url( add_query_arg(
				array( 'post' => $post_id ),
				menu_page_url( 'wpcf7', false )
			) ),
			'id' => 'wpcf7-admin-form-element',
			'disabled' => ! current_user_can( 'wpcf7_edit_contact_form', $post_id ),
		) )
	);

	if ( current_user_can( 'wpcf7_edit_contact_form', $post_id ) ) {
		wp_nonce_field( 'wpcf7-save-contact-form_' . $post_id );
	}

?>
<input type="hidden" id="post_ID" name="post_ID" value="<?php echo (int) $post_id; ?>" />
<input type="hidden" id="wpcf7-locale" name="wpcf7-locale" value="<?php echo esc_attr( $post->locale() ); ?>" />
<input type="hidden" id="hiddenaction" name="action" value="save" />
<input type="hidden" id="active-tab" name="active-tab" value="<?php echo esc_attr( $_GET['active-tab'] ?? '' ); ?>" />

<div id="poststuff">
<div id="post-body" class="metabox-holder columns-2 wp-clearfix">
<div id="post-body-content">
<div id="titlediv">
<div id="titlewrap">
<?php

	echo sprintf(
		'<input %s />',
		wpcf7_format_atts( array(
			'type' => 'text',
			'name' => 'post_title',
			'value' => $post->initial() ? '' : $post->title(),
			'id' => 'title',
			'spellcheck' => 'true',
			'autocomplete' => 'off',
			'disabled' => ! current_user_can( 'wpcf7_edit_contact_form', $post_id ),
			'placeholder' => __( 'Enter title here', 'contact-form-7' ),
			'aria-label' => __( 'Enter title here', 'contact-form-7' ),
		) )
	);

?>
</div><!-- #titlewrap -->

<div class="inside">
<?php

	if ( ! $post->initial() ) {

		if ( $shortcode = $post->shortcode() ) {
			echo sprintf(
				'<p class="description"><label for="wpcf7-shortcode">%1$s</label> <span class="shortcode wp-ui-highlight"><input %2$s /></span></p>',
				esc_html( __( "Copy this shortcode and paste it into your post, page, or text widget content:", 'contact-form-7' ) ),
				wpcf7_format_atts( array(
					'type' => 'text',
					'id' => 'wpcf7-shortcode',
					'onfocus' => 'this.select();',
					'readonly' => true,
					'class' => 'large-text code',
					'value' => $shortcode,
				) )
			);
		}

		if ( $shortcode = $post->shortcode( array( 'use_old_format' => true ) ) ) {
			echo sprintf(
				'<p class="description"><label for="wpcf7-shortcode-old">%1$s</label> <span class="shortcode old"><input %2$s /></span></p>',
				esc_html( __( "You can also use this old-style shortcode:", 'contact-form-7' ) ),
				wpcf7_format_atts( array(
					'type' => 'text',
					'id' => 'wpcf7-shortcode-old',
					'onfocus' => 'this.select();',
					'readonly' => true,
					'class' => 'large-text code',
					'value' => $shortcode,
				) )
			);
		}

	}

?>
</div>

</div><!-- #titlediv -->
</div><!-- #post-body-content -->

<div id="postbox-container-1" class="postbox-container">

<?php

	if ( current_user_can( 'wpcf7_edit_contact_form', $post_id ) ) :

?>

<section id="submitdiv" class="postbox">
<h2><?php echo esc_html( __( 'Status', 'contact-form-7' ) ); ?></h2>
<div class="inside">
<div class="submitbox" id="submitpost">

<div id="minor-publishing-actions">

<div class="hidden">
	<input type="submit" class="button-primary" name="wpcf7-save" value="<?php echo esc_attr( __( 'Save', 'contact-form-7' ) ); ?>" />
</div>

<?php

		if ( ! $post->initial() ) {
			echo sprintf(
				'<input %s />',
				wpcf7_format_atts( array(
					'type' => 'submit',
					'name' => 'wpcf7-copy',
					'class' => 'copy button',
					'value' => __( 'Duplicate', 'contact-form-7' ),
				) )
			);
		}

?>

</div><!-- #minor-publishing-actions -->

<div id="misc-publishing-actions">

<?php

		do_action( 'wpcf7_admin_misc_pub_section', $post_id );

?>
</div><!-- #misc-publishing-actions -->

<div id="major-publishing-actions">

<?php

		if ( ! $post->initial() ) {
			echo sprintf(
				'<div id="delete-action"><input %s /></div>',
				wpcf7_format_atts( array(
					'type' => 'submit',
					'name' => 'wpcf7-delete',
					'class' => 'delete submitdelete',
					'value' => __( 'Delete', 'contact-form-7' ),
				) )
			);
		}

?>

<div id="publishing-action">
	<span class="spinner"></span>
	<?php echo $save_button; ?>
</div>
<div class="clear"></div>
</div><!-- #major-publishing-actions -->
</div><!-- #submitpost -->
</div>
</section><!-- #submitdiv -->

<?php

	endif;

?>

<section id="informationdiv" class="postbox">
<h2><?php echo esc_html( __( "Do you need help?", 'contact-form-7' ) ); ?></h2>
<div class="inside">
	<p><?php echo esc_html( __( "Here are some available options to help solve your problems.", 'contact-form-7' ) ); ?></p>
	<ol>
		<li><?php echo sprintf(
			/* translators: 1: FAQ, 2: Docs ("FAQ & Docs") */
			__( '%1$s and %2$s', 'contact-form-7' ),
			wpcf7_link(
				__( 'https://contactform7.com/faq/', 'contact-form-7' ),
				__( 'FAQ', 'contact-form-7' )
			),
			wpcf7_link(
				__( 'https://contactform7.com/docs/', 'contact-form-7' ),
				__( 'docs', 'contact-form-7' )
			)
		); ?></li>
		<li><?php echo wpcf7_link(
			__( 'https://wordpress.org/support/plugin/contact-form-7/', 'contact-form-7' ),
			__( 'Support forums', 'contact-form-7' )
		); ?></li>
		<li><?php echo wpcf7_link(
			__( 'https://contactform7.com/custom-development/', 'contact-form-7' ),
			__( 'Professional services', 'contact-form-7' )
		); ?></li>
	</ol>
</div>
</section><!-- #informationdiv -->

</div><!-- #postbox-container-1 -->

<div id="postbox-container-2" class="postbox-container">
<div id="contact-form-editor" data-active-tab="">

<?php

	$editor = new WPCF7_Editor( $post );
	$panels = array();

	if ( current_user_can( 'wpcf7_edit_contact_form', $post_id ) ) {
		$panels = array(
			'form-panel' => array(
				'title' => __( 'Form', 'contact-form-7' ),
				'callback' => 'wpcf7_editor_panel_form',
			),
			'mail-panel' => array(
				'title' => __( 'Mail', 'contact-form-7' ),
				'callback' => 'wpcf7_editor_panel_mail',
			),
			'messages-panel' => array(
				'title' => __( 'Messages', 'contact-form-7' ),
				'callback' => 'wpcf7_editor_panel_messages',
			),
		);

		$additional_settings = $post->prop( 'additional_settings' );

		if ( ! is_scalar( $additional_settings ) ) {
			$additional_settings = '';
		}

		$additional_settings = trim( $additional_settings );
		$additional_settings = explode( "\n", $additional_settings );
		$additional_settings = array_filter( $additional_settings );
		$additional_settings = count( $additional_settings );

		$panels['additional-settings-panel'] = array(
			'title' => $additional_settings
				? sprintf(
					/* translators: %d: number of additional settings */
					__( 'Additional Settings (%d)', 'contact-form-7' ),
					$additional_settings )
				: __( 'Additional Settings', 'contact-form-7' ),
			'callback' => 'wpcf7_editor_panel_additional_settings',
		);
	}

	$panels = apply_filters( 'wpcf7_editor_panels', $panels );

	foreach ( $panels as $id => $panel ) {
		$editor->add_panel( $id, $panel['title'], $panel['callback'] );
	}

	$editor->display();

?>
</div><!-- #contact-form-editor -->

<?php

	if ( current_user_can( 'wpcf7_edit_contact_form', $post_id ) ) {
		echo sprintf(
			'<p class="submit">%s</p>',
			$save_button
		);
	}

?>

</div><!-- #postbox-container-2 -->

</div><!-- #post-body -->
<br class="clear" />
</div><!-- #poststuff -->
</form>

<?php

endif;

?>

</div><!-- .wrap -->

<?php

$tag_generator = WPCF7_TagGenerator::get_instance();
$tag_generator->print_panels( $post );

do_action( 'wpcf7_admin_footer', $post );
