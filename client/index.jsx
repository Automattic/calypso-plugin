import 'babel-polyfill';
import 'calypso/boot';
import React from 'react';
import ReactDOM from 'react-dom';
import '../assets/stylesheets/style.scss';
import AdminNotices from './admin-notices';
import PopoverDemo from './components/react-popover/test/demo';

const rootComponent = (
	<div>
		<h3>Calypso Plugin</h3>
		<p>This is generated output from React</p>
		<PopoverDemo />
	</div>
);

ReactDOM.render( <AdminNotices />, document.getElementById( 'calypso-plugin-notices' ) );

ReactDOM.render( rootComponent, document.getElementById( 'calypso-plugin' ) );
