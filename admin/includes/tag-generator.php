<?php

class WPCF7_TagGenerator {

	private static $instance;

	private $panels = array();

	private function __construct() {}

	public static function get_instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function add( $id, $title, $callback, $options = array() ) {
		$id = trim( $id );

		if (
			'' === $id or
			! wpcf7_is_name( $id ) or
			! is_callable( $callback )
		) {
			return false;
		}

		$options = wp_parse_args( $options, array(
			'version' => '1',
		) );

		$this->panels[$id] = array(
			'title' => $title,
			'content' => 'tag-generator-panel-' . $id,
			'options' => $options,
			'callback' => $callback,
		);

		return true;
	}

	public function print_buttons() {
		echo '<span id="tag-generator-list">';

		foreach ( (array) $this->panels as $panel ) {
			echo sprintf(
				'<button %1$s>%2$s</button>',
				wpcf7_format_atts( array(
					'type' => 'button',
					'data-taggen' => 'open-dialog',
					'data-target' => $panel['content'],
					'title' => sprintf(
						/* translators: %s: title of form-tag */
						__( 'Form-tag Generator: %s', 'contact-form-7' ),
						$panel['title']
					),
				) ),
				esc_html( $panel['title'] )
			);
		}

		echo '</span>';
	}

	public function print_panels( WPCF7_ContactForm $contact_form ) {
		foreach ( (array) $this->panels as $id => $panel ) {
			$callback = $panel['callback'];

			$options = array_merge( $panel['options'], array(
				'id' => $id,
				'title' => $panel['title'],
				'content' => $panel['content'],
			) );

			if ( is_callable( $callback ) ) {
				echo "\n";
				echo sprintf(
					'<dialog id="%s" class="tag-generator-dialog">',
					esc_attr( $options['content'] )
				);
				echo "\n";
				echo sprintf(
					'<form %s>',
					wpcf7_format_atts( array(
						'method' => 'dialog',
						'class' => 'tag-generator-panel',
						'data-id' => $options['id'],
						'data-version' => $options['version'],
					) )
				);
				echo "\n";
				echo sprintf(
					'<div class="close-button-container"><button data-taggen="close-dialog">%s</button></div>',
					esc_html( __( 'Close', 'contact-form-7' ) )
				);
				echo "\n";
				call_user_func( $callback, $contact_form, $options );
				echo "\n";
				echo '</form>';
				echo "\n";
				echo '</dialog>';
				echo "\n\n";
			}
		}
	}

}
