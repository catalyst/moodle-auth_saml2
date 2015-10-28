
== Embedded SSP ==

Embed simplesamlphp inside a moodle auth plugin 

* there is already the shiboleth plugin in moodle, we could put it inside that but perhaps it's better to fork it under a different name eg auth_saml2
* add the whole source as a submodule with a submodule so we can better track upstream changes in the future

OR

* actually leaning towards a fresh clean plugin, the shib plugin has an awful lot of baggage in there which we don't need or want

=== Moodle todo ===


* Clone from shibboleth and rename
** fix up shitty code guidelines errors in whole plugin

/auth/saml2/config.php
* in moodle make a config page:
** have a url for the IDP discovery XML file <- this is usally all that is needed
** have fields for the various idp endpoints. If the IDP xml is loaded it should pull it apart and put each url into these fields, otherwise you can add them manually
** have a debug mode checkbox / dropdown
** have a file upload for cert pair, save this to moodle site data
** have a convenience link from config page to the simple saml SP xml metadata page which you will need to give to the IDP admin for whitelist
** add a test config method / page which dumps raw saml return values for easier debugging in browser, this will show if saml is working outside of moodle

/auth/saml2/auth.php


* Add a pre_login_hook to remove 1 redirect on the moodle side to make it faster
* don't redirect to SSP, redirect to the upstream IDP with the SSP return url pre-formatted to remove another redirect

=== SimpleSamlPhp todo ===

Pretty sure we can do everything below WITHOUT touching the SSP source at all (keep core clean!) by setting the static SimpleSAML_Configuration::setConfigDir($configdir); early somewhere in moodle land before we do stuff.

/metadata

* make a page inside moodle which sets up the SSP config and then proxy's the metadata page

/config/config.php

HINT: use define('ABORT_AFTER_CONFIG', true); and friends (ABORT_AFTER_CONFIG_CANCEL) to load the two configs side by side where needed

* in SSP /config/config.php point all the paths to where they should be, ie tmp dir inside moodle tmp dir, cert dir inside moodledata
* lookup the moodle config and honour the debug settings here
* repeat the moodle admin contacts here for the saml contacts
* set timezone to moodle timezone
* point the logging handler to a custom logging handler that appends into moodle log, so only 1 log file to look into
* turn on SP, turn of other things (or put these under more config options inside moodle)
* session handling should be memcache, need to expose these settings in moodle

/config/authsources.php

* idp url lookup from moodle config, could be url of idp service discovery xml, or could just be a static hard coded string

/metadata/saml20-idp-remote.php

* lookup meta data from idp metadata url xml file and cache it / store it in config vars


