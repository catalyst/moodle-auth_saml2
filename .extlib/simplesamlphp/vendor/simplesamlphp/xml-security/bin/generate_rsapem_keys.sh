#!/usr/bin/env bash

DESTINATION="/tmp"
PASSWORD="1234"
KEYSIZE="1024"
VALID_UNTIL=36500

if [[ $1 == '-h' || $1 == '--help' ]]; then
    echo "Usage: $0 <destination directory>"
    exit
fi
if [[ $1 == '-d' || $1 == '--destination' ]]; then
    DESTINATION="${2:-/tmp}"
fi

PEM_RSA_DIR="${DESTINATION}/resources/certificates/rsa-pem"

declare -a CERTS=(
  "signed"
  "other"
  "expired"
  "corrupted"
  "broken"
)

## Generate directory structure ##

mkdir $DESTINATION/certificates
touch $DESTINATION/index.txt
echo 01 > $DESTINATION/serial.txt
mkdir $PEM_RSA_DIR

## Create CA ##

SUBJ="/emailAddress=noreply@simplesamlphp.org/CN=SimpleSAMLphp\ Testing\ CA/O=SimpleSAMLphp\ HQ/L=Honolulu/ST=Hawaii/C=US"

openssl genrsa -out $PEM_RSA_DIR/simplesamlphp.org-ca_nopasswd.key $KEYSIZE

openssl rsa -aes256 -in $PEM_RSA_DIR/simplesamlphp.org-ca_nopasswd.key \
  -out $PEM_RSA_DIR/simplesamlphp.org-ca.key -passout pass:$PASSWORD

openssl req -x509 -new -nodes -key $PEM_RSA_DIR/simplesamlphp.org-ca_nopasswd.key \
  -sha256 -days $VALID_UNTIL -out $PEM_RSA_DIR/simplesamlphp.org-ca.crt \
  -subj "${SUBJ}"

## Generate certificates

for CERT in "${CERTS[@]}"
do
  SUBJ="/CN=${CERT}.simplesamlphp.org/O=SimpleSAMLphp\ HQ/L=Honolulu/ST=Hawaii/C=US"

  openssl genrsa -out $PEM_RSA_DIR/$CERT.simplesamlphp.org_nopasswd.key $KEYSIZE

  openssl rsa -aes256 -in $PEM_RSA_DIR/$CERT.simplesamlphp.org_nopasswd.key \
    -out $PEM_RSA_DIR/$CERT.simplesamlphp.org.key -passout pass:$PASSWORD

  openssl req -new -key $PEM_RSA_DIR/$CERT.simplesamlphp.org_nopasswd.key \
    -out $PEM_RSA_DIR/$CERT.simplesamlphp.org.csr -subj "${SUBJ}"

  if [[ "$CERT" == "expired" ]]; then
    openssl x509 -req -in $PEM_RSA_DIR/$CERT.simplesamlphp.org.csr -CA $PEM_RSA_DIR/simplesamlphp.org-ca.crt \
      -CAkey $PEM_RSA_DIR/simplesamlphp.org-ca_nopasswd.key -CAserial $DESTINATION/serial.txt \
      -out $PEM_RSA_DIR/$CERT.simplesamlphp.org.crt -days 0 -passin pass:$PASSWORD
  else
    openssl x509 -req -in $PEM_RSA_DIR/$CERT.simplesamlphp.org.csr -CA $PEM_RSA_DIR/simplesamlphp.org-ca.crt \
      -CAkey $PEM_RSA_DIR/simplesamlphp.org-ca_nopasswd.key -CAserial $DESTINATION/serial.txt \
      -out $PEM_RSA_DIR/$CERT.simplesamlphp.org.crt -days $VALID_UNTIL -passin pass:$PASSWORD
  fi
done

## Self-signed certificate

SUBJ="/CN=selfsigned.simplesamlphp.org/O=SimpleSAMLphp\ HQ/L=Honolulu/ST=Hawaii/C=US"

openssl genrsa -out $PEM_RSA_DIR/selfsigned.simplesamlphp.org_nopasswd.key $KEYSIZE

openssl rsa -aes256 -in $PEM_RSA_DIR/selfsigned.simplesamlphp.org_nopasswd.key \
  -out $PEM_RSA_DIR/selfsigned.simplesamlphp.org.key -passout pass:$PASSWORD

openssl req -x509 -new -nodes -key $PEM_RSA_DIR/selfsigned.simplesamlphp.org_nopasswd.key \
  -sha256 -days $VALID_UNTIL -out $PEM_RSA_DIR/selfsigned.simplesamlphp.org.crt \
  -subj "${SUBJ}"

## Corrupt certificate ##

# This sed-command will swap the first & last character
sed 's/^\(.\)\(.\+\)\(.\)$/\3\2\1/' $PEM_RSA_DIR/corrupted.simplesamlphp.org_nopasswd.key \
  > $PEM_RSA_DIR/corrupted.simplesamlphp.org.key
sed 's/^\(.\)\(.\+\)\(.\)$/\3\2\1/' $PEM_RSA_DIR/corrupted.simplesamlphp.org.crt \
  >> $PEM_RSA_DIR/corrupted.simplesamlphp.org.crt
rm $PEM_RSA_DIR/corrupted.simplesamlphp.org_nopasswd.key

## Break PEM structure ##

# This sed-command will swap the first & last character
sed 's/ //g' $PEM_RSA_DIR/broken.simplesamlphp.org_nopasswd.key > $PEM_RSA_DIR/broken.simplesamlphp.org.key
sed 's/ //g' $PEM_RSA_DIR/broken.simplesamlphp.org.crt >> $PEM_RSA_DIR/broken.simplesamlphp.org.crt
rm $PEM_RSA_DIR/broken.simplesamlphp.org_nopasswd.key

## Clean-up csr- and ca-files
rm -f $PEM_RSA_DIR/*.csr
rm -f $DESTINATION/index.txt
rm -f $DESTINATION/serials.txt
