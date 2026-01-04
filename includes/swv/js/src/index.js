import {
	InvalidityException,
	AbstractRule,
	CompositeRule,
	rules,
} from '@rocklobsterinc/swv';

const availableRules = new Map( [
	[ 'all', rules.AllRule ],
	[ 'any', rules.AnyRule ],
	[ 'date', rules.DateRule ],
	[ 'dayofweek', rules.DayofweekRule ],
	[ 'email', rules.EmailRule ],
	[ 'enum', rules.EnumRule ],
	[ 'file', rules.FileRule ],
	[ 'maxdate', rules.MaxDateRule ],
	[ 'maxfilesize', rules.MaxFilesizeRule ],
	[ 'maxitems', rules.MaxItemsRule ],
	[ 'maxlength', rules.MaxLengthRule ],
	[ 'maxnumber', rules.MaxNumberRule ],
	[ 'mindate', rules.MinDateRule ],
	[ 'minfilesize', rules.MinFilesizeRule ],
	[ 'minitems', rules.MinItemsRule ],
	[ 'minlength', rules.MinLengthRule ],
	[ 'minnumber', rules.MinNumberRule ],
	[ 'number', rules.NumberRule ],
	[ 'required', rules.RequiredRule ],
	[ 'requiredfile', rules.RequiredFileRule ],
	[ 'stepnumber', rules.StepNumberRule ],
	[ 'tel', rules.TelRule ],
	[ 'time', rules.TimeRule ],
	[ 'url', rules.URLRule ],
] );

window.swv = {
	InvalidityException,
	AbstractRule,
	CompositeRule,
	availableRules,
	...( window.swv ?? {} ),
};
