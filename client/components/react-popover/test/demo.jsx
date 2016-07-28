import React, { PropTypes } from 'react';
import Draggable from 'react-draggable';
import Gridicon from 'components/gridicon';
import Popover, { show } from '../';

export default class Demo extends React.Component {
	constructor( props ) {
		super( props );

		this.state = {
			popoverContext: null,
		};

		this.togglePopover = this.togglePopover.bind( this );
	}

	togglePopover( evt ) {
		let target = evt.currentTarget;

		// Only set target it if wasn't set before.
		target = ( this.state.popoverContext === target ? null : target );

		this.setState( { popoverContext: target } );
	}

	render() {
		return (
				<div>
					<h3>React Popover Demo</h3>
					<Gridicon icon="info-outline" onClick={ this.togglePopover } />
					<Draggable>
						<div style={ { width: '50px', height: '50px', backgroundColor: '#ccf' } } >
							<Gridicon icon="info" onClick={ this.togglePopover } />
						</div>
					</Draggable>

					{ this.renderPopover( this.state.popoverContext ) }
				</div>
		);
	}

	renderPopover( context ) {
		return (
			<Popover context={ context } >
				<span>I am a popover.</span>
			</Popover>
		);
	}
}

