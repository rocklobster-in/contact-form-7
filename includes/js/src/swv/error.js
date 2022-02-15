export function ValidationError( code, message, ...params ) {
	this.code = code;
	this.message = message;
	this.params = params;
}
