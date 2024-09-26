#!/bin/bash

set -e

. ../commun/tools.sh

echoTitre "Installation des packages"
apt -y install vsftpd

installFiles

echoTitre "Changement des droits"
chmod 00644 /etc/vsftpd-hew.conf
chmod 00755 /home/ftp
chown hew:hew /home/hew/bin/InstHeberFtp.php
chmod 00700 /home/hew/bin/InstHeberFtp.php
chmod 00700 /usr/bin/InstHeberFtp

echoTitre "Modification de /etc/shells"
if ! grep '/bin/false' /etc/shells >/dev/null 2>&1; then
	echo "# Personnalisation pour Home Easy Web" >>/etc/shells
	echo "/bin/false" >>/etc/shells
else
	echo "  /etc/shells avec déjà /bin/false"
fi

echoTitre "Configuration de vsftpd"
installConfig /etc/vsftpd.conf

echoTitre "Relance de vsftpd"
systemctl restart vsftpd
