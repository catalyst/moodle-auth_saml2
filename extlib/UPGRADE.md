Upgrade Simplesaml lib
====================


SimpleSAMLphp
-------------
# Manually build simplesaml
We need to manually build simplesaml sometimes as the release contains dependencies we don't want.

### Get upstream simplesaml.
```bash
git clone git@github.com:simplesamlphp/simplesamlphp.git simplesamlphp

cd /var/simplesamlphp
cp -r config-templates/* config/
cp -r metadata-templates/* metadata/
```
### Install composer
```bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('sha384', 'composer-setup.php') === 'a5c698ffe4b8e849a443b120cd5ba38043260d5c4023dbf93e1558871f1f07f58274fc6f4c93bcfd858c6bd0775cd8d1') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"
```
### Remove any dependencies we don't want.
Hopefully you don't need to do this, but in the 1.17 build we removed symfony/polyfill-php70 from composer.lock.

### Install the external dependencies (excluding dev)
Make sure you run the install with "--no-dev" as below.
```bash
php composer.phar install --no-dev
npm install
npm run build
```
### Copy into auth_saml2
Copy the updated simplesaml files into auth/saml/extlib/simplesaml.
Exclude stuff like .gitignore, other hidden system files that shouldnt be needed - also exclude the composer.* files
enter the simplesaml folder and run the following to fix file permission for Totara:
```bash
find . -type f -exec chmod 644 -- {} +
```
Commit the changes directly into the repo.

### Cherry-pick previous changes.
Look at the previous history on extlib/simplesaml and cherry-pick our custom changes.

# Using a published release 
Sometimes you might get away with using a published release with the following instructions.

Do not copy & paste blindly, this is the general idea only.

```bash
cd auth/saml2/extlib

# We will delete stuff and compare later.
# Ensure everything is commited into git.
git checkout -b issueXXX_extlib-upgrade 
git status
```
 
```bash
rm -rf simplesamlphp

curl -L https://simplesamlphp.org/download?latest | tar vxz
mv simplesamlphp-1.15.4 simplesamlphp
rm -rf simplesamlphp/config
rm -rf simplesamlphp/metadata

git add simplesamlphp
git commit -m 'Issue #XXX - Updating with code from simplesamlphp-1.15.4' # Customise the message!

# Check what was customised when this document was updated, you may want to cherry pick it.
git show a32804374772709d65264ab823f8a3b3adfc6391  
  
# Analyse what is different, backport modifications as needed.
git diff master..HEAD

# Analyse your changes, try to keep as close as possible to the upstream code.
git diff HEAD

# Done!
git add simplesamlphp
git commit -m 'Issue #XXX - Backporting modifications for auth_saml2' # Customise the message!
```
