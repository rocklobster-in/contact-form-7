let ctScrollCollected = false;
let	ctMouseMovedCollected = false;

function ctSetCookie( cookies, value, expires ){
	if( typeof cookies === 'string' && typeof value === 'string' || typeof value === 'number'){
		cookies = [ [ cookies, value, expires ] ];
	}

	cookies.forEach( function (item, i, arr	) {
		var expires = typeof item[2] !== 'undefined' ? "expires=" + expires + '; ' : '';
		var ctSecure = location.protocol === 'https:' ? '; secure' : '';
		document.cookie = item[0] + "=" + encodeURIComponent(item[1]) + "; " + expires + "path=/; samesite=lax" + ctSecure;
	});
}

function ctSetHasScrolled() {
	if (!ctScrollCollected) {
		ctSetCookie("ct_has_scrolled", 'true');
		ctScrollCollected = true;
	}
}

function ctSetMouseMoved() {
	if (!ctMouseMovedCollected) {
		ctSetCookie("ct_mouse_moved", 'true');
		ctMouseMovedCollected = true;
	}
}

window.addEventListener("scroll", ctSetHasScrolled);
window.addEventListener("mousemove", ctSetMouseMoved);

document.addEventListener( 'DOMContentLoaded', () => {
	// Collect scrolling info
	var initCookies = [
		["ct_has_scrolled", 'false'],
		["ct_mouse_moved", 'false'],
	];

	ctSetCookie(initCookies);
} );
