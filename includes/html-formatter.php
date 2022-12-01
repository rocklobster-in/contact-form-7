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

	/**
	 * HTML elements that can contain phrasing content.
	 */
	const br_parent_elements = array(
		'a', 'abbr', 'address', 'article', 'aside', 'audio', 'b', 'bdi',
		'bdo', 'blockquote', 'button', 'canvas', 'caption', 'cite', 'code',
		'data', 'datalist', 'dd', 'del', 'details', 'dfn', 'dialog', 'div',
		'dt', 'em', 'fieldset', 'figcaption', 'figure', 'footer', 'form',
		'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'header', 'i', 'ins', 'kbd',
		'label', 'legend', 'li', 'main', 'map', 'mark', 'meter', 'nav',
		'noscript', 'object', 'output', 'p', 'pre', 'progress', 'q', 'rt',
		'ruby', 's', 'samp', 'section', 'slot', 'small', 'span', 'strong',
		'sub', 'summary', 'sup', 'td', 'template', 'th', 'time', 'u', 'var',
		'video',
	);

	private $input = '';
	private $output = '';
	private $stacked_elements = array();
	private $options = array();

	public function __construct( string $input, $args = '' ) {
		$this->input = $input;

		$this->options = wp_parse_args( $args, array(
			'auto_br' => true,
			'auto_indent' => true,
		) );
	}

	public function pre_format( WPCF7_HTMLIterator $iterator ) {
		$position = 0;

		$calc_position_cb = function ( $chunk ) {
			return $chunk['position'] + strlen( $chunk['content'] );
		};

		$text_left = null;

		foreach ( $iterator->iterate() as $chunk ) {
			$chunk['position'] = $position;

			// Standardize newline characters to "\n".
			$chunk['content'] = str_replace(
				array( "\r\n", "\r" ), "\n", $chunk['content']
			);

			if ( $chunk['type'] === WPCF7_HTMLIterator::opening_tag ) {
				$chunk['content'] = self::normalize_void_element( $chunk['content'] );

				// Replace <br /> by a line break.
				if ( $this->options['auto_br']
				and preg_match( '/^<br\s*\/?>$/i', $chunk['content'] ) ) {
					$chunk['type'] = WPCF7_HTMLIterator::text;
					$chunk['content'] = "\n";
				}
			}

			if ( $chunk['type'] === WPCF7_HTMLIterator::text ) {
				// Concatenate neighboring texts.
				if ( isset( $text_left ) ) {
					$text_left['content'] .= $chunk['content'];
				} else {
					$text_left = $chunk;
				}

				continue;
			}

			if ( isset( $text_left ) ) {
				yield $text_left;
				$chunk['position'] = call_user_func( $calc_position_cb, $text_left );
				$text_left = null;
			}

			yield $chunk;
			$position = call_user_func( $calc_position_cb, $chunk );
		}

		if ( isset( $text_left ) ) {
			yield $text_left;
		}
	}

	public function format() {
		$this->output = '';
		$this->stacked_elements = array();

		$iterator = new WPCF7_HTMLIterator( $this->input );

		foreach ( $this->pre_format( $iterator ) as $chunk ) {

			if ( $chunk['type'] === WPCF7_HTMLIterator::text ) {
				$this->append_text( $chunk['content'] );
			}

			if ( $chunk['type'] === WPCF7_HTMLIterator::opening_tag ) {
				$this->append_opening_tag( $chunk['content'] );
			}

			if ( $chunk['type'] === WPCF7_HTMLIterator::closing_tag ) {
				$this->append_closing_tag( $chunk['content'] );
			}

			if ( $chunk['type'] === WPCF7_HTMLIterator::comment ) {
				$this->append_comment( $chunk['content'] );
			}
		}

		// Close all remaining tags.
		if ( $this->stacked_elements ) {
			$this->append_closing_tag( end( $this->stacked_elements ) );
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
			if ( $this->options['auto_br']
			and $this->has_parent( self::br_parent_elements ) ) {
				$content = self::auto_br( $content );
			} else {
				$content = preg_replace( '/\s*\n\s*/', "\n", $content );
			}

			$this->output .= $content;

		} else {
			// Split up the contents into paragraphs, separated by double line breaks.
			$paragraphs = preg_split( '/\n\s*\n/', $content );

			foreach ( $paragraphs as $paragraph ) {
				$this->append_opening_tag( 'p' );

				$paragraph = trim( $paragraph );

				if ( $this->options['auto_br'] ) {
					$paragraph = self::auto_br( $paragraph );
				} else {
					$paragraph = preg_replace( '/\s*\n\s*/', "\n", $paragraph );
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

		if ( ! in_array( $tag_name, self::void_elements ) ) {
			array_unshift( $this->stacked_elements, $tag_name );
		}

		if ( ! in_array( $tag_name, self::p_child_elements ) ) {
			$this->output = rtrim( $this->output ) . "\n";

			if ( $this->options['auto_indent'] ) {
				$this->output .= self::indent( count( $this->stacked_elements ) - 1 );
			}
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

				if ( ! in_array( $element, self::p_child_elements ) ) {
					$this->output = rtrim( $this->output ) . "\n";

					if ( $this->options['auto_indent'] ) {
						$this->output .= self::indent( count( $this->stacked_elements ) );
					}
				}

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

	public function is_inside( $tag_names ) {
		$tag_names = (array) $tag_names;

		foreach ( $this->stacked_elements as $element ) {
			if ( in_array( $element, $tag_names ) ) {
				return true;
			}
		}

		return false;
	}

	public function has_parent( $tag_names ) {
		$tag_names = (array) $tag_names;

		$parent = reset( $this->stacked_elements );

		if ( false === $parent ) {
			return false;
		}

		return in_array( $parent, $tag_names );
	}

	public static function auto_br( $text ) {
		return preg_replace( '/\s*\n\s*/', '<br />', $text );
	}

	public static function indent( $level ) {
		$level = (int) $level;

		if ( 0 < $level ) {
			return str_repeat( "\t", $level );
		}

		return '';
	}

	public static function normalize_void_element( $tag ) {
		if ( preg_match( '/<(.+?)[\s\/>]/', $tag, $matches ) ) {
			$tag_name = strtolower( $matches[1] );
		} else {
			$tag_name = strtolower( $tag );
			$tag = sprintf( '<%s>', $tag_name );
		}

		if ( in_array( $tag_name, self::void_elements ) ) {
			$tag = preg_replace( '/\s*\/?>/', ' />', $tag );
		}

		return $tag;
	}

}
