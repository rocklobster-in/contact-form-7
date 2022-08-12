export const getMimeTypes = () => {
	const mimeTypes = new Map();

	// https://developer.wordpress.org/reference/functions/wp_get_mime_types/
	mimeTypes.set( 'jpg|jpeg|jpe', 'image/jpeg' );
	mimeTypes.set( 'gif', 'image/gif' );
	mimeTypes.set( 'png', 'image/png' );
	mimeTypes.set( 'bmp', 'image/bmp' );
	mimeTypes.set( 'tiff|tif', 'image/tiff' );
	mimeTypes.set( 'webp', 'image/webp' );
	mimeTypes.set( 'ico', 'image/x-icon' );
	mimeTypes.set( 'heic', 'image/heic' );
	mimeTypes.set( 'asf|asx', 'video/x-ms-asf' );
	mimeTypes.set( 'wmv', 'video/x-ms-wmv' );
	mimeTypes.set( 'wmx', 'video/x-ms-wmx' );
	mimeTypes.set( 'wm', 'video/x-ms-wm' );
	mimeTypes.set( 'avi', 'video/avi' );
	mimeTypes.set( 'divx', 'video/divx' );
	mimeTypes.set( 'flv', 'video/x-flv' );
	mimeTypes.set( 'mov|qt', 'video/quicktime' );
	mimeTypes.set( 'mpeg|mpg|mpe', 'video/mpeg' );
	mimeTypes.set( 'mp4|m4v', 'video/mp4' );
	mimeTypes.set( 'ogv', 'video/ogg' );
	mimeTypes.set( 'webm', 'video/webm' );
	mimeTypes.set( 'mkv', 'video/x-matroska' );
	mimeTypes.set( '3gp|3gpp', 'video/3gpp' );
	mimeTypes.set( '3g2|3gp2', 'video/3gpp2' );
	mimeTypes.set( 'txt|asc|c|cc|h|srt', 'text/plain' );
	mimeTypes.set( 'csv', 'text/csv' );
	mimeTypes.set( 'tsv', 'text/tab-separated-values' );
	mimeTypes.set( 'ics', 'text/calendar' );
	mimeTypes.set( 'rtx', 'text/richtext' );
	mimeTypes.set( 'css', 'text/css' );
	mimeTypes.set( 'htm|html', 'text/html' );
	mimeTypes.set( 'vtt', 'text/vtt' );
	mimeTypes.set( 'dfxp', 'application/ttaf+xml' );
	mimeTypes.set( 'mp3|m4a|m4b', 'audio/mpeg' );
	mimeTypes.set( 'aac', 'audio/aac' );
	mimeTypes.set( 'ra|ram', 'audio/x-realaudio' );
	mimeTypes.set( 'wav', 'audio/wav' );
	mimeTypes.set( 'ogg|oga', 'audio/ogg' );
	mimeTypes.set( 'flac', 'audio/flac' );
	mimeTypes.set( 'mid|midi', 'audio/midi' );
	mimeTypes.set( 'wma', 'audio/x-ms-wma' );
	mimeTypes.set( 'wax', 'audio/x-ms-wax' );
	mimeTypes.set( 'mka', 'audio/x-matroska' );
	mimeTypes.set( 'rtf', 'application/rtf' );
	mimeTypes.set( 'js', 'application/javascript' );
	mimeTypes.set( 'pdf', 'application/pdf' );
	mimeTypes.set( 'swf', 'application/x-shockwave-flash' );
	mimeTypes.set( 'class', 'application/java' );
	mimeTypes.set( 'tar', 'application/x-tar' );
	mimeTypes.set( 'zip', 'application/zip' );
	mimeTypes.set( 'gz|gzip', 'application/x-gzip' );
	mimeTypes.set( 'rar', 'application/rar' );
	mimeTypes.set( '7z', 'application/x-7z-compressed' );
	mimeTypes.set( 'exe', 'application/x-msdownload' );
	mimeTypes.set( 'psd', 'application/octet-stream' );
	mimeTypes.set( 'xcf', 'application/octet-stream' );
	mimeTypes.set( 'doc', 'application/msword' );
	mimeTypes.set( 'pot|pps|ppt', 'application/vnd.ms-powerpoint' );
	mimeTypes.set( 'wri', 'application/vnd.ms-write' );
	mimeTypes.set( 'xla|xls|xlt|xlw', 'application/vnd.ms-excel' );
	mimeTypes.set( 'mdb', 'application/vnd.ms-access' );
	mimeTypes.set( 'mpp', 'application/vnd.ms-project' );
	mimeTypes.set( 'docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' );
	mimeTypes.set( 'docm', 'application/vnd.ms-word.document.macroEnabled.12' );
	mimeTypes.set( 'dotx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.template' );
	mimeTypes.set( 'dotm', 'application/vnd.ms-word.template.macroEnabled.12' );
	mimeTypes.set( 'xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' );
	mimeTypes.set( 'xlsm', 'application/vnd.ms-excel.sheet.macroEnabled.12' );
	mimeTypes.set( 'xlsb', 'application/vnd.ms-excel.sheet.binary.macroEnabled.12' );
	mimeTypes.set( 'xltx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.template' );
	mimeTypes.set( 'xltm', 'application/vnd.ms-excel.template.macroEnabled.12' );
	mimeTypes.set( 'xlam', 'application/vnd.ms-excel.addin.macroEnabled.12' );
	mimeTypes.set( 'pptx', 'application/vnd.openxmlformats-officedocument.presentationml.presentation' );
	mimeTypes.set( 'pptm', 'application/vnd.ms-powerpoint.presentation.macroEnabled.12' );
	mimeTypes.set( 'ppsx', 'application/vnd.openxmlformats-officedocument.presentationml.slideshow' );
	mimeTypes.set( 'ppsm', 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12' );
	mimeTypes.set( 'potx', 'application/vnd.openxmlformats-officedocument.presentationml.template' );
	mimeTypes.set( 'potm', 'application/vnd.ms-powerpoint.template.macroEnabled.12' );
	mimeTypes.set( 'ppam', 'application/vnd.ms-powerpoint.addin.macroEnabled.12' );
	mimeTypes.set( 'sldx', 'application/vnd.openxmlformats-officedocument.presentationml.slide' );
	mimeTypes.set( 'sldm', 'application/vnd.ms-powerpoint.slide.macroEnabled.12' );
	mimeTypes.set( 'onetoc|onetoc2|onetmp|onepkg', 'application/onenote' );
	mimeTypes.set( 'oxps', 'application/oxps' );
	mimeTypes.set( 'xps', 'application/vnd.ms-xpsdocument' );
	mimeTypes.set( 'odt', 'application/vnd.oasis.opendocument.text' );
	mimeTypes.set( 'odp', 'application/vnd.oasis.opendocument.presentation' );
	mimeTypes.set( 'ods', 'application/vnd.oasis.opendocument.spreadsheet' );
	mimeTypes.set( 'odg', 'application/vnd.oasis.opendocument.graphics' );
	mimeTypes.set( 'odc', 'application/vnd.oasis.opendocument.chart' );
	mimeTypes.set( 'odb', 'application/vnd.oasis.opendocument.database' );
	mimeTypes.set( 'odf', 'application/vnd.oasis.opendocument.formula' );
	mimeTypes.set( 'wp|wpd', 'application/wordperfect' );
	mimeTypes.set( 'key', 'application/vnd.apple.keynote' );
	mimeTypes.set( 'numbers', 'application/vnd.apple.numbers' );
	mimeTypes.set( 'pages', 'application/vnd.apple.pages' );

	return mimeTypes;
};


export const convertMimeToExt = mime => {
	const results = [];

	const found = mime.match(
		/^(?<toplevel>[a-z]+)\/(?<sub>[*]|[a-z0-9.+-]+)$/i
	);

	if ( found ) {
		const toplevel = found.groups.toplevel.toLowerCase();
		const sub = found.groups.sub.toLowerCase();

		for ( const [ key, value ] of getMimeTypes() ) {
			if ( '*' === sub && value.startsWith( toplevel + '/' ) ) {
				results.push( ...key.split( '|' ) );
			} else if ( value === found[ 0 ] ) {
				results.push( ...key.split( '|' ) );
			}
		}
	}

	return results;
};
