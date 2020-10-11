( function() {

    var restartFriendlyCaptchaWidget = function() {
        window.friendlyChallenge.autoWidget.start();
    }

	document.addEventListener( 'DOMContentLoaded', function( event ) {
        document.addEventListener( 'wpcf7mailsent',
            restartFriendlyCaptchaWidget
        );
        document.addEventListener( 'wpcf7mailfailed',
            restartFriendlyCaptchaWidget
		);
        document.addEventListener( 'wpcf7spam',
            restartFriendlyCaptchaWidget
        );
    } );
})();