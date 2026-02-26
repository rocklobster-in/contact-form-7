<?php

namespace RockLobsterInc\Swv\Rules;

use RockLobsterInc\FormDataTree\{ FormDataTreeInterface as FormDataTree };
use RockLobsterInc\Swv\{ AbstractRule, InvalidityException as Invalidity };
use function RockLobsterInc\Functions\{ array_flatten };

final class FileRule extends AbstractRule {

	const RULE_NAME = 'file';

	const MIME_TYPES = [
		'application/java'
			=> [ 'class' ],
		'application/javascript'
			=> [ 'js' ],
		'application/msword'
			=> [ 'doc' ],
		'application/octet-stream'
			=> [ 'psd', 'xcf' ],
		'application/onenote'
			=> [ 'onetoc', 'onetoc2', 'onetmp', 'onepkg' ],
		'application/oxps'
			=> [ 'oxps' ],
		'application/pdf'
			=> [ 'pdf' ],
		'application/rar'
			=> [ 'rar' ],
		'application/rtf'
			=> [ 'rtf' ],
		'application/ttaf+xml'
			=> [ 'dfxp' ],
		'application/vnd.apple.keynote'
			=> [ 'key' ],
		'application/vnd.apple.numbers'
			=> [ 'numbers' ],
		'application/vnd.apple.pages'
			=> [ 'pages' ],
		'application/vnd.ms-access'
			=> [ 'mdb' ],
		'application/vnd.ms-excel'
			=> [ 'xla', 'xls', 'xlt', 'xlw' ],
		'application/vnd.ms-excel.addin.macroEnabled.12'
			=> [ 'xlam' ],
		'application/vnd.ms-excel.sheet.binary.macroEnabled.12'
			=> [ 'xlsb' ],
		'application/vnd.ms-excel.sheet.macroEnabled.12'
			=> [ 'xlsm' ],
		'application/vnd.ms-excel.template.macroEnabled.12'
			=> [ 'xltm' ],
		'application/vnd.ms-powerpoint'
			=> [ 'pot', 'pps', 'ppt' ],
		'application/vnd.ms-powerpoint.addin.macroEnabled.12'
			=> [ 'ppam' ],
		'application/vnd.ms-powerpoint.presentation.macroEnabled.12'
			=> [ 'pptm' ],
		'application/vnd.ms-powerpoint.slide.macroEnabled.12'
			=> [ 'sldm' ],
		'application/vnd.ms-powerpoint.slideshow.macroEnabled.12'
			=> [ 'ppsm' ],
		'application/vnd.ms-powerpoint.template.macroEnabled.12'
			=> [ 'potm' ],
		'application/vnd.ms-project'
			=> [ 'mpp' ],
		'application/vnd.ms-word.document.macroEnabled.12'
			=> [ 'docm' ],
		'application/vnd.ms-word.template.macroEnabled.12'
			=> [ 'dotm' ],
		'application/vnd.ms-write'
			=> [ 'wri' ],
		'application/vnd.ms-xpsdocument'
			=> [ 'xps' ],
		'application/vnd.oasis.opendocument.chart'
			=> [ 'odc' ],
		'application/vnd.oasis.opendocument.database'
			=> [ 'odb' ],
		'application/vnd.oasis.opendocument.formula'
			=> [ 'odf' ],
		'application/vnd.oasis.opendocument.graphics'
			=> [ 'odg' ],
		'application/vnd.oasis.opendocument.presentation'
			=> [ 'odp' ],
		'application/vnd.oasis.opendocument.spreadsheet'
			=> [ 'ods' ],
		'application/vnd.oasis.opendocument.text'
			=> [ 'odt' ],
		'application/vnd.openxmlformats-officedocument.presentationml.presentation'
			=> [ 'pptx' ],
		'application/vnd.openxmlformats-officedocument.presentationml.slide'
			=> [ 'sldx' ],
		'application/vnd.openxmlformats-officedocument.presentationml.slideshow'
			=> [ 'ppsx' ],
		'application/vnd.openxmlformats-officedocument.presentationml.template'
			=> [ 'potx' ],
		'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
			=> [ 'xlsx' ],
		'application/vnd.openxmlformats-officedocument.spreadsheetml.template'
			=> [ 'xltx' ],
		'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
			=> [ 'docx' ],
		'application/vnd.openxmlformats-officedocument.wordprocessingml.template'
			=> [ 'dotx' ],
		'application/wordperfect'
			=> [ 'wp', 'wpd' ],
		'application/x-7z-compressed'
			=> [ '7z' ],
		'application/x-gzip'
			=> [ 'gz', 'gzip' ],
		'application/x-msdownload'
			=> [ 'exe' ],
		'application/x-shockwave-flash'
			=> [ 'swf' ],
		'application/x-tar'
			=> [ 'tar' ],
		'application/zip'
			=> [ 'zip' ],
		'audio/3gpp'
			=> [ '3gp', '3gpp' ],
		'audio/3gpp2'
			=> [ '3g2', '3gp2' ],
		'audio/aac'
			=> [ 'aac' ],
		'audio/flac'
			=> [ 'flac' ],
		'audio/midi'
			=> [ 'mid', 'midi' ],
		'audio/mpeg'
			=> [ 'mp3', 'm4a', 'm4b' ],
		'audio/ogg'
			=> [ 'ogg', 'oga' ],
		'audio/wav'
			=> [ 'wav', 'x-wav' ],
		'audio/x-matroska'
			=> [ 'mka' ],
		'audio/x-ms-wax'
			=> [ 'wax' ],
		'audio/x-ms-wma'
			=> [ 'wma' ],
		'audio/x-realaudio'
			=> [ 'ra', 'ram' ],
		'image/avif'
			=> [ 'avif' ],
		'image/bmp'
			=> [ 'bmp' ],
		'image/gif'
			=> [ 'gif' ],
		'image/heic'
			=> [ 'heic' ],
		'image/heic-sequence'
			=> [ 'heic', 'heics' ],
		'image/heif'
			=> [ 'heic', 'heif' ],
		'image/heif-sequence'
			=> [ 'heic', 'heifs' ],
		'image/jpeg'
			=> [ 'jpg', 'jpeg', 'jpe' ],
		'image/png'
			=> [ 'png' ],
		'image/tiff'
			=> [ 'tiff', 'tif' ],
		'image/webp'
			=> [ 'webp' ],
		'image/x-icon'
			=> [ 'ico' ],
		'text/calendar'
			=> [ 'ics' ],
		'text/css'
			=> [ 'css' ],
		'text/csv'
			=> [ 'csv' ],
		'text/html'
			=> [ 'htm', 'html' ],
		'text/plain'
			=> [ 'txt', 'asc', 'c', 'cc', 'h', 'srt' ],
		'text/richtext'
			=> [ 'rtx' ],
		'text/tab-separated-values'
			=> [ 'tsv' ],
		'text/vtt'
			=> [ 'vtt' ],
		'video/3gpp'
			=> [ '3gp', '3gpp' ],
		'video/3gpp2'
			=> [ '3g2', '3gp2' ],
		'video/avi'
			=> [ 'avi' ],
		'video/divx'
			=> [ 'divx' ],
		'video/mp4'
			=> [ 'mp4', 'm4v' ],
		'video/mpeg'
			=> [ 'mpeg', 'mpg', 'mpe' ],
		'video/ogg'
			=> [ 'ogv' ],
		'video/quicktime'
			=> [ 'mov', 'qt' ],
		'video/webm'
			=> [ 'webm' ],
		'video/x-flv'
			=> [ 'flv' ],
		'video/x-matroska'
			=> [ 'mkv' ],
		'video/x-ms-asf'
			=> [ 'asf', 'asx' ],
		'video/x-ms-wm'
			=> [ 'wm' ],
		'video/x-ms-wmv'
			=> [ 'wmv' ],
		'video/x-ms-wmx'
			=> [ 'wmx' ],
	];


	/**
	 * Rule properties.
	 */
	public readonly string $field;
	public readonly string $error;
	public readonly array $accept;


	/**
	 * Constructor.
	 *
	 * @param array $properties Rule properties.
	 */
	public function __construct( array $properties = [] ) {
		$this->field = $properties[ 'field' ] ?? '';
		$this->error = $properties[ 'error' ] ?? '';
		$this->accept = $properties[ 'accept' ] ?? [];
	}


	/**
	 * Converts a MIME type string to an array of corresponding file extensions.
	 *
	 * @param string $mime MIME type. Wildcard (*) is available for the subtype.
	 * @return array Corresponding file extensions.
	 */
	public static function convertMimeToExt( string $mime ): array {
		$results = [];

		if ( preg_match( '%^([a-z]+)/([*]|[a-z0-9.+-]+)$%i', $mime, $matches ) ) {
			$maintype = $matches[ 1 ];
			$subtype = $matches[ 2 ];

			if ( '*' !== $subtype ) {
				$results = self::MIME_TYPES[ $mime ];
			} else {
				$mime_types = array_filter(
					self::MIME_TYPES,
					static function ( $mime_type ) use ( $maintype ) {
						return 0 === strpos( $mime_type, $maintype . '/' );
					},
					ARRAY_FILTER_USE_KEY
				);

				$results = array_merge( ...array_values( $mime_types ) );
			}
		}

		return array_values( array_unique( $results ) );
	}


	/**
	 * Returns true if this rule matches the given context.
	 *
	 * @param array $context Context.
	 */
	public function matches( array $context ): bool {
		if ( false === parent::matches( $context ) ) {
			return false;
		}

		if ( empty( $context[ 'file' ] ) ) {
			return false;
		}

		return true;
	}


	/**
	 * Validates the form data according to the logic defined by this rule.
	 *
	 * @param FormDataTree $form_data Form data.
	 * @param array $context Optional context.
	 */
	public function validate( FormDataTree $form_data, array $context = [] ) {
		$files = $form_data->getAllFiles( $this->field );
		$files = array_flatten( $files );

		if ( empty( $files ) ) {
			return true;
		}

		$acceptable_filetypes = [];

		foreach ( $this->accept as $accept ) {
			if ( preg_match( '/^\.[a-z0-9]+$/i', $accept ) ) {
				$acceptable_filetypes[] = $accept;
			} else {
				foreach ( self::convertMimeToExt( $accept ) as $extension ) {
					$acceptable_filetypes[] = sprintf( '.%s', trim( $extension, ' .' ) );
				}
			}
		}

		$acceptable_filetypes = array_map( 'strtolower', $acceptable_filetypes );
		$acceptable_filetypes = array_unique( $acceptable_filetypes );

		foreach ( $files as $file ) {
			$file_name = $file->name();

			$last_period_pos = strrpos( $file_name, '.' );

			if ( false === $last_period_pos ) { // No period.
				throw new Invalidity( $this );
			}

			$suffix = strtolower( substr( $file_name, $last_period_pos ) );

			if ( ! in_array( $suffix, $acceptable_filetypes, true ) ) {
				throw new Invalidity( $this );
			}
		}

		return true;
	}


	/**
	 * Returns an array that represents the rule properties.
	 *
	 * @return array Array of rule properties.
	 */
	public function toArray(): array {
		return [
			'rule' => self::RULE_NAME,
			'field' => $this->field,
			'error' => $this->error,
			'accept' => $this->accept,
		];
	}

}
