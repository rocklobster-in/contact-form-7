(function() {
    var resetFriendlyCaptchaWidget = function() {
        window.friendlyChallenge.autoWidget.reset();
    }
	document.addEventListener( 'DOMContentLoaded', function( event ) {
        document.addEventListener( 'wpcf7mailsent',
            resetFriendlyCaptchaWidget
        );
        document.addEventListener( 'wpcf7mailfailed',
            resetFriendlyCaptchaWidget
		);
        document.addEventListener( 'wpcf7spam',
            resetFriendlyCaptchaWidget
        );
    });
})();