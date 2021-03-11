
document.addEventListener('DOMContentLoaded', event => {
	conditionallyRunRecaptcha();
});


/**
 * If consent api is active:
 *      remove "accept marketing cookies" notice and run recaptcha when marketing cookies are accepted
 * Else
 *      run recaptcha
 */
function conditionallyRunRecaptcha(){
	if (cf7_consent_api_active()) {
        document.addEventListener( "wp_listen_for_consent_change", function (e) {
            var changedConsentCategory = e.detail;
            for (var key in changedConsentCategory) {
                if (changedConsentCategory.hasOwnProperty(key)) {
                    if (key === 'marketing' && changedConsentCategory[key] === 'allow') {
                        remove_blocked_content_notice()
                        runReCaptcha()
                    }
                }
            }
        });
	} else {
		runReCaptcha();
	}

}


function cf7_consent_api_active(){
    return typeof wp_has_consent == 'function';
}


/**
 * Google recaptcha script is added as type="text/plain" so it doesn't get executed imidiately
 * We change that back to type="text/javascript" and run the script
 */
function runReCaptcha(){
	var handle = document.getElementById('google-recaptcha-js');
    var src = handle.getAttribute('src');
    if (src && src.length) {
        handle.setAttribute('type', 'text/javascript');
        getScript(src, runInlineRecaptcha);
    }
}


/**
 * Executes a script with source, run callback when completed
 * @param source
 * @param callback
 */
function getScript(source, callback) {
    var script = document.createElement('script');
    var prior = document.getElementsByTagName('script')[0];
    script.async = 1;

    script.onload = script.onreadystatechange = function( _, isAbort ) {
        if(isAbort || !script.readyState || /loaded|complete/.test(script.readyState) ) {
            script.onload = script.onreadystatechange = null;
            script = undefined;

            if(!isAbort && callback) setTimeout(callback, 0);
        }
    };

    script.src = source;
    prior.parentNode.insertBefore(script, prior);
}


function runInlineRecaptcha() {
    wpcf7_recaptcha = {
        ...( wpcf7_recaptcha ?? {} ),
    };

    const siteKey = wpcf7_recaptcha.sitekey;
    const { homepage, contactform } = wpcf7_recaptcha.actions;

    const execute = options => {
        const { action, func, params } = options;

        grecaptcha.execute( siteKey, {
            action,
        } ).then( token => {
            const event = new CustomEvent( 'wpcf7grecaptchaexecuted', {
                detail: {
                    action,
                    token,
                },
            } );

            document.dispatchEvent( event );
        } ).then( () => {
            if ( typeof func === 'function' ) {
                func( ...params );
            }
        } ).catch( error => console.error( error ) );
    };

    grecaptcha.ready( () => {
        execute( {
            action: homepage,
        } );
    } );

    document.addEventListener( 'change', event => {
        execute( {
            action: contactform,
        } );
    } );

    if ( typeof wpcf7 !== 'undefined' && typeof wpcf7.submit === 'function' ) {
        const submit = wpcf7.submit;

        wpcf7.submit = ( form, options = {} ) => {
            execute( {
                action: contactform,
                func: submit,
                params: [ form, options ],
            } );
        };
    }

    document.addEventListener( 'wpcf7grecaptchaexecuted', event => {
        const fields = document.querySelectorAll(
            'form.wpcf7-form input[name="_wpcf7_recaptcha_response"]'
        );

        fields.forEach( field => {
            field.setAttribute( 'value', event.detail.token );
        } );
    } );
}


/**
 * Remove html of the blocked content notice above Submit button
 */
function remove_blocked_content_notice() {
    var blocked_content_notice = document.getElementsByClassName('wpcf7-blocked-content-notice')[0];
    if ( blocked_content_notice ) {
        blocked_content_notice.parentElement.remove();
    }
}
