<?php

/**
 * Contact Form 7's class used for formatting HTML fragments.
 */
class WPCF7_HTMLFormatter {

	// HTML component types.
	const text = 0;
	const start_tag = 1;
	const end_tag = 2;
	const comment = 3;

	/**
	 * Tag name reserved for a custom HTML element used as a block placeholder.
	 */
	const placeholder_block = 'placeholder:block';

	/**
	 * Tag name reserved for a custom HTML element used as an inline placeholder.
	 */
	const placeholder_inline = 'placeholder:inline';

	/**
	 * The void elements in HTML.
	 *
	 * @link https://developer.mozilla.org/en-US/docs/Glossary/Void_element
	 */
	const void_elements = array(
		'area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input',
		'keygen', 'link', 'meta', 'param', 'source', 'track', 'wbr',
		self::placeholder_block, self::placeholder_inline,
	);

	/**
	 * HTML elements that can contain flow content.
	 */
	const p_parent_elements = array(
		'address', 'article', 'aside', 'blockquote', 'body', 'caption',
		'dd', 'details', 'dialog', 'div', 'dt', 'fieldset', 'figcaption',
		'figure', 'footer', 'form', 'header', 'li', 'main', 'nav',
		'section', 'td', 'th',
	);

	/**
	 * HTML elements that can be neither the parent nor a child of
	 * a paragraph element.
	 */
	const p_nonparent_elements = array(
		'colgroup', 'dl', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'head',
		'hgroup', 'html', 'legend', 'menu', 'ol', 'pre', 'style', 'summary',
		'table', 'tbody', 'template', 'tfoot', 'thead', 'title', 'tr', 'ul',
	);

	/**
	 * HTML elements in the phrasing content category, plus non-phrasing
	 * content elements that can be grandchildren of a paragraph element.
	 */
	const p_child_elements = array(
		'a', 'abbr', 'area', 'audio', 'b', 'bdi', 'bdo', 'br', 'button',
		'canvas', 'cite', 'code', 'data', 'datalist', 'del', 'dfn',
		'em', 'embed', 'i', 'iframe', 'img', 'input', 'ins', 'kbd',
		'keygen', 'label', 'link', 'map', 'mark', 'meta',
		'meter', 'noscript', 'object', 'output', 'picture', 'progress',
		'q', 'ruby', 's', 'samp', 'script', 'select', 'slot', 'small',
		'span', 'strong', 'sub', 'sup', 'textarea',
		'time', 'u', 'var', 'video', 'wbr',
		'optgroup', 'option', 'rp', 'rt', // non-phrasing grandchildren
		self::placeholder_inline,
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
		'noscript', 'object', 'output', 'p', 'progress', 'q', 'rt',
		'ruby', 's', 'samp', 'section', 'slot', 'small', 'span', 'strong',
		'sub', 'summary', 'sup', 'td', 'th', 'time', 'u', 'var',
		'video',
	);


	// Properties.
	private $options = array();
	private $stacked_elements = array();
	private $output = '';


	/**
	 * Constructor.
	 */
	public function __construct( $options = '' ) {
		$this->options = wp_parse_args( $options, array(
			'auto_br' => true,
			'auto_indent' => true,
		) );
	}


	/**
	 * Separates the given text into chunks of HTML. Each chunk must be an
	 * associative array that includes 'position', 'type', and 'content' keys.
	 *
	 * @param string $input Text to be separated into chunks.
	 * @return iterable Iterable of chunks.
	 */
	public function separate_into_chunks( $input ) {
		$input_bytelength = strlen( $input );
		$position = 0;

		while ( $position < $input_bytelength ) {
			$next_tag = preg_match(
				'/(?:<!--.*?-->|<(?:\/?)[a-z].*?>)/is',
				$input,
				$matches,
				PREG_OFFSET_CAPTURE,
				$position
			);

			if ( ! $next_tag ) {
				yield array(
					'position' => $position,
					'type' => self::text,
					'content' => substr( $input, $position ),
				);

				break;
			}

			$next_tag = $matches[0][0];
			$next_tag_position = $matches[0][1];

			if ( $position < $next_tag_position ) {
				yield array(
					'position' => $position,
					'type' => self::text,
					'content' => substr(
						$input,
						$position,
						$next_tag_position - $position
					),
				);
			}

			if ( '<!' === substr( $next_tag, 0, 2 ) ) {
				$next_tag_type = self::comment;
			} elseif ( '</' === substr( $next_tag, 0, 2 ) ) {
				$next_tag_type = self::end_tag;
			} else {
				$next_tag_type = self::start_tag;
			}

			yield array(
				'position' => $next_tag_position,
				'type' => $next_tag_type,
				'content' => substr(
					$input,
					$next_tag_position,
					strlen( $next_tag )
				),
			);

			$position = $next_tag_position + strlen( $next_tag );
		}
	}


	/**
	 * Normalizes content in each chunk. This may change the type and position
	 * of the chunk.
	 *
	 * @param iterable $chunks The original chunks.
	 * @return iterable Normalized chunks.
	 */
	public function pre_format( $chunks ) {
		$position = 0;

		foreach ( $chunks as $chunk ) {
			$chunk['position'] = $position;

			// Standardize newline characters to "\n".
			$chunk['content'] = str_replace(
				array( "\r\n", "\r" ), "\n", $chunk['content']
			);

			if ( $chunk['type'] === self::start_tag ) {
				list( $chunk['content'] ) =
					self::normalize_start_tag( $chunk['content'] );

				// Replace <br /> by a line break.
				if (
					$this->options['auto_br'] and
					preg_match( '/^<br\s*\/?>$/i', $chunk['content'] )
				) {
					$chunk['type'] = self::text;
					$chunk['content'] = "\n";
				}
			}

			yield $chunk;
			$position = self::calc_next_position( $chunk );
		}
	}


	/**
	 * Concatenates neighboring text chunks to create a single chunk.
	 *
	 * @param iterable $chunks The original chunks.
	 * @return iterable Processed chunks.
	 */
	public function concatenate_texts( $chunks ) {
		$position = 0;
		$text_left = null;

		foreach ( $chunks as $chunk ) {
			$chunk['position'] = $position;

			if ( $chunk['type'] === self::text ) {
				if ( isset( $text_left ) ) {
					$text_left['content'] .= $chunk['content'];
				} else {
					$text_left = $chunk;
				}

				continue;
			}

			if ( isset( $text_left ) ) {
				yield $text_left;
				$chunk['position'] = self::calc_next_position( $text_left );
				$text_left = null;
			}

			yield $chunk;
			$position = self::calc_next_position( $chunk );
		}

		if ( isset( $text_left ) ) {
			yield $text_left;
		}
	}


	/**
	 * Outputs formatted HTML based on the given chunks.
	 *
	 * @param iterable $chunks The original chunks.
	 * @return string Formatted HTML.
	 */
	public function format( $chunks ) {
		$chunks = $this->pre_format( $chunks );
		$chunks = $this->concatenate_texts( $chunks );

		$this->output = '';
		$this->stacked_elements = array();

		foreach ( $chunks as $chunk ) {

			if ( $chunk['type'] === self::text ) {
				$this->append_text( $chunk['content'] );
			}

			if ( $chunk['type'] === self::start_tag ) {
				$this->start_tag( $chunk['content'] );
			}

			if ( $chunk['type'] === self::end_tag ) {
				$this->end_tag( $chunk['content'] );
			}

			if ( $chunk['type'] === self::comment ) {
				$this->append_comment( $chunk['content'] );
			}
		}

		// Close all remaining tags.
		$this->close_all_tags();

		return $this->output;
	}


	/**
	 * Appends a text node content to the output property.
	 *
	 * @param string $content Text node content.
	 */
	public function append_text( $content ) {
		if ( $this->is_inside( array( 'pre', 'template' ) ) ) {
			$this->output .= $content;
			return;
		}

		if (
			empty( $this->stacked_elements ) or
			$this->has_parent( 'p' ) or
			$this->has_parent( self::p_parent_elements )
		) {
			// Close <p> if the content starts with multiple line breaks.
			if ( preg_match( '/^\s*\n\s*\n\s*/', $content ) ) {
				$this->end_tag( 'p' );
			}

			// Split up the contents into paragraphs, separated by double line breaks.
			$paragraphs = preg_split( '/\s*\n\s*\n\s*/', $content );

			$paragraphs = array_filter( $paragraphs, static function ( $paragraph ) {
				return '' !== trim( $paragraph );
			} );

			$paragraphs = array_values( $paragraphs );

			if ( $paragraphs ) {
				if ( $this->is_inside( 'p' ) ) {
					$paragraph = array_shift( $paragraphs );

					$paragraph = self::normalize_paragraph(
						$paragraph,
						$this->options['auto_br']
					);

					$this->output .= $paragraph;
				}

				foreach ( $paragraphs as $paragraph ) {
					$this->start_tag( 'p' );

					$paragraph = ltrim( $paragraph );

					$paragraph = self::normalize_paragraph(
						$paragraph,
						$this->options['auto_br']
					);

					$this->output .= $paragraph;
				}
			}

			// Close <p> if the content ends with multiple line breaks.
			if ( preg_match( '/\s*\n\s*\n\s*$/', $content ) ) {
				$this->end_tag( 'p' );
			}

			// Cases where the content is a single line break.
			if ( preg_match( '/^\s*\n\s*$/', $content ) ) {
				$auto_br = $this->options['auto_br'] && $this->is_inside( 'p' );

				$content = self::normalize_paragraph( $content, $auto_br );

				$this->output .= $content;
			}
		} else {
			$auto_br = $this->options['auto_br'] &&
				$this->has_parent( self::br_parent_elements );

			$content = self::normalize_paragraph( $content, $auto_br );

			$this->output .= $content;
		}
	}


	/**
	 * Appends a start tag to the output property.
	 *
	 * @param string $tag A start tag.
	 */
	public function start_tag( $tag ) {
		list( $tag, $tag_name ) = self::normalize_start_tag( $tag );

		if ( in_array( $tag_name, self::p_child_elements ) ) {
			if (
				! $this->is_inside( 'p' ) and
				! $this->is_inside( self::p_child_elements ) and
				! $this->has_parent( self::p_nonparent_elements )
			) {
				// Open <p> if it does not exist.
				$this->start_tag( 'p' );
			}
		} elseif (
			'p' === $tag_name or
			in_array( $tag_name, self::p_parent_elements ) or
			in_array( $tag_name, self::p_nonparent_elements )
		) {
			// Close <p> if it exists.
			$this->end_tag( 'p' );
		}

		if ( 'dd' === $tag_name or 'dt' === $tag_name ) {
			// Close <dd> and <dt> if closing tag is omitted.
			$this->end_tag( 'dd' );
			$this->end_tag( 'dt' );
		}

		if ( 'li' === $tag_name ) {
			// Close <li> if closing tag is omitted.
			$this->end_tag( 'li' );
		}

		if ( 'optgroup' === $tag_name ) {
			// Close <option> and <optgroup> if closing tag is omitted.
			$this->end_tag( 'option' );
			$this->end_tag( 'optgroup' );
		}

		if ( 'option' === $tag_name ) {
			// Close <option> if closing tag is omitted.
			$this->end_tag( 'option' );
		}

		if ( 'rp' === $tag_name or 'rt' === $tag_name ) {
			// Close <rp> and <rt> if closing tag is omitted.
			$this->end_tag( 'rp' );
			$this->end_tag( 'rt' );
		}

		if ( 'td' === $tag_name or 'th' === $tag_name ) {
			// Close <td> and <th> if closing tag is omitted.
			$this->end_tag( 'td' );
			$this->end_tag( 'th' );
		}

		if ( 'tr' === $tag_name ) {
			// Close <tr> if closing tag is omitted.
			$this->end_tag( 'tr' );
		}

		if ( 'tbody' === $tag_name or 'tfoot' === $tag_name ) {
			// Close <thead> if closing tag is omitted.
			$this->end_tag( 'thead' );
		}

		if ( 'tfoot' === $tag_name ) {
			// Close <tbody> if closing tag is omitted.
			$this->end_tag( 'tbody' );
		}

		if ( ! in_array( $tag_name, self::void_elements ) ) {
			array_unshift( $this->stacked_elements, $tag_name );
		}

		if ( ! in_array( $tag_name, self::p_child_elements ) ) {
			if ( '' !== $this->output ) {
				$this->output = rtrim( $this->output ) . "\n";
			}

			if ( $this->options['auto_indent'] ) {
				$this->output .= self::indent( count( $this->stacked_elements ) - 1 );
			}
		}

		$this->output .= $tag;
	}


	/**
	 * Closes an element and its open descendants at a time.
	 *
	 * @param string $tag An end tag.
	 */
	public function end_tag( $tag ) {
		if ( preg_match( '/<\/(.+?)(?:\s|>)/', $tag, $matches ) ) {
			$tag_name = strtolower( $matches[1] );
		} else {
			$tag_name = strtolower( $tag );
		}

		$stacked_elements = array_values( $this->stacked_elements );

		$tag_position = array_search( $tag_name, $stacked_elements );

		if ( false === $tag_position ) {
			return;
		}

		// Element groups that make up an indirect nesting structure.
		// Descendant can contain ancestors.
		static $nesting_families = array(
			array(
				'ancestors' => array( 'dl', ),
				'descendants' => array( 'dd', 'dt', ),
			),
			array(
				'ancestors' => array( 'ol', 'ul', 'menu', ),
				'descendants' => array( 'li', ),
			),
			array(
				'ancestors' => array( 'table', ),
				'descendants' => array( 'td', 'th', 'tr', 'thead', 'tbody', 'tfoot', ),
			),
		);

		foreach ( $nesting_families as $family ) {
			$ancestors = (array) $family['ancestors'];
			$descendants = (array) $family['descendants'];

			if ( in_array( $tag_name, $descendants ) ) {
				$intersect = array_intersect(
					$ancestors,
					array_slice( $stacked_elements, 0, $tag_position )
				);

				if ( $intersect ) { // Ancestor appears after descendant.
					return;
				}
			}
		}

		while ( $element = array_shift( $this->stacked_elements ) ) {
			$this->append_end_tag( $element );

			if ( $element === $tag_name ) {
				break;
			}
		}
	}


	/**
	 * Closes all open tags.
	 */
	public function close_all_tags() {
		while ( $element = array_shift( $this->stacked_elements ) ) {
			$this->append_end_tag( $element );
		}
	}


	/**
	 * Appends an end tag to the output property.
	 *
	 * @param string $tag_name Tag name.
	 */
	public function append_end_tag( $tag_name ) {
		if ( ! in_array( $tag_name, self::p_child_elements ) ) {
			// Remove unnecessary <br />.
			$this->output = preg_replace( '/\s*<br \/>\s*$/', '', $this->output );

			$this->output = rtrim( $this->output ) . "\n";

			if ( $this->options['auto_indent'] ) {
				$this->output .= self::indent( count( $this->stacked_elements ) );
			}
		}

		$this->output .= sprintf( '</%s>', $tag_name );

		// Remove trailing <p></p>.
		$this->output = preg_replace( '/<p>\s*<\/p>$/', '', $this->output );
	}


	/**
	 * Appends an HTML comment to the output property.
	 *
	 * @param string $tag An HTML comment.
	 */
	public function append_comment( $tag ) {
		$this->output .= $tag;
	}


	/**
	 * Returns true if it is currently inside one of HTML elements specified
	 * by tag names.
	 *
	 * @param string|array $tag_names A tag name or an array of tag names.
	 */
	public function is_inside( $tag_names ) {
		$tag_names = (array) $tag_names;

		foreach ( $this->stacked_elements as $element ) {
			if ( in_array( $element, $tag_names ) ) {
				return true;
			}
		}

		return false;
	}


	/**
	 * Returns true if the parent node is one of HTML elements specified
	 * by tag names.
	 *
	 * @param string|array $tag_names A tag name or an array of tag names.
	 */
	public function has_parent( $tag_names ) {
		$tag_names = (array) $tag_names;

		$parent = reset( $this->stacked_elements );

		if ( false === $parent ) {
			return false;
		}

		return in_array( $parent, $tag_names );
	}


	/**
	 * Calculates the position of the next chunk based on the position and
	 * length of the current chunk.
	 *
	 * @param array $chunk An associative array of the current chunk.
	 * @return int The position of the next chunk.
	 */
	public static function calc_next_position( $chunk ) {
		return $chunk['position'] + strlen( $chunk['content'] );
	}


	/**
	 * Outputs a set of tabs to indent.
	 *
	 * @param int $level Indentation level.
	 * @return string A series of tabs.
	 */
	public static function indent( $level ) {
		$level = (int) $level;

		if ( 0 < $level ) {
			return str_repeat( "\t", $level );
		}

		return '';
	}


	/**
	 * Normalizes a start tag.
	 *
	 * @param string $tag A start tag or a tag name.
	 * @return array An array includes the normalized start tag and tag name.
	 */
	public static function normalize_start_tag( $tag ) {
		if ( preg_match( '/<(.+?)[\s\/>]/', $tag, $matches ) ) {
			$tag_name = strtolower( $matches[1] );
		} else {
			$tag_name = strtolower( $tag );
			$tag = sprintf( '<%s>', $tag_name );
		}

		if ( in_array( $tag_name, self::void_elements ) ) {
			// Normalize void element.
			$tag = preg_replace( '/\s*\/?>/', ' />', $tag );
		}

		return array( $tag, $tag_name );
	}


	/**
	 * Normalizes a paragraph of text.
	 *
	 * @param string $paragraph A paragraph of text.
	 * @param bool $auto_br Optional. If true, line breaks will be replaced
	 *             by a br element.
	 * @return string The normalized paragraph.
	 */
	public static function normalize_paragraph( $paragraph, $auto_br = false ) {
		if ( $auto_br ) {
			$paragraph = preg_replace( '/\s*\n\s*/', "<br />\n", $paragraph );
		}

		$paragraph = preg_replace( '/[ ]+/', " ", $paragraph );

		return $paragraph;
	}

}
