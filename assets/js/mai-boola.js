( function() {
	const toggle = document.querySelector( '#maiboola-toggle' );

	toggle.addEventListener( 'click', event => {
		const entryContent = document.querySelector( '.entry-content-single' );
		const maiBoola     = event.target.closest( '.maiboola' );

		if ( entryContent ) {
			entryContent.classList.add( 'maiboola-content-full' );
			entryContent.classList.remove( 'maiboola-content-hidden' );
		}

		if ( maiBoola ) {
			maiBoola.classList.add( 'maiboola-conceal-hidden' );
		}
	});
} )();
