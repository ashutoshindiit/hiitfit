=== MemberPress MultiCurrency ===
Contributors: davexpression
Donate link: https://example.com/
Tags: comments, spam
Requires at least: 4.6
Tested up to: 5.4
Stable tag: 1.5.1
Requires PHP: 5.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds multi-currency option to MemberPress

== Description ==

MemberPress MultiCurrency gives you the currency flexibility you have ever desired with MemberPress. You can now sell your memberships to your global customers in more than one currency with the ease of a currency switcher.

== Installation ==

This section describes how to install the plugin and get it working.

e.g.

1. Upload the plugin files to the `/wp-content/plugins` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Use the Settings->Plugin Name screen to configure the plugin
1. (Make your instructions match the desired user flow for activating and installing your plugin. Include any steps that might be needed for explanatory purposes)


== Frequently Asked Questions ==

= Why can’t I see the currency switcher? =

Make sure that you select appropriate field position in _MemberPress -> Options -> General_ page in your WordPress admin

= Do I require MemberPress plugin to use this plugin? =

Yes, please make sure that you have downloaded and activated MemberPress

== Screenshots ==


== Changelog ==

= 1.5.7 =
* Fix pricing on pro-rated options

= 1.5.5 =
* Make sure currency conversion works on SPC with Coupon Code
* Make sure right currency symbol appears in emails

= 1.5.4 =

= 1.5.3 =
* Fixed bugs in non Single Page Checkout (SPC) currency issues
* Added support for SPC invoice.

= 1.5.1 =
* Added ExchangeAPI service provider
* Another change.

= 1.5 =
* Fixed header already sent error
* Removed unused DB table creation code
* Support for AED/TWD
* Added .po translation file

1.4
* Fixed: SPC Stripe currency switch not working

1.3

* Plugin updater now works
* Makes plugin compatible with MemberPress 1.8
* Updated currency list to include CNY and others
* Stripe, PayPal Express and PayPal Standard works

= 1.0 =
* Initial release