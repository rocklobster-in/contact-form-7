export const apiFetch = options => {
	const {
		root,
		namespace = 'contact-form-7/v1',
	} = wpcf7.api;

	const fetchHandler = nextOptions => {
		let {
			url,
			path,
			endpoint,
			headers,
			body,
			data,
			...remainingOptions
		} = nextOptions;

		let namespaceTrimmed, endpointTrimmed;

		if ( typeof endpoint === 'string' ) {
			namespaceTrimmed = namespace.replace( /^\/|\/$/g, '' );
			endpointTrimmed = endpoint.replace( /^\//, '' );

			if ( endpointTrimmed ) {
				path = namespaceTrimmed + '/' + endpointTrimmed;
			} else {
				path = namespaceTrimmed;
			}
		}

		if ( typeof path === 'string' ) {
			if ( -1 !== root.indexOf( '?' ) ) {
				path = path.replace( '?', '&' );
			}

			path = path.replace( /^\//, '' );

			url = root + path;
		}

		headers = {
			Accept: 'application/json, */*;q=0.1',
			...headers,
		};

		delete headers[ 'X-WP-Nonce' ];

		if ( data ) {
			body = JSON.stringify( data );
			headers[ 'Content-Type' ] = 'application/json';
		}

		const fetchError = {
			code: 'fetch_error',
			message: 'You are probably offline.',
		};

		const invalidJsonError = {
			code: 'invalid_json',
			message: 'The response is not a valid JSON response.',
		};

		return (
			window.fetch(
				url || path || window.location.href,
				{
					...remainingOptions,
					headers,
					body,
				}
			).then(
				value => {
					return Promise.resolve( value )
						.then( response => {
							if ( response.status >= 200 && response.status < 300 ) {
								return response;
							} else {
								throw response;
							}
						} )
						.then( response => {
							if ( response.status === 204 ) {
								return null;
							}

							if ( response && response.json ) {
								return response.json().catch( () => {
									throw invalidJsonError;
								} );
							}

							throw invalidJsonError;
						} );
				},
				() => {
					throw fetchError;
				}
			)
		);
	};

	const enhancedHandler = middlewares.reduceRight( (
		next,
		middleware
	) => {
		return ( workingOptions ) => middleware( workingOptions, next );
	}, fetchHandler );

	return enhancedHandler( options );
};

const middlewares = [];

apiFetch.use = middleware => {
	middlewares.unshift( middleware );
};
