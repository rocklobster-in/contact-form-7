export function getFieldValues( formData, fieldName ) {
	let values = [];

	for ( const field of formData ) {
		const name = field[ 0 ].replace( /\[.*\]$/, '' );
		const value = field[ 1 ];

		if ( fieldName === name && '' !== value ) {
			values.push( value );
		}
	}

	return values;
}
