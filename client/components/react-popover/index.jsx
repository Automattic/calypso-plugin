import React, { PropTypes } from 'react';
import RootChild from 'components/root-child';
import debug from 'debug';

const log = debug( 'calypso:react-popover' );

export default class Popover extends React.Component {

	constructor( props ) {
		super( props );

		this.state = {
			context: null
		};
	}

	getContextRect() {
		const { context } = this.props;

		if ( context instanceof Element ) {
			return context.getBoundingClientRect();
		} else {
			return context;
		}
	}

	render() {
		const rect = this.getContextRect();

		if ( rect ) {
			const divStyle = {
				position: 'absolute',
				top: rect.bottom + 'px',
				left: rect.left + 'px',
			};

			return (
					<RootChild>
						<div style={ divStyle } >
							{ this.props.children }
						</div>
					</RootChild>
			);
		} else {
			return null;
		}
	}
}

