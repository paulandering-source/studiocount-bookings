( function () {
	'use strict';

	function init() {
		var button = document.getElementById( 'studiocount-bookings-check' );
		var result = document.getElementById( 'studiocount-bookings-check-result' );
		var config = window.StudioCountBookingsAdmin || {};

		if ( ! button || ! result || ! config.ajaxUrl || ! config.nonce ) {
			return;
		}

		button.addEventListener( 'click', function () {
			var originalLabel = button.textContent;
			var body = new FormData();
			body.append( 'action', 'studiocount_bookings_check_connection' );
			body.append( 'nonce', config.nonce );
			button.disabled = true;
			button.textContent = config.checking || 'Checking…';
			result.className = 'studiocount-bookings-admin__result';
			result.textContent = '';

			fetch( config.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				body: body
			} )
				.then( function ( response ) {
					return response.json();
				} )
				.then( function ( response ) {
					var message = response && response.data && response.data.message;
					if ( ! response || 'string' !== typeof message ) {
						throw new Error( 'connection_failed' );
					}
					result.classList.add( true === response.success ? 'is-success' : 'is-error' );
					result.textContent = message;
				} )
				.catch( function () {
					result.classList.add( 'is-error' );
					result.textContent = config.failed || 'StudioCount could not be reached. Try again shortly.';
				} )
				.finally( function () {
					button.disabled = false;
					button.textContent = originalLabel;
				} );
		} );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
}() );
