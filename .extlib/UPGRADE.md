Upgrade Simplesaml lib
====================


SimpleSAMLphp
-------------
# Manually build simplesaml
We need to manually build simplesaml sometimes as the release contains dependencies we don't want.

### Get upstream simplesaml.
Make sure to checkout the latest version tag
```bash
git clone git@github.com:simplesamlphp/simplesamlphp.git simplesamlphp
cd simplesamlphp
git checkout v2.3.3
```
### Install composer
[Composer Install Guide](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-macos)

### Install the external dependencies (excluding dev)
Make sure you run the install with "--no-dev" as below.
Remove any depdnencies we don't want.
```bash
composer install --no-dev
composer remove phpmailer/phpmailer
```

### Clean Files
See the cleaning step in the [workflow](https://github.com/simplesamlphp/simplesamlphp/blob/master/.github/workflows/build-release.yml), At the time of writing this deletes everything referenced in `.gitattributes`. We also remove
* `composer.json`
* `composer.lock`
* `modules/.gitignore`
### Copy into auth_saml2
Copy the updated simplesaml files into auth/saml/.extlib/simplesaml.

enter the simplesaml folder and run the following to fix file permission for Totara:
```bash
find . -type f -exec chmod 644 -- {} +
```
Commit the changes directly into the repo.

### Cherry-pick previous changes.
Look at the previous history on /.extlib/simplesaml and cherry-pick our custom changes.


### Make sure you fix README.md

Update the version of SSP in the supported branches. Make sure you do this is ALL supported branches
even if you have only updated SSP in one branch. It shouldn't matter which README you look at they
should be consistent.

# Testing locally
1> Set up IDP locally as suggested here: https://simplesamlphp.org/docs/stable/simplesamlphp-idp

**IDP Settings:**
config.php - double check 'baseurlpath' is set correctly
authsources.php - fields mapping can be as below:
```
$config = [
    'example-userpass' => [
        'exampleauth:UserPass',
        'student:studentpass' => [
            'uid' => ['student'],
            'email'=> ['student@yahoo.com'],
            'firstname' => ['StudFname'],
            'lastname' => ['StudLname'],
            'eduPersonAffiliation' => ['member', 'student'],
        ],
        'employee:employeepass' => [
            'uid' => ['employee'],
            'email'=> ['emp@yahoo.com'],
            'firstname' => ['EmpFname'],
            'lastname' => ['EmpLname'],
            'eduPersonAffiliation' => ['member', 'employee'],
        ],
    ],
];
```
2> Add below rules to nginx
```
    # deny dot-files
    location ~ /\. {
        deny all;
        access_log off;
        log_not_found off;
    }
```
3> Once upgrade is done and cherry-pick commits are applied, integrate with moodle
**Settings to check on moodle**

 - /admin/settings.php?section=httpsecurity
    cookiesecure = false

 - /admin/settings.php?section=authsettingsaml2
    auth_saml2 | autocreate = Yes

    Under Data mapping - map few fields
    Firstname
    Lastname
    Email

4> Fix whatever is not working after step 3.
5> Good to have commits in order as below:
- Library upgrade with version tag
- Library patches/cherry-picked/manually applied changes
- doc changes - README, Travis, version.php, any other
