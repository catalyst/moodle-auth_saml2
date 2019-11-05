#!/usr/bin/env bash
set -eo xtrace

# Download
pushd ~
curl -L https://github.com/simplesamlphp/simplesamlphp/releases/download/v1.17.6/simplesamlphp-1.17.6.tar.gz | tar zx
cd simplesamlphp-1.17.6
composer install
popd

# Config
touch ~/simplesamlphp-1.17.6/modules/exampleauth/enable
mkdir -p ~/simplesamlphp-1.17.6/cert
cp -v tests/idp/authsources.idp ~/simplesamlphp-1.17.6/config/authsources.php
cp -v tests/idp/config.idp ~/simplesamlphp-1.17.6/config/config.php
cp -v tests/idp/crt ~/simplesamlphp-1.17.6/cert/server.crt
cp -v tests/idp/pem ~/simplesamlphp-1.17.6/cert/server.pem
cp -v tests/idp/sp-remote.idp ~/simplesamlphp-1.17.6/metadata/saml20-sp-remote.php

# Run
cd ~/simplesamlphp-1.17.6/www
php -S localhost:8001 2> /dev/null &
