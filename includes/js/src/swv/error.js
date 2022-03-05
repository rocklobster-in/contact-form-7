export function ValidationError( { rule, field, error, ...properties } ) {
	this.rule = rule;
	this.field = field;
	this.error = error;
	this.properties = properties;
}
