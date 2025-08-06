# ADFS Module

Enables AD FS IdP
Compatible with VS 2012 Identity and Access

Basic Setup Companion based on [SimpleSAMLphp IDP configuration][docs]

1. Enabling the Identity Provider functionality

   In config/config.php, the option will be:
   'enable.adfs-idp' => true

2. Authentication module

   Follow as is.

3. Configuring the authentication module

   Next thing you need to do is to enable the module: in `config.php`,
   search for the `module.enable` key and set `adfs` to true:

   ```php
       'module.enable' => [
            'adfs' => true,
            …
       ],
   ```

4. Configuring the IdP

   ADFS IdP is configured by metadata stored in /metadata/adfs-idp-hosted.php
   and metadata/adfs-sp-remote.php

   If they are not present, copy them from /metadata-templates to the metadata
   directory.

5. Using the uri NameFormat on attributes

   WS-FED likes a few parameters to be very specifically named. This is
   especially true if .net clients will be treating this as a Microsoft ADFS
   IdP.

   The recommended settings for /metadata/adfs-idp-hosted.php is:

   ```php
       'authproc' => [
            // Convert LDAP names to WS-Fed Claims.
            100 => ['class' => 'core:AttributeMap', 'name2claim'],
       ],
   ```

6. Adding SPs to the IdP

   The minimal configuration for /metadata/adfs-sp-remote.php is:

   ```php
   $metadata['urn:federation:localhost'] = [
       'prp' => 'https://localhost/adfs/ls/',
   ];
   ```

7. Creating a SSL self signed certificate

   Follow as is.

8. Adding this IdP to other SPs

   Metadata should be available from /module.php/adfs/idp/metadata.php

9. This module tries its best to emulate a Microsoft ADFS endpoint, and as
   such, it is simplest to test using a .net client.

   To build the test client, follow the tutorial from [Microsoft][ms_docs].

   This will build a .net app that uses a dev machine running STS (their name for
   an IdP).

   To point to your SimpleSamlPHP ADFS IdP, in VS 2012:

   a. Right-click the project in Solution Explorer and select the Identity and
      Access option.

   b. In the Identity and Access Window, Select Use a business identity
      provider.

   c. Under “Enter the path to the STS metadata document” enter the url you have

      from step 8. Something like
      `https://.../module.php/adfs/idp/metadata.php`

   d. Click Ok

For more information in regards to [.NET][dotnet]

[dotnet]: http://msdn.microsoft.com/en-us/library/hh377151.aspx
[docs]: http://simplesamlphp.org/docs/stable/simplesamlphp-idp
[ms_docs]: http://code.msdn.microsoft.com/Claims-Aware-Web-d94a89ca
