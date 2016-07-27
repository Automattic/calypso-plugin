import React from 'react';
import { localize } from 'i18n-calypso';
import Notice from 'components/notice';
import NoticeAction from 'components/notice/notice-action';

class AdminNotices extends React.Component {

	constructor( props ) {
		super( props );

		this.viewClicked = this.viewClicked.bind( this );

		this.state = {
			noticeList: null,
			count: 0,
			hidden: true
		};
	}

	componentWillMount() {
		const noticeList = document.getElementById( 'admin-notice-list' );
		let count = 0;

		count += this.countElements( noticeList, 'updated' );
		count += this.countElements( noticeList, 'update-nag' );
		count += this.countElements( noticeList, 'notice-error' );
		count += this.countElements( noticeList, 'notice-warning' );
		count += this.countElements( noticeList, 'notice-success' );
		count += this.countElements( noticeList, 'notice-info' );

		this.setState( Object.assign( {}, { noticeList, count } ) );
	}

	countElements( list, className ) {
		const elements = list.getElementsByClassName( className );
		return elements.length;
	}

	viewClicked( evt ) {
		const { hide } = this.state;

		evt.preventDefault();

		this.setHidden( !this.state.hidden );
	}

	setHidden( hidden ) {

		if ( hidden ) {
			this.state.noticeList.className = 'admin-notice-list-hide';
		} else {
			this.state.noticeList.className = 'admin-notice-list-show';
		}

		this.setState( Object.assign( {}, { hidden } ) );
	}

	render() {
		const __ = this.props.translate;
		const { count, hidden } = this.state;

		if ( count > 0 ) {
			return (
				<div>
					<Notice
						status="is-info"
						icon="notice"
						showDismiss={ false }
						text={ __(
							'There is a WordPress notice which needs your attention',
							'There are %(count)d WordPress notices which need your attention',
							{ count, args: { count } } ) }
						className="wordpress-notices"
					>
						<NoticeAction href="#" external={ false } onClick={ this.viewClicked }>
							{ hidden ? __( 'View' ) : __( 'Hide' ) }
						</NoticeAction>
					</Notice>
				</div>
			);
		} else {
			return null;
		}
	}
}

export default localize( AdminNotices );
