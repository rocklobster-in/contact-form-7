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

	public function add( $id, $title, $callback, $options = '' ) {
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

		if ( version_compare( $options['version'], '2', '<' ) ) {
			$message = sprintf(
				__( 'Use of tag generator instances older than version 2 is deprecated. Version %1$s instance (%2$s) detected.', 'contact-form-7' ),
				$options['version'],
				$title
			);

			wp_trigger_error( __METHOD__, $message, E_USER_DEPRECATED );
		}

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


class WPCF7_TagGeneratorGenerator {

	private $key = '';
	private $basetype = '';
	private $options = array();

	public function __construct( $key, $basetype, $options = '' ) {
		$this->key = $key;
		$this->basetype = $basetype;

		$this->options = wp_parse_args( $options, array(
			'title' => ucfirst( $this->basetype ),
		) );
	}

	public function print( $part, $options = '' ) {
		if ( is_callable( array( $this, $part ) ) ) {
			call_user_func( array( $this, $part ), $options );
		}
	}

	private function field_type( $options = '' ) {
		$options = wp_parse_args( $options, array(
			'with_required' => true,
		) );

?>
<fieldset>
	<legend><?php
		echo esc_html( __( 'Field type', 'contact-form-7' ) );
	?></legend>
	<?php echo esc_html( $this->options['title'] ?? '' ); ?>
	<br />
	<input type="hidden" data-tag-part="basetype" value="<?php echo esc_attr( $this->basetype ); ?>" />
	<?php if ( $options['with_required'] ) { ?>
	<label>
		<input type="checkbox" data-tag-part="type-suffix" value="*" />
		<?php echo esc_html( __( "This is a required field.", 'contact-form-7' ) ); ?>
	</label>
	<?php } ?>
</fieldset>
<?php
	}

	private function field_name( $options = '' ) {
		$id = sprintf( '%s-name-legend', $this->key );

?>
<fieldset>
	<legend id="<?php echo esc_attr( $id ); ?>"><?php
		echo esc_html( __( 'Field name', 'contact-form-7' ) );
	?></legend>
	<input type="text" data-tag-part="name" pattern="[A-Za-z][A-Za-z0-9_\-]*" aria-labelledby="<?php echo esc_attr( $id ); ?>" />
</fieldset>
<?php
	}

	private function default_value( $options = '' ) {
		$id = sprintf( '%s-value-legend', $this->key );

?>
<fieldset>
	<legend id="<?php echo esc_attr( $id ); ?>"><?php
		echo esc_html( __( 'Default value', 'contact-form-7' ) );
	?></legend>
	<input type="text" data-tag-part="value" aria-labelledby="<?php echo esc_attr( $id ); ?>" />
	<br />
	<label>
		<input type="checkbox" data-tag-part="option" data-tag-option="placeholder" /> <?php echo esc_html( __( "Use this text as the placeholder.", 'contact-form-7' ) ); ?>
	</label>
</fieldset>
<?php
	}

	private function id_attr( $options = '' ) {
		$id = sprintf( '%s-id-legend', $this->key );

?>
<fieldset>
	<legend id="<?php echo esc_attr( $id ); ?>"><?php
		echo esc_html( __( 'ID attribute', 'contact-form-7' ) );
	?></legend>
	<input type="text" data-tag-part="option" data-tag-option="id:" pattern="[A-Za-z][A-Za-z0-9_\-]*" aria-labelledby="<?php echo esc_attr( $id ); ?>" />
</fieldset>
<?php
	}

	private function class_attr( $options = '' ) {
		$id = sprintf( '%s-class-legend', $this->key );

?>
<fieldset>
	<legend id="<?php echo esc_attr( $id ); ?>"><?php
		echo esc_html( __( 'Class attribute', 'contact-form-7' ) );
	?></legend>
	<input type="text" data-tag-part="option" data-tag-option="class:" pattern="[A-Za-z0-9_\-\s]*" aria-labelledby="<?php echo esc_attr( $id ); ?>" />
</fieldset>
<?php
	}

	private function insert_box_content( $options = '' ) {
?>
<div class="flex-container">
	<input type="text" class="code" readonly="readonly" onfocus="this.select();" data-tag-part="tag" />
	<button type="button" class="button button-primary" data-taggen="insert-tag"><?php echo esc_html( __( 'Insert Tag', 'contact-form-7' ) ); ?></button>
</div>
<?php
	}
}
