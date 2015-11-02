A SAML2 auth plugin which is actually simple
--------------------------------------------

What is this?
=============

This plugin does authentication and that's it.

What isn't it?
==============


It does not do any user creation and assumes users are already provisioned
via some other means such as ldap sync or a custom enrolment plugin. In our
experience this is how almost all large uni's and corp's want it and it make
the most sense architecturally because students need to be in class lists
and groups well before they may ever log in.

Why is is better?
=================

* 100% configured in the Moodle GUI (no installation of a whole separate app)
* Minimal configuration needed, in most cases 2 urls (no XML wrangling)
* Fast! - 3 redirects instead of 7
* Supports back channel Single Logout which most big organisations require (unlike OneLogin)

How does it work?
=================

It completely embeds a SimpleSamlPhp instance which is dynamically configured
the way it should be and inherits almost all of it's configuration from moodle
configuration. In the future we should be able to swap to a differnt internal
SAML implementation and the plugin GUI shouldn't need to change at all.


== Embedded SSP ==

=== Moodle TODO ===

* have option for dual login - half copy from other saml plugin but use a hook instead
* option to strip or map domain?
* have option to allow login regardless of auth type

=== SimpleSamlPhp TODO ===

* lookup the moodle config and honour the debug settings here
* set timezone to moodle timezone
* point the logging handler to a custom logging handler that appends into moodle log, so only 1 log file to look into


