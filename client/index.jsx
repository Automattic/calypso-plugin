import 'babel-polyfill';
import 'calypso/boot';
import React from 'react';
import ReactDOM from 'react-dom';
import '../assets/stylesheets/style.scss';
import AdminNotices from './admin-notices';

const rootComponent = (
	<div>
		<h3>Calypso Plugin</h3>
		<p>This is generated output from React</p>
	</div>
);

ReactDOM.render( <AdminNotices />, document.getElementById( 'calypso-plugin-notices' ) );

ReactDOM.render( rootComponent, document.getElementById( 'calypso-plugin' ) );
