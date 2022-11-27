<?php

require_once WPCF7_PLUGIN_DIR . '/includes/html-iterator.php';

class WPCF7_HTMLFormatter {

	/**
	 * The void elements in HTML.
	 *
	 * @link https://developer.mozilla.org/en-US/docs/Glossary/Void_element
	 */
	const void_elements = array(
		'area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input',
		'keygen', 'link', 'meta', 'param', 'source', 'track', 'wbr',
	);

	/**
	 * HTML elements that can contain flow content.
	 */
	const p_parent_elements = array(
		'address', 'article', 'aside', 'blockquote', 'caption', 'dd',
		'details', 'dialog', 'div', 'dt', 'fieldset', 'figcaption', 'figure',
		'footer', 'form', 'header', 'li', 'main', 'nav', 'section', 'td', 'th',
	);

	/**
	 * HTML elements in the phrasing content category.
	 */
	const p_child_elements = array(
		'a', 'abbr', 'area', 'audio', 'b', 'bdi', 'bdo', 'br', 'button',
		'canvas', 'cite', 'code', 'data', 'datalist', 'del', 'dfn',
		'em', 'embed', 'i', 'iframe', 'img', 'input', 'ins', 'kbd',
		'keygen', 'label', 'link', 'map', 'mark', 'math', 'meta',
		'meter', 'noscript', 'object', 'output', 'picture', 'progress',
		'q', 'ruby', 's', 'samp', 'script', 'select', 'small', 'span',
		'strong', 'sub', 'sup', 'svg', 'textarea', 'time', 'u', 'var',
		'video', 'wbr',
	);

	private $input = '';
	private $output = '';
	private $stacked_elements = array();
	private $options = array();

	public function __construct( string $input, $args = '' ) {
		$this->input = $input;

		$this->options = wp_parse_args( $args, array(
			'auto_br' => true,
		) );
	}

	public function format() {
		$iterator = new WPCF7_HTMLIterator( $this->input );

		foreach ( $iterator->iterate() as $chunk ) {
			$position = $chunk['position'];
			$type = $chunk['type'];
			$content = $chunk['content'];

			// Standardize newline characters to "\n".
			$content = str_replace( array( "\r\n", "\r" ), "\n", $content );

			if ( $type === WPCF7_HTMLIterator::text ) {
				$this->append_text( $content );
			}

			if ( $type === WPCF7_HTMLIterator::opening_tag ) {
				$this->append_opening_tag( $content );
			}

			if ( $type === WPCF7_HTMLIterator::closing_tag ) {
				$this->append_closing_tag( $content );
			}

			if ( $type === WPCF7_HTMLIterator::comment ) {
				$this->append_comment( $content );
			}
		}

		// Close all remaining tags.
		while ( $element = array_shift( $this->stacked_elements ) ) {
			$this->output .= "\n" . sprintf( '</%s>', $element );
		}

		return $this->output;
	}

	public function append_text( $content ) {
		// Inside <pre>
		if ( $this->is_inside( 'pre' ) ) {
			$this->output .= $content;
			return;
		}

		if ( $this->is_inside( self::p_child_elements ) ) {
			if ( $this->options['auto_br'] ) {
				$content = preg_replace( '/\s*\n\s*/', '<br />', $content );
			}

			$this->output .= $content;

		} else {
			// Open <p> if it does not exist.
			if ( ! $this->is_inside( 'p' ) ) {
				$this->append_opening_tag( 'p' );
			}

			// Split up the contents into paragraphs, separated by double line breaks.
			$paragraphs = preg_split( '/\n\s*\n/', $content );

			foreach ( $paragraphs as $paragraph ) {
				$this->append_opening_tag( 'p' );

				$paragraph = trim( $paragraph );

				if ( $this->options['auto_br'] ) {
					$paragraph = preg_replace( '/\s*\n\s*/', '<br />', $paragraph );
				}

				$this->output .= $paragraph;
			}

			if ( preg_match( '/\n\s*\n$/', $content ) ) {
				$this->append_closing_tag( 'p' );
			}
		}
	}

	public function append_opening_tag( $tag ) {
		if ( preg_match( '/<(.+?)[\s\/>]/', $tag, $matches ) ) {
			$tag_name = strtolower( $matches[1] );
		} else {
			$tag_name = strtolower( $tag );
			$tag = sprintf( '<%s>', $tag_name );
		}

		if ( in_array( $tag_name, self::p_child_elements ) ) {
			// Open <p> if it does not exist.
			if ( ! $this->is_inside( 'p' ) ) {
				$this->append_opening_tag( 'p' );
			}
		} else {
			// Close <p> if it exists.
			$this->append_closing_tag( 'p' );
		}

		if ( 'dd' === $tag_name or 'dt' === $tag_name ) {
			// Close <dd> and <dt> if closing tag is omitted.
			$this->append_closing_tag( 'dd' );
			$this->append_closing_tag( 'dt' );
		}

		if ( 'li' === $tag_name ) {
			// Close <li> if closing tag is omitted.
			$this->append_closing_tag( 'li' );
		}

		if ( in_array( $tag_name, self::void_elements ) ) {
			// Normalize void element.
			$tag = preg_replace( '/\s*\/?>/', ' />', $tag );
		} else {
			array_unshift( $this->stacked_elements, $tag_name );
		}

		$this->output .= $tag;
	}

	public function append_closing_tag( $tag ) {
		if ( preg_match( '/<\/(.+?)(?:\s|>)/', $tag, $matches ) ) {
			$tag_name = strtolower( $matches[1] );
		} else {
			$tag_name = strtolower( $tag );
		}

		if ( $this->is_inside( $tag_name ) ) {
			while ( $element = array_shift( $this->stacked_elements ) ) {

				$this->output .= sprintf( '</%s>', $element );

				// Remove trailing <p></p>.
				$this->output = preg_replace(
					'/<p>\s*<\/p>$/', '', $this->output, 1, $count
				);

				if ( $count or $element === $tag_name ) {
					break;
				}
			}
		}
	}

	public function append_comment( $tag ) {
		$this->output .= $tag;
	}

	public function is_inside( $tag_name ) {
		$tag_names = (array) $tag_name;

		foreach ( $this->stacked_elements as $element ) {
			if ( in_array( $element, $tag_names ) ) {
				return true;
			}
		}

		return false;
	}

}
