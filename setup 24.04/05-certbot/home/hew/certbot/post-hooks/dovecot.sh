#!/usr/bin/env bash

set -e

# reload dovecot
systemctl reload dovecot.service

# notify by mail
/usr/sbin/sendmail serge@homeasyweb.net <<END_OF_MAIL
Subject: dovecot reload
From: $(hostname -s)@homeasyweb.net
To: serge@homeasyweb.net

certbot post hook reload dovecot
END_OF_MAIL
