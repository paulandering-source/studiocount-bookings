( function () {
	'use strict';

	var source = 'studiocount-bookings';
	var version = 1;
	var serviceOrigin = 'https://www.studiocount.com';
	var exactKeys = {
		ready: [ 'instanceId', 'source', 'type', 'version' ],
		resize: [ 'height', 'instanceId', 'source', 'type', 'version' ],
		navigate: [ 'instanceId', 'source', 'type', 'url', 'version' ]
	};

	function hasExactKeys( value, keys ) {
		if ( ! value || 'object' !== typeof value || Array.isArray( value ) ) {
			return false;
		}
		return Object.keys( value ).sort().join( '|' ) === keys.slice().sort().join( '|' );
	}

	function frameForMessage( event, instanceId ) {
		var frames = document.querySelectorAll( '.studiocount-bookings__frame[data-studiocount-instance]' );
		for ( var index = 0; index < frames.length; index += 1 ) {
			var frame = frames[ index ];
			if (
				frame.getAttribute( 'data-studiocount-instance' ) === instanceId &&
				frame.getAttribute( 'data-studiocount-origin' ) === event.origin &&
				frame.contentWindow === event.source
			) {
				return frame;
			}
		}
		return null;
	}

	function safeNavigationUrl( value ) {
		var destination;
		try {
			destination = new URL( String( value || '' ) );
		} catch {
			return '';
		}

		if (
			'https:' !== destination.protocol ||
			destination.username ||
			destination.password
		) {
			return '';
		}

		if (
			serviceOrigin === destination.origin &&
			'/checkout-return' === destination.pathname
		) {
			return destination.href;
		}

		if (
			'https://checkout.stripe.com' === destination.origin &&
			0 === destination.pathname.indexOf( '/c/pay/' )
		) {
			return destination.href;
		}

		return '';
	}

	function markReady( frame ) {
		var wrapper = frame.closest( '.studiocount-bookings' );
		if ( ! wrapper ) {
			return;
		}
		wrapper.classList.add( 'studiocount-bookings--ready' );
		var status = wrapper.querySelector( '.studiocount-bookings__status' );
		if ( status ) {
			status.textContent = '';
		}
	}

	function receiveMessage( event ) {
		if ( serviceOrigin !== event.origin ) {
			return;
		}

		var message = event.data;
		if (
			! message ||
			source !== message.source ||
			version !== message.version ||
			! Object.prototype.hasOwnProperty.call( exactKeys, message.type ) ||
			! hasExactKeys( message, exactKeys[ message.type ] ) ||
			'string' !== typeof message.instanceId
		) {
			return;
		}

		var frame = frameForMessage( event, message.instanceId );
		if ( ! frame ) {
			return;
		}

		if ( 'ready' === message.type ) {
			markReady( frame );
			return;
		}

		if ( 'resize' === message.type ) {
			if ( ! Number.isInteger( message.height ) || message.height < 320 || message.height > 12000 ) {
				return;
			}
			frame.height = String( message.height );
			markReady( frame );
			return;
		}

		if ( 'navigate' === message.type ) {
			var navigationUrl = safeNavigationUrl( message.url );
			if ( navigationUrl ) {
				window.location.assign( navigationUrl );
			}
		}
	}

	window.addEventListener( 'message', receiveMessage, false );
}() );
