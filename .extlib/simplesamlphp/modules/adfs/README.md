# ADFS Module

![Build Status](https://github.com/simplesamlphp/simplesamlphp-module-adfs/workflows/CI/badge.svg?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/simplesamlphp/simplesamlphp-module-adfs/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/simplesamlphp/simplesamlphp-module-adfs/?branch=master)
[![Coverage Status](https://codecov.io/gh/simplesamlphp/simplesamlphp-module-adfs/branch/master/graph/badge.svg)](https://codecov.io/gh/simplesamlphp/simplesamlphp-module-adfs)
[![Type coverage](https://shepherd.dev/github/simplesamlphp/simplesamlphp-module-adfs/coverage.svg)](https://shepherd.dev/github/simplesamlphp/simplesamlphp-module-adfs)

## Install

Install with composer

```bash
    vendor/bin/composer require simplesamlphp/simplesamlphp-module-adfs
```

## Configuration

Next thing you need to do is to enable the module: in
 `config.php`, search for the `module.enable` key and set `adfs` to true:

 ```php
     'module.enable' => [
          'adfs' => true,
          …
     ],
 ```

View samples in `metadata-templates` for defining your idp and any relying
parties/sps.

### Tips for admins new to WS-Fed

* A `realm` is similar to an entityId from SAML. `adfs-sp-remote.php` metadata
  array is based on `realm`. An IP STS is similar to an IdP.

* Some WS-Fed Relying Party applications want the assertion lifetime to be
  longer than the application's session lifetime. If not, the application will
  send the user to the IdP to login again, hoping for a longer lived assertion.
  SSP's default assertion lifetime is 5 minutes while SharePoint, by default,
  wants 10 minutes. Use the `assertion.lifetime` in `adfs-sp-remote.php` to set
  the time greater than that set in SharePoint (which can be configured by
  adjusting `LogonTokenCacheExpirationWindow`)
