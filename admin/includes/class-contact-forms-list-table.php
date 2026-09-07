<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class WPCF7_Contact_Form_List_Table extends WP_List_Table {

	private static $instance;

	public static function get_instance() {
		self::$instance ??= new self();

		return self::$instance;
	}

	private function __construct() {
		parent::__construct( array(
			'singular' => 'post',
			'plural' => 'posts',
			'ajax' => false,
		) );
	}

	public function prepare_items() {
		$per_page = $this->get_items_per_page( 'wpcf7_contact_forms_per_page' );
		$order = wpcf7_superglobal_get( 'order' );
		$orderby = wpcf7_superglobal_get( 'orderby' );

		if ( '' === $orderby ) {
			$orderby = 'date';
		}

		if ( '' === $order ) {
			$order = ( 'date' === $orderby ) ? 'desc' : 'asc';
		}

		$args = array(
			'posts_per_page' => $per_page,
			'order' => $order,
			'orderby' => $orderby,
			'offset' => ( $this->get_pagenum() - 1 ) * $per_page,
			's' => wpcf7_superglobal_get( 's' ),
		);

		$this->items = WPCF7_ContactForm::find( $args );

		$total_items = WPCF7_ContactForm::count();
		$total_pages = ceil( $total_items / $per_page );

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'total_pages' => $total_pages,
			'per_page' => $per_page,
		) );
	}

	public function get_columns() {
		return array(
			'cb' => '<input type="checkbox" />',
			'title' => __( 'Title', 'contact-form-7' ),
			'author' => __( 'Author', 'contact-form-7' ),
			'date' => __( 'Date', 'contact-form-7' ),
		);
	}

	protected function get_sortable_columns() {
		return array(
			'title' => array(
				'title',
				false,
				__( 'Title', 'contact-form-7' ),
				__( 'Table ordered by Title.', 'contact-form-7' ),
				'asc'
			),
			'author' => array(
				'author',
				false,
				__( 'Author', 'contact-form-7' ),
				__( 'Table ordered by Author.', 'contact-form-7' ),
				'asc'
			),
			'date' => array(
				'date',
				false,
				__( 'Date', 'contact-form-7' ),
				__( 'Table ordered by Date.', 'contact-form-7' ),
				'desc'
			),
		);
	}

	protected function get_bulk_actions() {
		$post_type_obj = get_post_type_object( WPCF7_ContactForm::post_type );

		if ( current_user_can( $post_type_obj->cap->edit_posts ) ) {
			return array(
				'delete' => __( 'Delete', 'contact-form-7' ),
			);
		} else {
			return array();
		}
	}

	public function column_default( $item, $column_name ) {
		return '';
	}

	public function column_cb( $item ) {
		$post_type_obj = get_post_type_object( WPCF7_ContactForm::post_type );
		$show = current_user_can( $post_type_obj->cap->edit_post, $item->id() );

		if ( ! $show ) {
			return '';
		}

		$formatter = new WPCF7_HTMLFormatter();

		$formatter->append_start_tag( 'input', array(
			'id' => sprintf( 'cb-select-%d', $item->id() ),
			'type' => 'checkbox',
			'name' => sprintf( '%s[]', $this->_args['singular'] ),
			'value' => $item->id(),
		) );

		$formatter->append_start_tag( 'label', array(
			'for' => sprintf( 'cb-select-%d', $item->id() ),
		) );

		$formatter->append_start_tag( 'span', array(
			'class' => 'screen-reader-text',
		) );

		$formatter->append_preformatted(
			esc_html( sprintf(
				/* translators: %s: contact form title */
				__( 'Select %s', 'contact-form-7' ),
				$item->title()
			) )
		);

		$formatter->print();
	}

	public function column_title( $item ) {
		$formatter = new WPCF7_HTMLFormatter();

		$formatter->append_start_tag( 'strong' );

		$edit_link = add_query_arg(
			array(
				'post' => absint( $item->id() ),
				'action' => 'edit',
			),
			menu_page_url( 'wpcf7', false )
		);

		$formatter->append_start_tag( 'a', array(
			'class' => 'row-title',
			'href' => esc_url( $edit_link ),
			'aria-label' => sprintf(
				/* translators: %s: title of contact form */
				__( 'Edit &#8220;%s&#8221;', 'contact-form-7' ),
				$item->title()
			),
		) );

		$formatter->append_preformatted( esc_html( $item->title() ) );

		$formatter->end_tag( 'strong' );

		$post_type_obj = get_post_type_object( WPCF7_ContactForm::post_type );

		if (
			wpcf7_validate_configuration() and
			current_user_can( $post_type_obj->cap->edit_post, $item->id() )
		) {
			$config_validator = new WPCF7_ConfigValidator( $item );
			$config_validator->restore();

			if ( $count_errors = $config_validator->count_errors() ) {
				$error_notice = sprintf(
					/* translators: %s: number of errors detected */
					_n(
						'%s configuration error detected',
						'%s configuration errors detected',
						$count_errors,
						'contact-form-7'
					),
					number_format_i18n( $count_errors )
				);

				$formatter->append_start_tag( 'div', array(
					'class' => 'config-error',
				) );

				$formatter->append_start_tag( 'span', array(
					'class' => 'icon-in-circle',
					'aria-hidden' => 'true',
				) );

				$formatter->append_preformatted( '!' );

				$formatter->end_tag( 'span' );

				$formatter->append_whitespace();

				$formatter->append_preformatted( $error_notice );

				$formatter->end_tag( 'div' );
			}
		}

		$formatter->print();
	}

	protected function handle_row_actions( $item, $column_name, $primary ) {
		if ( $column_name !== $primary ) {
			return '';
		}

		$edit_link = add_query_arg(
			array(
				'post' => absint( $item->id() ),
				'action' => 'edit',
			),
			menu_page_url( 'wpcf7', false )
		);

		$actions = array(
			'edit' => wpcf7_link( $edit_link, __( 'Edit', 'contact-form-7' ) ),
		);

		if ( current_user_can( 'wpcf7_edit_contact_form', $item->id() ) ) {
			$copy_link = add_query_arg(
				array(
					'post' => absint( $item->id() ),
					'action' => 'copy',
				),
				menu_page_url( 'wpcf7', false )
			);

			$copy_link = wp_nonce_url(
				$copy_link,
				'wpcf7-copy-contact-form_' . absint( $item->id() )
			);

			$actions = array_merge( $actions, array(
				'copy' => wpcf7_link(
					$copy_link,
					__( 'Duplicate', 'contact-form-7' )
				),
			) );
		}

		return $this->row_actions( $actions );
	}

	public function column_author( $item ) {
		$post = get_post( $item->id() );

		if ( ! $post ) {
			return;
		}

		$author = get_userdata( $post->post_author );

		if ( false === $author ) {
			return;
		}

		echo esc_html( $author->display_name );
	}

	public function column_date( $item ) {
		$datetime = get_post_datetime( $item->id() );

		if ( false === $datetime ) {
			return '';
		}

		$t_time = sprintf(
			/* translators: 1: date, 2: time */
			__( '%1$s at %2$s', 'contact-form-7' ),
			/* translators: date format, see https://www.php.net/date */
			$datetime->format( __( 'Y/m/d', 'contact-form-7' ) ),
			/* translators: time format, see https://www.php.net/date */
			$datetime->format( __( 'g:i a', 'contact-form-7' ) )
		);

		echo $t_time;
	}
}
