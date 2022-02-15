export function ValidationError( { rule, field, message, ...properties } ) {
	this.rule = rule;
	this.field = field;
	this.message = message;
	this.properties = properties;
}
