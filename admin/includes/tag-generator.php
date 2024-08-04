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
				'<button type="button" data-target="%1$s" title="%2$s">%3$s</button>',
				esc_attr( $panel['content'] ),
				esc_attr( sprintf(
					/* translators: %s: title of form-tag like 'email' or 'checkboxes' */
					__( 'Form-tag Generator: %s', 'contact-form-7' ),
					$panel['title'] )
				),
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
				echo '<button class="close-modal">close</button>';
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
