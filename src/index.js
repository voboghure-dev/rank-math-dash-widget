import { render } from '@wordpress/element';
import App from './App';

document.addEventListener('DOMContentLoaded', function () {
	let element = document.getElementById('rmdw-widget-reactjs');
	if (typeof element != 'undefined' && element !== null) {
		render(<App />, element);
	}
});
