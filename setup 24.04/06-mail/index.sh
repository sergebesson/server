#!/bin/bash

set -e

. ../commun/tools.sh

echoTitre "Création de l'utilisateur vmail"
if ! getent group vmail >/dev/null 2>&1; then
	groupadd -g 5000 vmail
else
	echo "  le groupe vmail existe déjà"
fi
if ! getent passwd vmail >/dev/null 2>&1; then
	useradd -g vmail -u 5000 vmail -d /home/vmail/ -s /bin/bash -m
else
	echo "  l'utilisateur vmail existe déjà"
fi

echoTitre "Installation des packages"
DEBIAN_FRONTEND=noninteractive apt -y install \
	postfix \
	dovecot-imapd dovecot-lmtpd dovecot-sieve
DEBIAN_FRONTEND=noninteractive apt -y install \
	spamassassin \
	spamass-milter \
	pyzor \
	razor

installFiles

echoTitre "Changement des droits"
chmod 00600 /etc/cron.d/hew-sa-learn
chmod 00600 /etc/sudoers.d/vmail
chmod 00644 /etc/dovecot/conf.d/*-hew.conf* \
	/etc/postfix/*-hew.cf \
	/etc/aliases-hew
chown hew:hew /home/hew/bin/updatePostfixVmail.sh
chmod 00700 /home/hew/bin/updatePostfixVmail.sh
chown hew:hew /home/hew/bin/sa-learn.sh
chmod 00755 /home/hew/bin/sa-learn.sh
chmod 00770 /usr/bin/updatePostfixVmail
chgrp vmail /usr/bin/updatePostfixVmail
chown -R vmail:vmail /home/vmail
find /home/vmail -type f -exec chmod 00664 {} \;
find /home/vmail -type d -exec chmod 00775 {} \;
chown debian-spamd /home/spamassassin

echoTitre "configuration de devocot"
mv /etc/dovecot/conf.d/10-auth.conf{,.disabled} || true
mv /etc/dovecot/conf.d/10-mail.conf{,.disabled} || true
mv /etc/dovecot/conf.d/10-master.conf{,.disabled} || true
mv /etc/dovecot/conf.d/10-ssl.conf{,.disabled} || true
mv /etc/dovecot/conf.d/15-mailboxes.conf{,.disabled} || true
mv /etc/dovecot/conf.d/20-imap.conf{,.disabled} || true
mv /etc/dovecot/conf.d/20-lmtp.conf{,.disabled} || true
mv /etc/dovecot/conf.d/90-quota.conf{,.disabled} || true
mv /etc/dovecot/conf.d/90-sieve.conf{,.disabled} || true
sudo -u vmail sievec /home/vmail/dovecot.sieve

echoTitre "configuration de postfix"
installConfig /etc/aliases
installConfig /etc/postfix/main.cf
installConfig /etc/postfix/master.cf
newaliases
updatePostfixVmail

echoTitre "configuration de spamassassin"
installConfig /etc/default/spamd
installConfig /etc/spamassassin/local.cf
sudo -u debian-spamd sa-learn --sync

echoTitre "relance des services (spamassassin, dovecot, postfix)"
systemctl enable spamassassin-maintenance.timer
systemctl reload spamd
systemctl reload dovecot
systemctl reload postfix

echo -e "
\e[1;4;7;33m06-mail : information\e[0m

Pensez à mettre à jour le certificat pour \e[1;103m postfix \e[0m et \e[1;103m dovecot \e[0m.
Si les DNS le permet utiliser \e[1;103m certbot \e[0m ou sinon copier le certificat manuellement.
" | tee /home/hew/06-mail.info
