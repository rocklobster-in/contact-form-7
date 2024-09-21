<?php

require_once WPCF7_PLUGIN_DIR . '/admin/includes/admin-functions.php';
require_once WPCF7_PLUGIN_DIR . '/admin/includes/help-tabs.php';
require_once WPCF7_PLUGIN_DIR . '/admin/includes/tag-generator.php';
require_once WPCF7_PLUGIN_DIR . '/admin/includes/welcome-panel.php';
require_once WPCF7_PLUGIN_DIR . '/admin/includes/config-validator.php';


add_action(
	'admin_init',
	static function () {
		do_action( 'wpcf7_admin_init' );
	},
	10, 0
);


add_action(
	'admin_menu',
	'wpcf7_admin_menu',
	9, 0
);

function wpcf7_admin_menu() {
	do_action( 'wpcf7_admin_menu' );

	add_menu_page(
		__( 'Contact Form 7', 'contact-form-7' ),
		__( 'Contact', 'contact-form-7' )
			. wpcf7_admin_menu_change_notice(),
		'wpcf7_read_contact_forms',
		'wpcf7',
		'wpcf7_admin_management_page',
		'dashicons-email',
		30
	);

	$edit = add_submenu_page( 'wpcf7',
		__( 'Edit Contact Form', 'contact-form-7' ),
		__( 'Contact Forms', 'contact-form-7' )
			. wpcf7_admin_menu_change_notice( 'wpcf7' ),
		'wpcf7_read_contact_forms',
		'wpcf7',
		'wpcf7_admin_management_page'
	);

	add_action( 'load-' . $edit, 'wpcf7_load_contact_form_admin', 10, 0 );

	$addnew = add_submenu_page( 'wpcf7',
		__( 'Add New Contact Form', 'contact-form-7' ),
		__( 'Add New', 'contact-form-7' )
			. wpcf7_admin_menu_change_notice( 'wpcf7-new' ),
		'wpcf7_edit_contact_forms',
		'wpcf7-new',
		'wpcf7_admin_add_new_page'
	);

	add_action( 'load-' . $addnew, 'wpcf7_load_contact_form_admin', 10, 0 );

	$integration = WPCF7_Integration::get_instance();

	if ( $integration->service_exists() ) {
		$integration = add_submenu_page( 'wpcf7',
			__( 'Integration with External API', 'contact-form-7' ),
			__( 'Integration', 'contact-form-7' )
				. wpcf7_admin_menu_change_notice( 'wpcf7-integration' ),
			'wpcf7_manage_integration',
			'wpcf7-integration',
			'wpcf7_admin_integration_page'
		);

		add_action( 'load-' . $integration, 'wpcf7_load_integration_page', 10, 0 );
	}
}


function wpcf7_admin_menu_change_notice( $menu_slug = '' ) {
	$counts = apply_filters( 'wpcf7_admin_menu_change_notice',
		array(
			'wpcf7' => 0,
			'wpcf7-new' => 0,
			'wpcf7-integration' => 0,
		)
	);

	if ( empty( $menu_slug ) ) {
		$count = absint( array_sum( $counts ) );
	} elseif ( isset( $counts[$menu_slug] ) ) {
		$count = absint( $counts[$menu_slug] );
	} else {
		$count = 0;
	}

	if ( $count ) {
		return sprintf(
			' <span class="update-plugins %1$d"><span class="plugin-count">%2$s</span></span>',
			$count,
			esc_html( number_format_i18n( $count ) )
		);
	}

	return '';
}


add_action(
	'admin_enqueue_scripts',
	'wpcf7_admin_enqueue_scripts',
	10, 1
);

function wpcf7_admin_enqueue_scripts( $hook_suffix ) {
	if ( false === strpos( $hook_suffix, 'wpcf7' ) ) {
		return;
	}

	wp_enqueue_style( 'contact-form-7-admin',
		wpcf7_plugin_url( 'admin/includes/css/styles.css' ),
		array(), WPCF7_VERSION, 'all'
	);

	if ( wpcf7_is_rtl() ) {
		wp_enqueue_style( 'contact-form-7-admin-rtl',
			wpcf7_plugin_url( 'admin/includes/css/styles-rtl.css' ),
			array(), WPCF7_VERSION, 'all'
		);
	}

	$assets = include(
		wpcf7_plugin_path( 'admin/includes/js/index.asset.php' )
	);

	$assets = wp_parse_args( $assets, array(
		'dependencies' => array(),
		'version' => WPCF7_VERSION,
	) );

	wp_enqueue_script( 'wpcf7-admin',
		wpcf7_plugin_url( 'admin/includes/js/index.js' ),
		$assets['dependencies'],
		$assets['version'],
		array( 'in_footer' => true )
	);

	wp_set_script_translations( 'wpcf7-admin', 'contact-form-7' );

	$wpcf7_obj = array(
		'apiSettings' => array(
			'root' => sanitize_url( rest_url( 'contact-form-7/v1' ) ),
			'namespace' => 'contact-form-7/v1',
		),
	);

	$post = wpcf7_get_current_contact_form();

	if ( $post ) {
		$wpcf7_obj = array_merge( $wpcf7_obj, array(
			'nonce' => array(
				'save' => wp_create_nonce(
					sprintf(
						'wpcf7-save-contact-form_%s',
						$post->initial() ? -1 : $post->id()
					)
				),
				'copy' => wp_create_nonce(
					sprintf(
						'wpcf7-copy-contact-form_%s',
						$post->initial() ? -1 : $post->id()
					)
				),
				'delete' => wp_create_nonce(
					sprintf(
						'wpcf7-delete-contact-form_%s',
						$post->initial() ? -1 : $post->id()
					)
				),
			),
		) );

		if (
			current_user_can( 'wpcf7_edit_contact_form', $post->id() ) and
			wpcf7_validate_configuration()
		) {
			$config_validator = new WPCF7_ConfigValidator( $post );
			$config_validator->restore();

			$wpcf7_obj = array_merge( $wpcf7_obj, array(
				'configValidator' => array(
					'errors' => $config_validator->collect_error_messages(),
					'docUrl' => WPCF7_ConfigValidator::get_doc_link(),
				),
			) );
		}
	}

	wp_add_inline_script( 'wpcf7-admin',
		sprintf(
			'var wpcf7 = %s;',
			wp_json_encode( $wpcf7_obj, JSON_PRETTY_PRINT )
		),
		'before'
	);
}


add_filter(
	'set_screen_option_wpcf7_contact_forms_per_page',
	static function ( $result, $option, $value ) {
		$wpcf7_screens = array(
			'wpcf7_contact_forms_per_page',
		);

		if ( in_array( $option, $wpcf7_screens ) ) {
			$result = $value;
		}

		return $result;
	},
	10, 3
);


function wpcf7_load_contact_form_admin() {
	global $plugin_page;

	$action = wpcf7_current_action();

	do_action( 'wpcf7_admin_load',
		trim( $_GET['page'] ?? '' ),
		$action
	);

	if ( 'save' === $action ) {
		$id = $_POST['post_ID'] ?? '-1';

		check_admin_referer( 'wpcf7-save-contact-form_' . $id );

		if ( ! current_user_can( 'wpcf7_edit_contact_form', $id ) ) {
			wp_die(
				__( "You are not allowed to edit this item.", 'contact-form-7' )
			);
		}

		$contact_form = wpcf7_save_contact_form(
			array_merge(
				$_REQUEST,
				array(
					'id' => $id,
					'title' => $_POST['post_title'] ?? null,
					'locale' => $_POST['wpcf7-locale'] ?? null,
					'form' => $_POST['wpcf7-form'] ?? '',
					'mail' => $_POST['wpcf7-mail'] ?? array(),
					'mail_2' => $_POST['wpcf7-mail-2'] ?? array(),
					'messages' => $_POST['wpcf7-messages'] ?? array(),
					'additional_settings' => $_POST['wpcf7-additional-settings'] ?? '',
				)
			)
		);

		if ( $contact_form and wpcf7_validate_configuration() ) {
			$config_validator = new WPCF7_ConfigValidator( $contact_form );
			$config_validator->validate();
			$config_validator->save();
		}

		$query = array(
			'post' => $contact_form ? $contact_form->id() : 0,
			'active-tab' => wpcf7_canonicalize_name( $_POST['active-tab'] ?? '' ),
		);

		if ( ! $contact_form ) {
			$query['message'] = 'failed';
		} elseif ( -1 == $id ) {
			$query['message'] = 'created';
		} else {
			$query['message'] = 'saved';
		}

		$redirect_to = add_query_arg( $query, menu_page_url( 'wpcf7', false ) );
		wp_safe_redirect( $redirect_to );
		exit();
	}

	if ( 'copy' === $action ) {
		$id = absint( $_POST['post_ID'] ?? $_REQUEST['post'] ?? '' );

		check_admin_referer( 'wpcf7-copy-contact-form_' . $id );

		if ( ! current_user_can( 'wpcf7_edit_contact_form', $id ) ) {
			wp_die(
				__( "You are not allowed to edit this item.", 'contact-form-7' )
			);
		}

		$query = array();

		if ( $contact_form = wpcf7_contact_form( $id ) ) {
			$new_contact_form = $contact_form->copy();
			$new_contact_form->save();

			$query['post'] = $new_contact_form->id();
			$query['message'] = 'created';
		}

		$redirect_to = add_query_arg( $query, menu_page_url( 'wpcf7', false ) );

		wp_safe_redirect( $redirect_to );
		exit();
	}

	if ( 'delete' === $action ) {
		if ( ! empty( $_POST['post_ID'] ) ) {
			check_admin_referer( 'wpcf7-delete-contact-form_' . $_POST['post_ID'] );
		} elseif ( ! is_array( $_REQUEST['post'] ) ) {
			check_admin_referer( 'wpcf7-delete-contact-form_' . $_REQUEST['post'] );
		} else {
			check_admin_referer( 'bulk-posts' );
		}

		$posts = (array) ( $_POST['post_ID'] ?? $_REQUEST['post'] ?? array() );

		$deleted = 0;

		foreach ( $posts as $post ) {
			$post = WPCF7_ContactForm::get_instance( $post );

			if ( empty( $post ) ) {
				continue;
			}

			if ( ! current_user_can( 'wpcf7_delete_contact_form', $post->id() ) ) {
				wp_die(
					__( "You are not allowed to delete this item.", 'contact-form-7' )
				);
			}

			if ( ! $post->delete() ) {
				wp_die( __( "Error in deleting.", 'contact-form-7' ) );
			}

			$deleted += 1;
		}

		$query = array();

		if ( ! empty( $deleted ) ) {
			$query['message'] = 'deleted';
		}

		$redirect_to = add_query_arg( $query, menu_page_url( 'wpcf7', false ) );

		wp_safe_redirect( $redirect_to );
		exit();
	}

	$post = null;

	if ( 'wpcf7-new' === $plugin_page ) {
		$post = WPCF7_ContactForm::get_template( array(
			'locale' => $_GET['locale'] ?? null,
		) );
	} elseif ( ! empty( $_GET['post'] ) ) {
		$post = WPCF7_ContactForm::get_instance( $_GET['post'] );
	}

	$current_screen = get_current_screen();

	$help_tabs = new WPCF7_Help_Tabs( $current_screen );

	if ( $post
	and current_user_can( 'wpcf7_edit_contact_form', $post->id() ) ) {
		$help_tabs->set_help_tabs( 'edit' );
	} else {
		$help_tabs->set_help_tabs( 'list' );

		if ( ! class_exists( 'WPCF7_Contact_Form_List_Table' ) ) {
			require_once WPCF7_PLUGIN_DIR . '/admin/includes/class-contact-forms-list-table.php';
		}

		add_filter(
			'manage_' . $current_screen->id . '_columns',
			array( 'WPCF7_Contact_Form_List_Table', 'define_columns' ),
			10, 0
		);

		add_screen_option( 'per_page', array(
			'default' => 20,
			'option' => 'wpcf7_contact_forms_per_page',
		) );
	}
}


function wpcf7_admin_management_page() {
	if ( $post = wpcf7_get_current_contact_form() ) {
		$post_id = $post->initial() ? -1 : $post->id();

		require_once WPCF7_PLUGIN_DIR . '/admin/includes/editor.php';
		require_once WPCF7_PLUGIN_DIR . '/admin/edit-contact-form.php';
		return;
	}

	if ( 'validate' == wpcf7_current_action()
	and wpcf7_validate_configuration()
	and current_user_can( 'wpcf7_edit_contact_forms' ) ) {
		wpcf7_admin_bulk_validate_page();
		return;
	}

	$list_table = new WPCF7_Contact_Form_List_Table();
	$list_table->prepare_items();

?>
<div class="wrap" id="wpcf7-contact-form-list-table">

<h1 class="wp-heading-inline"><?php
	echo esc_html( __( 'Contact Forms', 'contact-form-7' ) );
?></h1>

<?php
	if ( current_user_can( 'wpcf7_edit_contact_forms' ) ) {
		echo wpcf7_link(
			menu_page_url( 'wpcf7-new', false ),
			__( 'Add New', 'contact-form-7' ),
			array( 'class' => 'page-title-action' )
		);
	}

	if ( ! empty( $_REQUEST['s'] ) ) {
		echo sprintf(
			'<span class="subtitle">'
			/* translators: %s: search keywords */
			. __( 'Search results for &#8220;%s&#8221;', 'contact-form-7' )
			. '</span>',
			esc_html( $_REQUEST['s'] )
		);
	}
?>

<hr class="wp-header-end">

<?php
	do_action( 'wpcf7_admin_warnings',
		'wpcf7', wpcf7_current_action(), null
	);

	wpcf7_welcome_panel();

	do_action( 'wpcf7_admin_notices',
		'wpcf7', wpcf7_current_action(), null
	);
?>

<form method="get" action="">
	<input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ); ?>" />
	<?php $list_table->search_box( __( 'Search Contact Forms', 'contact-form-7' ), 'wpcf7-contact' ); ?>
	<?php $list_table->display(); ?>
</form>

</div>
<?php
}


function wpcf7_admin_add_new_page() {
	$post = wpcf7_get_current_contact_form();

	if ( ! $post ) {
		$post = WPCF7_ContactForm::get_template();
	}

	$post_id = -1;

	require_once WPCF7_PLUGIN_DIR . '/admin/includes/editor.php';
	require_once WPCF7_PLUGIN_DIR . '/admin/edit-contact-form.php';
}


function wpcf7_load_integration_page() {
	do_action( 'wpcf7_admin_load',
		trim( $_GET['page'] ?? '' ),
		wpcf7_current_action()
	);

	$integration = WPCF7_Integration::get_instance();

	if ( isset( $_REQUEST['service'] )
	and $integration->service_exists( $_REQUEST['service'] ) ) {
		$service = $integration->get_service( $_REQUEST['service'] );
		$service->load( wpcf7_current_action() );
	}

	$help_tabs = new WPCF7_Help_Tabs( get_current_screen() );
	$help_tabs->set_help_tabs( 'integration' );
}


function wpcf7_admin_integration_page() {
	$integration = WPCF7_Integration::get_instance();

	$service = isset( $_REQUEST['service'] )
		? $integration->get_service( $_REQUEST['service'] )
		: null;

?>
<div class="wrap" id="wpcf7-integration">

<h1><?php echo esc_html( __( 'Integration with External API', 'contact-form-7' ) ); ?></h1>

<p><?php
	echo sprintf(
		/* translators: %s: link labeled 'Integration with external APIs' */
		esc_html( __( "You can expand the possibilities of your contact forms by integrating them with external services. For details, see %s.", 'contact-form-7' ) ),
		wpcf7_link(
			__( 'https://contactform7.com/integration-with-external-apis/', 'contact-form-7' ),
			__( 'Integration with external APIs', 'contact-form-7' )
		)
	);
?></p>

<?php
	do_action( 'wpcf7_admin_warnings',
		'wpcf7-integration', wpcf7_current_action(), $service
	);

	do_action( 'wpcf7_admin_notices',
		'wpcf7-integration', wpcf7_current_action(), $service
	);

	if ( $service ) {
		$message = $_REQUEST['message'] ?? '';
		$service->admin_notice( $message );

		$integration->list_services( array(
			'include' => $_REQUEST['service'],
		) );
	} else {
		$integration->list_services();
	}
?>

</div>
<?php
}


add_action( 'wpcf7_admin_notices', 'wpcf7_admin_updated_message', 10, 3 );

function wpcf7_admin_updated_message( $page, $action, $object ) {
	if ( ! in_array( $page, array( 'wpcf7', 'wpcf7-new' ) ) ) {
		return;
	}

	if ( empty( $_REQUEST['message'] ) ) {
		return;
	}

	if ( 'created' === $_REQUEST['message'] ) {
		$message = __( "Contact form created.", 'contact-form-7' );
	} elseif ( 'saved' === $_REQUEST['message'] ) {
		$message = __( "Contact form saved.", 'contact-form-7' );
	} elseif ( 'deleted' === $_REQUEST['message'] ) {
		$message = __( "Contact form deleted.", 'contact-form-7' );
	}

	if ( ! empty( $message ) ) {
		wp_admin_notice( esc_html( $message ), 'type=success' );
	}

	if ( 'failed' === $_REQUEST['message'] ) {
		$message =
			__( "There was an error saving the contact form.", 'contact-form-7' );

		wp_admin_notice( esc_html( $message ), 'type=error' );
	}

	if ( 'validated' === $_REQUEST['message'] ) {
		$bulk_validate = WPCF7::get_option( 'bulk_validate', array() );
		$count_invalid = absint( $bulk_validate['count_invalid'] ?? 0 );

		if ( $count_invalid ) {
			$message = sprintf(
				_n(
					/* translators: %s: number of contact forms */
					"Configuration validation completed. %s invalid contact form was found.",
					"Configuration validation completed. %s invalid contact forms were found.",
					$count_invalid, 'contact-form-7'
				),
				number_format_i18n( $count_invalid )
			);

			wp_admin_notice( esc_html( $message ), 'type=warning' );
		} else {
			$message = __( "Configuration validation completed. No invalid contact form was found.", 'contact-form-7' );

			wp_admin_notice( esc_html( $message ), 'type=success' );
		}
	}
}


add_filter( 'plugin_action_links', 'wpcf7_plugin_action_links', 10, 2 );

function wpcf7_plugin_action_links( $links, $file ) {
	if ( $file != WPCF7_PLUGIN_BASENAME ) {
		return $links;
	}

	if ( ! current_user_can( 'wpcf7_read_contact_forms' ) ) {
		return $links;
	}

	$settings_link = wpcf7_link(
		menu_page_url( 'wpcf7', false ),
		__( 'Settings', 'contact-form-7' )
	);

	array_unshift( $links, $settings_link );

	return $links;
}


add_action( 'wpcf7_admin_warnings', 'wpcf7_old_wp_version_error', 10, 3 );

function wpcf7_old_wp_version_error( $page, $action, $object ) {
	$wp_version = get_bloginfo( 'version' );

	if ( version_compare( $wp_version, WPCF7_REQUIRED_WP_VERSION, '<' ) ) {
		$message = sprintf(
			/* translators: 1: version of Contact Form 7, 2: version of WordPress, 3: URL */
			__( '<strong>Contact Form 7 %1$s requires WordPress %2$s or higher.</strong> Please <a href="%3$s">update WordPress</a> first.', 'contact-form-7' ),
			WPCF7_VERSION,
			WPCF7_REQUIRED_WP_VERSION,
			admin_url( 'update-core.php' )
		);

		wp_admin_notice( $message, 'type=warning' );
	}
}


add_action( 'wpcf7_admin_warnings', 'wpcf7_not_allowed_to_edit', 10, 3 );

function wpcf7_not_allowed_to_edit( $page, $action, $object ) {
	if ( $object instanceof WPCF7_ContactForm ) {
		$contact_form = $object;
	} else {
		return;
	}

	if ( ! current_user_can( 'wpcf7_edit_contact_form', $contact_form->id() ) ) {
		$message = __( "You are not allowed to edit this contact form.", 'contact-form-7' );

		wp_admin_notice( esc_html( $message ), 'type=warning' );
	}
}


add_action( 'wpcf7_admin_warnings', 'wpcf7_outdated_php_warning', 10, 3 );

function wpcf7_outdated_php_warning( $page, $action, $object ) {
	if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
		$message = __( "The next major release of Contact Form 7 will discontinue support for outdated PHP versions. If you don't upgrade PHP, you will not be able to upgrade the plugin.", 'contact-form-7' );

		wp_admin_notice( esc_html( $message ), 'type=warning' );
	}
}


add_action( 'wpcf7_admin_warnings', 'wpcf7_ctct_deprecated_warning', 10, 3 );

function wpcf7_ctct_deprecated_warning( $page, $action, $object ) {
	$service = WPCF7_ConstantContact::get_instance();

	if ( $service->is_active() ) {
		$message = __( "The Constant Contact integration is deprecated. It is not recommended to continue using the feature.", 'contact-form-7' );

		wp_admin_notice( esc_html( $message ), 'type=warning' );
	}
}
