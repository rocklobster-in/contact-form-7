//enable async loading of script handle "google-recaptcha"

//timing check
const grecaptchaWaitPromise = ms => new Promise(res => setTimeout(res, ms));

//check for defined grecaptcha or wait
const maybeWaitForGrecaptchaDefined = async () => {

	await grecaptchaWaitPromise(300);

	if(typeof grecaptcha === "undefined"){
		maybeWaitForGrecaptchaDefined();
	}else{
		wpcf7_recaptcha_callback();
	}
	
}

//run
maybeWaitForGrecaptchaDefined();

//extracted original inline function
//document.addEventListener("DOMContentLoaded", wpcf7_recaptcha_callback());
function wpcf7_recaptcha_callback(t) {
	
    var e;
    wpcf7_recaptcha = { ...(null !== (e = wpcf7_recaptcha) && void 0 !== e ? e : {}) };
    const c = wpcf7_recaptcha.sitekey,
        { homepage: n, contactform: a } = wpcf7_recaptcha.actions,
        o = (t) => {
            const { action: e, func: n, params: a } = t;
            grecaptcha
                .execute(c, { action: e })
                .then((t) => {
                    const c = new CustomEvent("wpcf7grecaptchaexecuted", { detail: { action: e, token: t } });
                    document.dispatchEvent(c);
                })
                .then(() => {
                    "function" == typeof n && n(...a);
                })
                .catch((t) => console.error(t));
        };
    if (
        (grecaptcha.ready(() => {
            o({ action: n });
        }),
        document.addEventListener("change", (t) => {
            o({ action: a });
        }),
        "undefined" != typeof wpcf7 && "function" == typeof wpcf7.submit)
    ) {
        const t = wpcf7.submit;
        wpcf7.submit = function (e) {
            let c = arguments.length > 1 && void 0 !== arguments[1] ? arguments[1] : {};
            o({ action: a, func: t, params: [e, c] });
        };
    }
    document.addEventListener("wpcf7grecaptchaexecuted", (t) => {
        const e = document.querySelectorAll('form.wpcf7-form input[name="_wpcf7_recaptcha_response"]');
        for (let c = 0; c < e.length; c++) e[c].setAttribute("value", t.detail.token);
    });
}
