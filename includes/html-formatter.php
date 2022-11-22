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
	private $options = array();

	public function __construct( string $input, $args = '' ) {
		$this->input = $input;

		$this->options = wp_parse_args( $args, array(
			'auto_br' => true,
		) );
	}

	public function format() {
		$input = &$this->input;

		$iterator = new WPCF7_HTMLIterator( $input );

		$elements = array();
		$output = '';

		foreach ( $iterator->iterate() as $chunk ) {
			$position = $chunk['position'];
			$type = $chunk['type'];
			$content = $chunk['content'];

			// Standardize newline characters to "\n".
			$content = str_replace( array( "\r\n", "\r" ), "\n", $content );

			// Pre tags shouldn't be touched by autop.
			if ( false !== array_search( 'pre', $elements ) ) {
				$output .= $content;
				continue;
			}

			if ( $type === WPCF7_HTMLIterator::text ) {
				if ( in_array( reset( $elements ), self::p_child_elements, true ) ) {
					if ( $this->options['auto_br'] ) {
						$content = preg_replace( '/\n+/', '<br />', $content );
					}
				}

				$output .= $content;
			}

			if ( $type === WPCF7_HTMLIterator::opening_tag ) {
				preg_match( '/<(.+?)[\s\/>]/', $content, $matches );
				$tag_name = strtolower( $matches[1] );

				// Normalize void element.
				if ( in_array( $tag_name, WPCF7_HTMLFormatter::void_elements ) ) {
					$content = preg_replace( '/\s*\/?>/', ' />', $content );
				} else {
					array_unshift( $elements, $tag_name );
				}

				$output .= $content;
			}

			if ( $type === WPCF7_HTMLIterator::closing_tag ) {
				// Remove whitespaces.
				$content = preg_replace( '/\s+/', '', $content );

				preg_match( '/<\/(.+?)(?:\s|>)/', $content, $matches );
				$tag_name = strtolower( $matches[1] );
				$opening_tag_offset = array_search( $tag_name, $elements );

				if ( false !== $opening_tag_offset ) {
					$elements = array_slice( $elements, $opening_tag_offset + 1 );
					$output .= $content;
				}
			}

			if ( $type === WPCF7_HTMLIterator::comment ) {
				$output .= $content;
			}

		}

		return $output;
	}

}
