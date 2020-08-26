=== Contact Form 7 ===
Contributors: takayukister
Donate link: https://contactform7.com/donate/
Tags: contact, form, contact form, feedback, email, ajax, captcha, akismet, multilingual
Requires at least: 5.3
Tested up to: 5.5
Stable tag: 5.2.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Just another contact form plugin. Simple but flexible.

== Description ==

Contact Form 7 can manage multiple contact forms, plus you can customize the form and the mail contents flexibly with simple markup. The form supports Ajax-powered submitting, CAPTCHA, Akismet spam filtering and so on.

= Docs and support =

You can find [docs](https://contactform7.com/docs/), [FAQ](https://contactform7.com/faq/) and more detailed information about Contact Form 7 on [contactform7.com](https://contactform7.com/). When you can't find the answer to your question on the FAQ or in any of the documentation, check the [support forum](https://wordpress.org/support/plugin/contact-form-7/) on WordPress.org. If you can't locate any topics that pertain to your particular issue, post a new topic for it.

= Contact Form 7 needs your support =

It is hard to continue development and support for this free plugin without contributions from users like you. If you enjoy using Contact Form 7 and find it useful, please consider [making a donation](https://contactform7.com/donate/). Your donation will help encourage and support the plugin's continued development and better user support.

= Privacy notices =

With the default configuration, this plugin, in itself, does not:

* track users by stealth;
* write any user personal data to the database;
* send any data to external servers;
* use cookies.

If you activate certain features in this plugin, the contact form submitter's personal data, including their IP address, may be sent to the service provider. Thus, confirming the provider's privacy policy is recommended. These features include:

* reCAPTCHA ([Google](https://policies.google.com/?hl=en))
* Akismet ([Automattic](https://automattic.com/privacy/))
* Constant Contact ([Endurance International Group](https://www.endurance.com/privacy))

= Recommended plugins =

The following plugins are recommended for Contact Form 7 users:

* [Flamingo](https://wordpress.org/plugins/flamingo/) by Takayuki Miyoshi - With Flamingo, you can save submitted messages via contact forms in the database.
* [Bogo](https://wordpress.org/plugins/bogo/) by Takayuki Miyoshi - Bogo is a straight-forward multilingual plugin that doesn't cause headaches.

= Translations =

You can [translate Contact Form 7](https://contactform7.com/translating-contact-form-7/) on [translate.wordpress.org](https://translate.wordpress.org/projects/wp-plugins/contact-form-7).

== Installation ==

1. Upload the entire `contact-form-7` folder to the `/wp-content/plugins/` directory.
1. Activate the plugin through the **Plugins** screen (**Plugins > Installed Plugins**).

You will find **Contact** menu in your WordPress admin screen.

For basic usage, have a look at the [plugin's website](https://contactform7.com/).

== Frequently Asked Questions ==

Do you have questions or issues with Contact Form 7? Use these support channels appropriately.

1. [Docs](https://contactform7.com/docs/)
1. [FAQ](https://contactform7.com/faq/)
1. [Support forum](https://wordpress.org/support/plugin/contact-form-7/)

[Support](https://contactform7.com/support/)

== Screenshots ==

1. screenshot-1.png

== Changelog ==

For more information, see [Releases](https://contactform7.com/category/releases/).

= 5.2.2 =

* Fixed: A REST API call aborted with a PHP fatal error when the `WPCF7_USE_PIPE` constant value was false.
* Introduces the `wpcf7_doing_it_wrong()` function.
* Sets the `trigger_error()` function’s `$error_type` parameter explicitly.
* Makes the `wpcf7_special_mail_tags` filter functions’ `$mail_tag` parameter optional.

= 5.2.1 =

* Makes the [contact-form-7 404 "Not Found"] message localizable.
* REST API: Adds the `permission_callback` argument to every endpoint definition.
* Flamingo: Uses `id()` instead of `id`, if available.
* Fixed: The `free_text` option did not work correctly with the `exclusive` option.
* Applies `wpcf7_mail_tag_replaced` filters even when the `$posted_data` is null.
* Adds custom mail-tag replacement for quiz fields.
* Admin: Updates the date column format in the list table.

= 5.2 =

* Submission: Introduces the `$posted_data_hash` and `$skip_spam_check` properties.
* Submission: Introduces the `wpcf7_skip_spam_check` filter hook.
* Contact form: Introduces the `pref()` method.
* REST API: Adds parsed form-tags data to the response.
* REST API: Deprecates the `wpcf7_ajax_json_echo` and `wpcf7_ajax_onload` filter hooks and introduces the `wpcf7_feedback_response` and `wpcf7_refill_response` filter hooks as alternatives.
* Frontend CSS: Style rules for the response output refer to the `form` element’s `class` attribute.
* Frontend JavaScript: Abolishes the use of jQuery events.
* reCAPTCHA: Moves script code to a separate file.
* reCAPTCHA: Changes the name of the field for reCAPTCHA response token from `g-recaptcha-response` to `_wpcf7_recaptcha_response`.

= 5.1.9 =

* Special mail-tags: Reflects WP timezone to `[_date]` and `[_time]` mail-tags.
* WPCF7_FormTag: Reflects WP timezone to `get_date_option()` output.
* User input validation: Strictly compares to boolean _false_.

= 5.1.8 =

* reCAPTCHA: Shows no warning on upgrading from v2 if the global sitekey is defined.
* reCAPTCHA: Improves the frontend JavaScript coding.
* Accessibility: Improves the response message markup.
* Fixes the regular expression pattern in `wpcf7_is_tel()`.
* Fixed: Character count was not reset after a successful submission.
* Fixed: The fourth parameter of the `wpcf7_special_mail_tags` filter hook was not correctly set.

= 5.1.7 =

* CSS: Adds an explicit LTR direction style rule for code inputs.
* Accessibility: Uses _Error_ instead of _ERROR_ in warnings.

= 5.1.6 =

* CSS: removes a style rule from the stylesheet that was unnecessary and conflicting with Twenty Twenty’s rules.
* REST API: retrieves the contact form ID explicitly from the route parameters.

= 5.1.5 =

* Config Validator: New test item for the unavailable_html_elements error.
* Config Validator: New test item for the attachments_overweight error.

= 5.1.4 =

* reCAPTCHA: introduces the WPCF7_RECAPTCHA_SITEKEY and WPCF7_RECAPTCHA_SECRET constants.
* reCAPTCHA: Introduces the wpcf7_recaptcha_sitekey and wpcf7_recaptcha_secret filter hooks.
* Adds $status parameter to the wpcf7_form_response_output filter.
* Creates a nonce only when the submitter is a logged-in user.
* Introduces WPCF7_ContactForm::unit_tag(), a public method that returns a unit tag.
* reCAPTCHA: gives a different spam log message for cases where the response token is empty.
* Acceptance Checkbox: supports the label_first option in an acceptance form-tag.

= 5.1.3 =

* Fixes a bug making it unable to unselect an option in the Mail tab panel.

= 5.1.2 =

* Constant Contact: Introduces the contact list selector.
* Constant Contact: Introduces the constant_contact additional setting.
* reCAPTCHA: Introduces the wpcf7_recaptcha_actions and wpcf7_recaptcha_threshold filter hooks.

= 5.1.1 =

* reCAPTCHA: Modifies the reaction to empty response tokens.

= 5.1 =

* Introduces the Constant Contact integration module.
* Updates the reCAPTCHA module to support reCAPTCHA v3.
* Adds Dark Mode style rules.

== Upgrade Notice ==
