
== Embedded SSP ==

=== Moodle todo ===

* have option for dual login - half copy from other saml plugin but use a hook instead
* option to strip or map domain?
*


/auth/saml2/config.php
* in moodle make a config page:
* have a file upload for cert pair, save this to moodle site data

* Add a preloginhook to remove 1 redirect on the moodle side to make it faster
* don't redirect to SSP, redirect to the upstream IDP with the SSP return url pre-formatted to remove another redirect

=== SimpleSamlPhp todo ===


HINT: use define('ABORT_AFTER_CONFIG', true); and friends (ABORT_AFTER_CONFIG_CANCEL) to load the two configs side by side where needed

* lookup the moodle config and honour the debug settings here
* set timezone to moodle timezone
* point the logging handler to a custom logging handler that appends into moodle log, so only 1 log file to look into


