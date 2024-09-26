#!/usr/bin/env bash

set -e

srcCertPath=/etc/letsencrypt/live/paul3539.famille-besson.com/
dstCertPath=/home/www/sites/besson/famille-besson.com/serge/FTP/node/certbot/paul3539.famille-besson.com/

# copie les cert
/usr/bin/cp -p ${srcCertPath}/*.pem ${dstCertPath}
/usr/bin/chown hew:hew ${dstCertPath}/*.pem

# relance du serveur
/usr/bin/pkill -SIGUSR1 -f '^node.*filmotheque'

# notify by mail
/usr/sbin/sendmail serge@homeasyweb.net <<END_OF_MAIL
Subject: certbot renew
From: $(hostname -s)@homeasyweb.net
To: serge@homeasyweb.net

certbot post hook copy cert for filmotheque and reload server
END_OF_MAIL
