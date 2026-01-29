<?php
/**
 * Contact Form 7 Export Functionality
 *
 * @package WPCF7
 * @subpackage Admin
 * @since 6.2
 */

/**
 * Exports a single contact form as WXR XML.
 *
 * @param WPCF7_ContactForm $contact_form The contact form to export.
 */
function wpcf7_export_contact_form( $contact_form ) {
	if ( ! $contact_form instanceof WPCF7_ContactForm ) {
		wp_die( esc_html( __( 'Invalid contact form.', 'contact-form-7' ) ) );
	}

	$post = get_post( $contact_form->id() );

	if ( ! $post ) {
		wp_die( esc_html( __( 'Contact form not found.', 'contact-form-7' ) ) );
	}

	// Get all post meta for this form
	$post_meta = get_post_meta( $post->ID );

	// Sanitize filename
	$title = sanitize_title( $contact_form->title() );
	$filename = sprintf(
		'contact-form-%s.%s.xml',
		$title,
		date( 'Y-m-d' )
	);

	// Send download headers
	header( 'Content-Type: text/xml; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=' . $filename );
	header( 'Pragma: no-cache' );
	header( 'Expires: 0' );

	// Output WXR XML
	echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
	echo '<rss version="2.0"' . "\n";
	echo '    xmlns:content="http://purl.org/rss/1.0/modules/content/"' . "\n";
	echo '    xmlns:excerpt="http://purl.org/rss/1.0/modules/excerpt/"' . "\n";
	echo '    xmlns:wfw="http://wellformedweb.org/CommentAPI/"' . "\n";
	echo '    xmlns:dc="http://purl.org/dc/elements/1.1/"' . "\n";
	echo '    xmlns:wp="http://wordpress.org/export/1.2/">' . "\n";
	echo '<channel>' . "\n";
	echo '    <title>' . esc_xml( get_bloginfo( 'name' ) ) . '</title>' . "\n";
	echo '    <link>' . esc_xml( home_url() ) . '</link>' . "\n";
	echo '    <description>' . esc_xml( get_bloginfo( 'description' ) ) . '</description>' . "\n";
	echo '    <pubDate>' . esc_xml( date( 'r' ) ) . '</pubDate>' . "\n";
	echo '    <language>' . esc_xml( get_bloginfo_rss( 'language' ) ) . '</language>' . "\n";
	echo '    <wp:wxr_version>1.2</wp:wxr_version>' . "\n";
	echo '    <wp:base_site_url>' . esc_xml( home_url() ) . '</wp:base_site_url>' . "\n";
	echo '    <wp:base_blog_url>' . esc_xml( home_url() ) . '</wp:base_blog_url>' . "\n";

	// Output the item
	echo '    <item>' . "\n";
	echo '        <title>' . esc_xml( $post->post_title ) . '</title>' . "\n";
	echo '        <link>' . esc_xml( get_permalink( $post->ID ) ) . '</link>' . "\n";
	echo '        <pubDate>' . esc_xml( mysql2date( 'r', $post->post_date ) ) . '</pubDate>' . "\n";
	echo '        <dc:creator>' . esc_xml( get_the_author_meta( 'login', $post->post_author ) ) . '</dc:creator>' . "\n";
	echo '        <description></description>' . "\n";
	echo '        <content:encoded><![CDATA[' . $post->post_content . ']]></content:encoded>' . "\n";
	echo '        <excerpt:encoded><![CDATA[]]></excerpt:encoded>' . "\n";
	echo '        <wp:post_id>' . intval( $post->ID ) . '</wp:post_id>' . "\n";
	echo '        <wp:post_date>' . esc_xml( $post->post_date ) . '</wp:post_date>' . "\n";
	echo '        <wp:post_date_gmt>' . esc_xml( $post->post_date_gmt ) . '</wp:post_date_gmt>' . "\n";
	echo '        <wp:post_modified>' . esc_xml( $post->post_modified ) . '</wp:post_modified>' . "\n";
	echo '        <wp:post_modified_gmt>' . esc_xml( $post->post_modified_gmt ) . '</wp:post_modified_gmt>' . "\n";
	echo '        <wp:comment_status>closed</wp:comment_status>' . "\n";
	echo '        <wp:ping_status>closed</wp:ping_status>' . "\n";
	echo '        <wp:post_name>' . esc_xml( $post->post_name ) . '</wp:post_name>' . "\n";
	echo '        <wp:status>publish</wp:status>' . "\n";
	echo '        <wp:post_parent>0</wp:post_parent>' . "\n";
	echo '        <wp:menu_order>0</wp:menu_order>' . "\n";
	echo '        <wp:post_type>wpcf7_contact_form</wp:post_type>' . "\n";
	echo '        <wp:post_password></wp:post_password>' . "\n";
	echo '        <wp:is_sticky>0</wp:is_sticky>' . "\n";

	// Output all post meta
	foreach ( $post_meta as $key => $values ) {
		foreach ( $values as $value ) {
			// Skip internal meta keys
			if ( '_edit_lock' === $key || '_edit_last' === $key ) {
				continue;
			}

			echo '        <wp:postmeta>' . "\n";
			echo '            <wp:meta_key>' . esc_xml( $key ) . '</wp:meta_key>' . "\n";
			echo '            <wp:meta_value><![CDATA[' . $value . ']]></wp:meta_value>' . "\n";
			echo '        </wp:postmeta>' . "\n";
		}
	}

	echo '    </item>' . "\n";
	echo '</channel>' . "\n";
	echo '</rss>' . "\n";

	exit;
}
