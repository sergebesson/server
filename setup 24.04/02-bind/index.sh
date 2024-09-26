#!/bin/bash

set -e

. ../commun/tools.sh

echoTitre "Installation des packages"
apt -y install bind9 apparmor

installFiles

echoTitre "Changement des droits"
chgrp -R bind /home/bind
chmod -R 02755 /home/bind
find /home/bind/* -type f -exec chmod 00644 {} \;
find /home/bind/* -type d -exec chmod 00755 {} \;

chown hew:hew /home/hew/bin/MajDns.php
chmod 00700 /home/hew/bin/MajDns.php
chmod 00700 /usr/bin/MajDns

echoTitre "Création du lien sur /home/bind/named.conf.local"
if [ -h /etc/bind/named.conf.local ]; then
	if [ $(ls -l /etc/bind/named.conf.local | cut -f2 -d'>') = '/home/bind/named.conf.local' ]; then
		echo "  /etc/bind/named.conf.local est déjà un lien sur /home/bind/named.conf.local"
	else
		error "/etc/bind/named.conf.local est déjà un lien mais pas sur /home/bind/named.conf.local"
	fi
else
	if [ -e /etc/bind/named.conf.local ]; then
		mv /etc/bind/named.conf.local /etc/bind/named.conf.local-$expdate
		echo "  /etc/bind/named.conf.local sauvegardé en /etc/bind/named.conf.local-$expdate"
	fi
	ln -s /home/bind/named.conf.local /etc/bind
fi

echoTitre "Ajout de /home/bind dans la conf de apparmor pour named"
echo "/home/bind/** r," >>/etc/apparmor.d/local/usr.sbin.named

echoTitre "Relance de apparmor et bind9"
systemctl reload apparmor
systemctl restart bind9
