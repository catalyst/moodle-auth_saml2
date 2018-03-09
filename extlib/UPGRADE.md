Upgrade Dependencies
====================

Do not copy & paste blindly, these is the general idea only.

```bash
cd auth/saml2/extlib

# We will delete stuff and compare later.
# Ensure everything is commited into git.
git checkout -b issueXXX_extlib-upgrade 
git status
```

SimpleSAMLphp
-------------

```bash
rm -rf simplesamlphp saml2 xmlseclibs

curl -L https://simplesamlphp.org/download?latest | tar vxz
mv simplesamlphp-1.15.4 simplesamlphp
mv simplesamlphp/vendor/simplesamlphp/saml2 .
mv simplesamlphp/vendor/robrichards/xmlseclibs .
rm -rf simplesamlphp/config
rm -rf simplesamlphp/metadata
rm -rf simplesamlphp/vendor # If later you get missing libraries, maybe you need to move them.

git add simplesamlphp saml2 xmlseclibs
git commit -m 'Issue #XXX - Updating with code from simplesamlphp-1.15.4' # Customise the message!

# Check what was customised when this document was updated, you may want to cherry pick it.
git show a32804374772709d65264ab823f8a3b3adfc6391  
  
# Analyse what is different, backport modifications as needed.
git diff master..HEAD

# Analyse your changes, try to keep as close as possible to the upstream code.
git diff HEAD

# Done!
git add simplesamlphp saml2 xmlseclibs
git commit -m 'Issue #XXX - Backporting modifications for auth_saml2' # Customise the message!
```
