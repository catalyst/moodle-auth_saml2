A SAML2 auth plugin which is actually simple
============================================

What is this?
-------------

This plugin does authentication and that's it.

What isn't it?
--------------

It does not do any user creation and assumes users are already provisioned
via some other means such as ldap sync or a custom enrolment plugin. In our
experience this is how almost all large uni's and corp's want it and it make
the most sense architecturally because students need to be in class lists
and groups well before they may ever log in.

Why is is better?
-----------------

* 100% configured in the Moodle GUI (no installation of a whole separate app)
* Minimal configuration needed, in most cases 2 urls (no XML wrangling)
* Fast! - 3 redirects instead of 7
* Supports back channel Single Logout which most big organisations require (unlike OneLogin)

How does it work?
-----------------

It completely embeds a SimpleSamlPhp instance as an internal dependancy which
is dynamically configured the way it should be and inherits almost all of it's
configuration from moodle configuration. In the future we should be able to
swap to a differnt internal SAML implementation and the plugin GUI shouldn't
need to change at all.

Other SAML plugins
------------------

The diversity and variable quality and features of SAML moodle plugins is a
reflection of a great need for a solid SAML plugin, but the negligence to do
it properly in core. SAML2 is by far the most robust and supported protocol
across the internet and should be fully integrated into moodle core as both
a Service Provider and as an Identity Provider, and without any external
dependancies to manage.

Here is a quick run down of the alternatives:

**Core:**

/auth/shibboleth - This requires a separate Shibolleth install

**Plugins:**

* https://moodle.org/plugins/view/auth_saml - This requires a separate SimpleSamlPHP install

* https://moodle.org/plugins/view/auth_zilink_saml - This is a custom fork of above

* https://github.com/piersharding/moodle-auth_saml - This is another custom fork of above

* https://moodle.org/plugins/view/auth_onelogin_saml - This one uses it's own embedded
saml library which is great and promising, however it doesn't support 'back channel logout'
which is critical for security in any large organisation.



=== Moodle TODO ===

* have option for dual login - half copy from other saml plugin but use a hook instead
* option to strip or map domain?
* have option to allow login regardless of auth type

=== SimpleSamlPhp TODO ===

* lookup the moodle config and honour the debug settings here
* set timezone to moodle timezone
* point the logging handler to a custom logging handler that appends into moodle log, so only 1 log file to look into


