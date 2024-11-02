<?php

/**
 * The base class for form-tag generators management.
 */
class WPCF7_TagGenerator {

	private static $instance;

	private $panels = array();

	private function __construct() {}


	/**
	 * Returns the singleton instance of this class.
	 */
	public static function get_instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self;
		}

		return self::$instance;
	}


	/**
	 * Adds a form-tag generator instance.
	 */
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
				/* translators: 1: version, 2: tag generator title */
				__( 'Use of tag generator instances older than version 2 is deprecated. Version %1$s instance (%2$s) detected.', 'contact-form-7' ),
				$options['version'],
				$title
			);

			wp_trigger_error( __METHOD__, $message, E_USER_DEPRECATED );
		}

		return true;
	}


	/**
	 * Renders form-tag generator calling buttons.
	 */
	public function print_buttons() {
		echo '<span id="tag-generator-list" class="hide-if-no-js">';

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


	/**
	 * Renders form-tag generator dialog panels (hidden until called).
	 */
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
					'<button %1$s>%2$s</button>',
					wpcf7_format_atts( array(
						'class' => 'close-button',
						'title' => __( 'Close this dialog box', 'contact-form-7' ),
						'data-taggen' => 'close-dialog',
					) ),
					esc_html( __( 'Close', 'contact-form-7' ) )
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


/**
 * Class helps to implement a form-tag generator content.
 */
class WPCF7_TagGeneratorGenerator {

	private $key = '';


	/**
	 * The constructor.
	 */
	public function __construct( $key ) {
		$this->key = $key;
	}


	/**
	 * Returns a unique reference ID.
	 */
	public function ref( $suffix = '' ) {
		$ref = sprintf( '%s-%s', $this->key, $suffix );
		$ref = strtolower( $ref );
		$ref = preg_replace( '/[^0-9a-z-]/', '', $ref );
		$ref = preg_replace( '/[-]+/', '-', $ref );
		$ref = trim( $ref, '-' );
		return $ref;
	}


	/**
	 * Calls one of the template methods.
	 */
	public function print( $part, $options = '' ) {
		if ( is_callable( array( $this, $part ) ) ) {
			call_user_func( array( $this, $part ), $options );
		}
	}


	/**
	 * Template method for field type field.
	 */
	private function field_type( $options = '' ) {
		$options = wp_parse_args( $options, array(
			'with_required' => false,
			'select_options' => array(),
		) );

?>
<fieldset>
	<legend id="<?php echo esc_attr( $this->ref( 'type-legend' ) ); ?>"><?php
		echo esc_html( __( 'Field type', 'contact-form-7' ) );
	?></legend>

	<select data-tag-part="basetype" aria-labelledby="<?php echo esc_attr( $this->ref( 'type-legend' ) ); ?>"><?php
		foreach ( (array) $options['select_options'] as $basetype => $title ) {
			echo sprintf(
				'<option %1$s>%2$s</option>',
				wpcf7_format_atts( array(
					'value' => $basetype,
				) ),
				esc_html( $title )
			);
		}
	?></select>

	<?php if ( $options['with_required'] ) { ?>
	<br />
	<label>
		<input type="checkbox" data-tag-part="type-suffix" value="*" />
		<?php echo esc_html( __( "This is a required field.", 'contact-form-7' ) ); ?>
	</label>
	<?php } ?>
</fieldset>
<?php
	}


	/**
	 * Template method for field name field.
	 */
	private function field_name( $options = '' ) {
		$options = wp_parse_args( $options, array(
			'ask_if' => '',
		) );

?>
<fieldset>
	<legend id="<?php echo esc_attr( $this->ref( 'name-legend' ) ); ?>"><?php
		echo esc_html( __( 'Field name', 'contact-form-7' ) );
	?></legend>
	<input type="text" data-tag-part="name" pattern="[A-Za-z][A-Za-z0-9_\-]*" aria-labelledby="<?php echo esc_attr( $this->ref( 'name-legend' ) ); ?>" />

<?php
		$tag_option = $label = '';

		if ( 'author_name' === $options['ask_if'] ) {
			$tag_option = 'autocomplete:name';

			if ( wpcf7_akismet_is_available() ) {
				$tag_option .= ' akismet:author';
			}

			$label = __( "This field expects the submitter name.", 'contact-form-7' );
		} elseif ( 'author_email' === $options['ask_if'] ) {
			$tag_option = 'autocomplete:email';

			if ( wpcf7_akismet_is_available() ) {
				$tag_option .= ' akismet:author_email';
			}

			$label = __( "This field expects the submitter email.", 'contact-form-7' );
		} elseif ( 'author_url' === $options['ask_if'] ) {
			$tag_option = 'autocomplete:url';

			if ( wpcf7_akismet_is_available() ) {
				$tag_option .= ' akismet:author_url';
			}

			$label = __( "This field expects the submitter URL.", 'contact-form-7' );
		} elseif ( 'author_tel' === $options['ask_if'] ) {
			$tag_option = 'autocomplete:tel';
			$label = __( "This field expects the submitter telephone number.", 'contact-form-7' );
		}

		if ( $tag_option ) {
?>
	<br />
	<label>
		<input type="checkbox" data-tag-part="option" data-tag-option="<?php echo esc_attr( $tag_option ); ?>" />
		<?php echo esc_html( $label ); ?>
	</label>
<?php
		}
?>
</fieldset>
<?php
	}


	/**
	 * Template method for ID attribute option field.
	 */
	private function id_attr( $options = '' ) {
?>
<fieldset>
	<legend id="<?php echo esc_attr( $this->ref( 'id-legend' ) ); ?>"><?php
		echo esc_html( __( 'ID attribute', 'contact-form-7' ) );
	?></legend>
	<input type="text" data-tag-part="option" data-tag-option="id:" pattern="[A-Za-z][A-Za-z0-9_\-]*" aria-labelledby="<?php echo esc_attr( $this->ref( 'id-legend' ) ); ?>" />
</fieldset>
<?php
	}


	/**
	 * Template method for class attribute option field.
	 */
	private function class_attr( $options = '' ) {
?>
<fieldset>
	<legend id="<?php echo esc_attr( $this->ref( 'class-legend' ) ); ?>"><?php
		echo esc_html( __( 'Class attribute', 'contact-form-7' ) );
	?></legend>
	<input type="text" data-tag-part="option" data-tag-option="class:" pattern="[A-Za-z0-9_\-\s]*" aria-labelledby="<?php echo esc_attr( $this->ref( 'class-legend' ) ); ?>" />
</fieldset>
<?php
	}


	/**
	 * Template method for min/max options.
	 */
	private function min_max( $options = '' ) {
		$options = wp_parse_args( $options, array(
			'type' => 'number',
			'title' => __( 'Length', 'contact-form-7' ),
			'min_option' => 'minlength:',
			'max_option' => 'maxlength:',
			'accept_minus' => false,
		) );

?>
<fieldset>
	<legend><?php
		echo esc_html( $options['title'] );
	?></legend>
	<label><?php
		echo esc_html( __( 'Min', 'contact-form-7' ) ) . "\n";

		echo sprintf(
			'<input %s />',
			wpcf7_format_atts( array(
				'type' => $options['type'],
				'data-tag-part' => 'option',
				'data-tag-option' => $options['min_option'],
				'min' => $options['accept_minus'] ? null : 0,
			) )
		);
	?></label>
	&#8660;
	<label><?php
		echo esc_html( __( 'Max', 'contact-form-7' ) ) . "\n";

		echo sprintf(
			'<input %s />',
			wpcf7_format_atts( array(
				'type' => $options['type'],
				'data-tag-part' => 'option',
				'data-tag-option' => $options['max_option'],
				'min' => $options['accept_minus'] ? null : 0,
			) )
		);
	?></label>
</fieldset>
<?php
	}


	/**
	 * Template method for default value field.
	 */
	private function default_value( $options = '' ) {
		$options = wp_parse_args( $options, array(
			'type' => 'text',
			'title' => __( 'Default value', 'contact-form-7' ),
			'with_placeholder' => false,
			'use_content' => false,
		) );

?>
<fieldset>
	<legend id="<?php echo esc_attr( $this->ref( 'value-legend' ) ); ?>"><?php
		echo esc_html( $options['title'] );
	?></legend>
	<?php
		echo sprintf(
			'<input %s />',
			wpcf7_format_atts( array(
				'type' => $options['type'],
				'data-tag-part' => $options['use_content'] ? 'content' : 'value',
				'aria-labelledby' => $this->ref( 'value-legend' ),
			) )
		);
	?>

	<?php if ( $options['with_placeholder'] ) { ?>
	<br />
	<label>
		<input type="checkbox" data-tag-part="option" data-tag-option="placeholder" /> <?php echo esc_html( __( "Use this text as the placeholder.", 'contact-form-7' ) ); ?>
	</label>
	<?php } ?>

</fieldset>
<?php
	}


	/**
	 * Template method for selectable values useful for checkboxes or a menu.
	 */
	private function selectable_values( $options = '' ) {
		$options = wp_parse_args( $options, array(
			'first_as_label' => false,
			'use_label_element' => false,
		) );

?>
<fieldset>
	<legend id="<?php echo esc_attr( $this->ref( 'selectable-values-legend' ) ); ?>"><?php
		echo esc_html( __( 'Selectable values', 'contact-form-7' ) );
	?></legend>
	<?php
		echo sprintf(
			'<span %1$s>%2$s</span>',
			wpcf7_format_atts( array(
				'id' => $this->ref( 'selectable-values-description' ),
			) ),
			esc_html( __( "One item per line.", 'contact-form-7' ) )
		);
	?>
	<br />
	<?php
		echo sprintf(
			'<textarea %1$s>%2$s</textarea>',
			wpcf7_format_atts( array(
				'required' => true,
				'data-tag-part' => 'value',
				'aria-labelledby' => $this->ref( 'selectable-values-legend' ),
				'aria-describedby' => $this->ref( 'selectable-values-description' ),
			) ),
			esc_html( __( "Option 1\nOption 2\nOption 3", 'contact-form-7' ) )
		);
	?>

	<?php if ( $options['first_as_label'] ) { ?>
	<br />
	<?php
		echo sprintf(
			'<label><input %1$s /> %2$s</label>',
			wpcf7_format_atts( array(
				'type' => 'checkbox',
				'checked' => 'checked' === $options['first_as_label'],
				'data-tag-part' => 'option',
				'data-tag-option' => 'first_as_label',
			) ),
			esc_html( __( "Use the first item as a label.", 'contact-form-7' ) )
		);
	?>
	<?php } ?>

	<?php if ( $options['use_label_element'] ) { ?>
	<br />
	<?php
		echo sprintf(
			'<label><input %1$s /> %2$s</label>',
			wpcf7_format_atts( array(
				'type' => 'checkbox',
				'checked' => 'checked' === $options['use_label_element'],
				'data-tag-part' => 'option',
				'data-tag-option' => 'use_label_element',
			) ),
			esc_html( __( "Wrap each item with a label element.", 'contact-form-7' ) )
		);
	?>
	<?php } ?>

</fieldset>
<?php
	}


	/**
	 * Template method for insert-box content including the result form-tag.
	 */
	private function insert_box_content( $options = '' ) {
?>
<div class="flex-container">
	<?php
		echo sprintf(
			'<input %s />',
			wpcf7_format_atts( array(
				'type' => 'text',
				'class' => 'code',
				'readonly' => true,
				'onfocus' => 'this.select();',
				'data-tag-part' => 'tag',
				'aria-label' => __( "The form-tag to be inserted into the form template", 'contact-form-7' ),
			) )
		);
	?>
	<button type="button" class="button button-primary" data-taggen="insert-tag"><?php echo esc_html( __( 'Insert Tag', 'contact-form-7' ) ); ?></button>
</div>
<?php
	}


	/**
	 * Template method for a tip message about mail-tag.
	 */
	private function mail_tag_tip( $options = '' ) {
		$tip = sprintf(
			/* translators: %s: mail-tag corresponding to the form-tag */
			esc_html( __( 'To use the user input in the email, insert the corresponding mail-tag %s into the email template.', 'contact-form-7' ) ),
			'<strong data-tag-part="mail-tag"></strong>'
		);

?>
<p class="mail-tag-tip"><?php echo $tip; ?></p>
<?php
	}

}
