<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$integration = WPCF7_Integration::get_instance();

$service_name = wpcf7_superglobal_request( 'service' );
$service = null;

if ( $service_name and $integration->service_exists( $service_name ) ) {
	$service = $integration->get_service( $service_name );
}

$formatter = new WPCF7_HTMLFormatter( array(
	'allowed_html' => array_merge( wpcf7_kses_allowed_html(), array(
		'form' => array(
			'action' => true,
			'method' => true,
		),
	) ),
) );

$formatter->append_start_tag( 'div', array(
	'class' => 'wrap',
	'id' => 'wpcf7-integration',
) );

$formatter->append_start_tag( 'h1' );

$formatter->append_preformatted(
	esc_html( __( 'Integration with External API', 'contact-form-7' ) )
);

$formatter->end_tag( 'h1' );

$formatter->append_start_tag( 'p' );

$formatter->append_preformatted(
	sprintf(
		/* translators: %s: URL to support page about integration with external APIs */
		__( 'You can expand the possibilities of your contact forms by integrating them with external services. For details, see <a href="%s">Integration with external APIs</a>.', 'contact-form-7' ),
		'https://contactform7.com/integration-with-external-apis/'
	)
);

$formatter->end_tag( 'p' );

$formatter->call_user_func(
	static function () use ( $integration, $service, $service_name ) {
		do_action( 'wpcf7_admin_warnings',
			'wpcf7-integration', wpcf7_current_action(), $service
		);

		do_action( 'wpcf7_admin_notices',
			'wpcf7-integration', wpcf7_current_action(), $service
		);

		if ( $service ) {
			$message = wpcf7_superglobal_request( 'message' );
			$service->admin_notice( $message );

			$integration->list_services( array(
				'include' => $service_name,
			) );
		} else {
			$integration->list_services();
		}
	}
);

$formatter->print();
