import { createRoot, render } from '@wordpress/element';
import { TabPanel } from '@wordpress/components';

import { store } from './store';

const ContactFormEditor = () => (
	<TabPanel
		tabs={ [
			{ name: "tab1", title: "tab1" },
			{ name: "tab2", title: "tab2" },
		] }
	>
		{ ( tab ) => <p>{ tab.title }</p> }
	</TabPanel>
);

window.addEventListener(
	'load',
	function () {
		createRoot(
			document.querySelector( '#contact-form-editor' )
		).render(
			<ContactFormEditor />
		);
	},
	false
);
