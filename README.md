<a href="https://travis-ci.org/catalyst/moodle-auth_saml2">
<img src="https://travis-ci.org/catalyst/moodle-auth_saml2.svg?branch=master">
</a>

https://moodle.org/plugins/auth_saml2

100% Moodle SAML fast, simple, secure
=====================================

![Churchill quote](/pix/churchill.jpg?raw=true)

* [What is this?](#what-is-this)
* [Why is it better?](#why-is-it-better)
* [How does it work?](#how-does-it-work)
* [Features](#features)
* [Installation](#installation)
* [Configuration](#configuration)
* [Testing](#testing)
* [Debugging](#debugging)
* [Gotchas](#gotchas)
* [Other SAML plugins](#other-saml-plugins)
* [Support](#support)
* [Warm thanks](#warm-thanks)

What is this?
-------------

This plugin does authentication, user auto creation with field mapping.


Why is it better?
-----------------

* 100% configured in the Moodle GUI - no installation of a whole separate app,
  and no touching of config files or generating certificates.
* Minimal configuration needed, in most cases just copy the IdP metadata in
  and then give the SP metadata to your IdP admin and that's it.
* Fast! - 3 redirects instead of 7
* Supports back channel Single Logout which most big organisations require (unlike OneLogin)


How does it work?
-----------------

It completely embeds a SimpleSamlPHP instance as an internal dependancy which
is dynamically configured the way it should be and inherits almost all of it's
configuration from Moodle configuration. In the future we should be able to
swap to a different internal SAML implementation and the plugin GUI shouldn't
need to change at all.

* SimpleSAMLphp version 1.14.10


Features
--------

* Dual login VS forced login for all as an option, with ?saml=off on the login
  page for manual accounts, and ?saml=on supported everywhere to deep link and
  force login via saml if dual auth is on.
* SAML attributes to Moodle user field mapping
* Automatic certificate creation
* Optionally auto create users

Features not yet implemented:

* Enrolment - this should be an enrol plugin and not in an auth plugin
* Role mapping - not yet implemented


Installation
------------

1. Install and enable php-mcrypt. On debian / ubuntu this may look like

   ```sh
   sudo apt-get install php5-mcrypt
   sudo php5enmod mcrypt 
   sudo service apache2 restart
   ```
   
2. Install the plugin the same as any standard moodle plugin either via the
Moodle plugin directory, or you can use git to clone it into your source:

   ```sh
   git clone git@github.com:catalyst/moodle-auth_saml2.git auth/saml2
   ```

   Or install via the Moodle plugin directory:
    
   https://moodle.org/plugins/auth_saml2

3. Then run the Moodle upgrade

4. If your IdP has a publicly available XML descriptor, copy it's url into
   the SAML2 auth config settings page. Otherwise copy the XML verbatum into
   the settings textarea instead.
   
5. If your IdP requires whitelisting each SP then in the settings page is
   links to download the XML, or you can provide that url to your IdP
   administrator.

For most simple setups this is enough to get authentication working, there are
many more settings to define how to handle new accounts, dual authentication,
and to easily debug the plugin if things are not working.


Configuration
-------------

Most of the configuration is done in the Moodle admin GUI and should be self
explanatory for someone familiar with SAML generally. There are a few extra
configuration items which are currently don't have a GUI and should be added
to your moodle config.php file:

```php
$CFG->auth_saml2_disco_url = '';

$CFG->auth_saml2_store = '\\auth_saml2\\redis_store'; # Use an alternate store

$CFG->auth_saml2_redis_server = ''; # Required for the redis_store above

```


Testing
-------

This plugin has been tested against:

* SimpleSamlPHP set up as an IdP
* openidp.feide.no
* testshib.org
* An AAF instance of Shibboleth
* OpenAM (Sun / Oracle)
* Microsoft ADFS

To configure this against testshib you will need a moodle which is publicly
accessible over the internet. Turn on the SAML2 plugin and then configure it:

Home ► Site administration ► Plugins ► Authentication ► SAML2

1. Set the Idp URL to: https://www.testshib.org/metadata/testshib-providers.xml
2. Set dual auth to Yes
3. Set auto create users to Yes
4. Click on 'Download SP Metadata'
5. Save the settings
6. Upload that file to: https://www.testshib.org/register.html
7. Logout and login, you should see 'TestShib Test IdP' as an alternate login method
   and be able to login via the example credentials.

Debugging
---------

If you are having any issues, turn on debugging inside the SAML2 auth plugin, as well
as turning on the moodle level debugging. This will give in depth debugging on the SAML
xml and errors, as well as stack traces. Please include this in any github issue you
create if you are having trouble.

There is also a standalone test page which authenticates but isn't a 'moodle' page. All
this page does is echo the saml attributes which have been provided by the IDP. This can
be very handy for setting up the mappings, ie for when the IDP might be providing the
right attributes but under an unexpected key name.

```
/auth/saml2/test.php
```

If you can succesfully do a saml login using this page then is narrows down where the
issues lies. Some common issues are:

1) You received a valid set of saml attributes, but the attribute(s) needed are not
   present. ie often with say ADFS you may have to specify to 'release' the username.
    
2) You have got a valid set of attributes, but the key for the username isn't what
   you expected. Cut and paste the correct key name into the Moodle auth_saml2 config
   page to correctly map the 'idpattr' value.
   
3) The attribute key name might be a really crazy long looking string. This is common
   with ADFS. If that long string contains certain characters then moodle will not
   accept it, and this is an issue in Moodle itself and applies to all auth plugins.
   You can add a custom claim in ADFS to rename this attribute to something nicer.
   See this for more: https://github.com/catalyst/moodle-auth_saml2/issues/124
   
4) If it is bringing across all the attributes properly, but you are getting:
   "You have logged in succesfully as 'xyz' but do not have an account in Moodle"
   then you either need to change your user provisioning process to ensure users are
   created ahead of time, or you need to enable the 'autocreate' setting. If you do
   auto create then you need to be very careful that autocreated users, and users
   provisioned via other means, and consistently setup.


Gotchas
-------

**OpenAM**

If you are getting signature issues with OpenAM then you may need to manually
yank out the contents of the ds:X509Certificate element into a file and then
import it into OpenAM's certificate store:

```bash
$ cat moodle.edu.crt 
-----BEGIN CERTIFICATE-----
thesuperlongcertificatestringgoeshere=
-----END CERTIFICATE-----
$ keytool -import -trustcacerts -alias moodle.edu -file moodle.edu.crt -keystore keystore.jks
```

Then follow the prompts and restart OpenAM.

**Certificate Locking**

It is only possible to unlock the certificates via the command line.
These certificates are located in the $CFG->dataroot/saml2 directory.

To unlock the certificates please restore the write permissions to the required files.
```bash
$ cd $CFG->dataroot/saml2
$ chmod 0660 site.example.crt
$ chmod 0660 site.example.pem
```

**OpenSSL errors during certificate regeneration**

Some environments, particularly Windows-based, may not provide an OpenSSL
configuration file at the default location, producing errors like the
following when regenerating certificates:

```
error:02001003:system library:fopen:No such process
error:2006D080:BIO routines:BIO_new_file:no such file
error:0E064002:configuration file routines:CONF_load:system lib
```

To work around this, set the `OPENSSL_CONF` environment variable to the location
of [`openssl.cnf`](https://www.openssl.org/docs/manmaster/man5/config.html)
within your environment.


**OKTA configuration**

Okta has some weird names for settings which are confusing, this may help decipher them:

|Okta name|Sane name|Value|
|---|---|---|
|Single sign on URL|ACS URL|`https://example.com/auth/saml2/sp/saml2-acs.php/example.com`|
|Audience URI|Entity ID|`https://example.com/auth/saml2/sp/metadata.php`|
|Enable Single Log Out|Enable Single Log Out|True|
|Single Logout URL|Single Logout URL|`https://example.com/auth/saml2/sp/saml2-logout.php/example.com`|
|Assertion Encryption|Assertion Encryption|Encrypted|

Suggested attribute mappings:

|Name|Value|
|---|---|
|`Login`|`user.login`|
|`FirstName`|`user.firstName`|
|`LastName`|`user.lastName`|
|`Email`|`user.email`|



Other SAML plugins
------------------

The diversity and variable quality and features of SAML moodle plugins is a
reflection of a great need for a solid SAML plugin, but the neglect to do
it properly in core. SAML2 is by far the most robust and supported protocol
across the internet and should be fully integrated into moodle core as both
a Service Provider and as an Identity Provider, and without any external
dependencies to manage.

Here is a quick run down of the alternatives:

**Core:**

* /auth/shibboleth - This requires a separately installed and configured
  Shibbolleth install

One big issue with this, and the category below, is as there is a whole extra
application between moodle and the IdP, so the login and logout processes have
more latency due to extra redirects. Latency on potentially slow mobile
networks is by far the biggest bottle neck for login speed and the biggest
complaint by end users in our experience.

**Plugins that require SimpleSamlPHP**

These are all forks of each other, and unfortunately have diverged quite early
or have no common git history making it difficult to cross port features or
fixes between them.

* https://moodle.org/plugins/view/auth_saml

* https://moodle.org/plugins/view/auth_zilink_saml

* https://github.com/piersharding/moodle-auth_saml

**Plugins which embed a SAML client lib:**

These are generally much easier to manage and configure as they are standalone.

* https://moodle.org/plugins/view/auth_onelogin_saml - This one uses it's own
  embedded saml library which is great and promising, however it doesn't support
  'back channel logout' which is critical for security in any large organisation.

* This plugin, with an embedded and dynamically configured SimpleSamlPHP
  instance under the hood


Support
-------

If you have issues please log them in github here

https://github.com/catalyst/moodle-auth_saml2/issues

Please note our time is limited, so if you need urgent support or want to
sponsor a new feature then please contact Catalyst IT Australia:

https://www.catalyst-au.net/contact-us


Warm thanks
-----------

Thanks to the various authors and contributors to the other plugins above.

Thanks to LaTrobe university in Melbourne for sponsoring the initial creation
of this plugin:

http://www.latrobe.edu.au

![LaTrobe](/pix/latrobe.png?raw=true)

Thanks to Centre de gestion informatique de l’éducation in Luxembourg for
sponsoring the user autocreation and field mapping work:

http://www.cgie.lu

![CGIE](/pix/cgie.png?raw=true)

This plugin was developed by Catalyst IT Australia:

https://www.catalyst-au.net/

<img alt="Catalyst IT" src="https://cdn.rawgit.com/CatalystIT-AU/moodle-auth_saml2/master/pix/catalyst-logo.svg" width="400">

