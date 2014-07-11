=== PMPro KISSmetrics ===
Contributors: strangerstudios, jessica o
Tags: pmpro, kissmetrics, analytics, tracking, metrics, user data, users, signup
Requires at least: 3.8
Tested up to: 3.9.1
Stable tag: .1.1

Integrates your WordPress site with KISSmetrics to track meaningful user data, with or without Paid Memberships Pro.

== Description ==

Integrates your WordPress site with KISSmetrics to track meaningful user data, with or without Paid Memberships Pro.

PMPro KISSmetrics tracks the following events and user properties:
* User registers
* User logs in
* Properties: Username, Email, Display Name, and User ID
* Identif in KISSmetrics by user's username, email address or display name.

If PMPro is installed, the following additional events and user properties can be tracked:
* User visits any PMPro front end page
* User changes membership level
* User checks out
* User starts trial
* Properties: Membership Level, Last Order ID, Checkout Total

Each event can be enabled or disabled on the PMPro KISSmetrics Settings page.

Features:

* Track powerful user data enabling you to follow EXACTLY what your customers are doing, in an easy to read format.
* Adds specific WordPress and PMPro properties to your KISSmetrics Customers.
* Works with or without Paid Memberships Pro.

== Installation ==

1. Upload the `pmpro-kissmetrics` directory to the `/wp-content/plugins/` directory of your site.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Add your custom API key and JavaScript Tracking Code and configure which events you want tracked on the PMPro KISSmetrics settings page.

== Frequently Asked Questions ==

= I found a bug in the plugin. =

Please post it in the issues section of GitHub and we'll fix it as soon as we can. Thanks for helping. https://github.com/strangerstudios/pmpro-kissmetrics/issues

== Changelog ==
= .1.1 =
* Added option to add the total cost as a property on checkout.
* Added select menu to choose how to identify users in KISSmetrics. Options are username, email, and display name.

= .1 =
* This is the initial version of the plugin.