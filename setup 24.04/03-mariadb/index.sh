#!/bin/bash

set -e

. ../commun/tools.sh

echoTitre "Installation des packages"
apt -y install mariadb-server

installFiles

echoTitre "Changement des droits"
chmod 00644 /etc/mysql/mariadb.conf.d/*hew.cnf

echoTitre "Création du lien /home/mysql"
if [ ! -h /home/mysql ]; then
	ln -s /var/lib/mysql /home/mysql
else
	alert "Le lien /home/mysql existe déjà"
fi
systemctl restart mysqld

echoTitre "Personnalisation des droits sous mysql"
mysql -uroot <<END || alert "Erreur lors de la personnalisation des droits sour mysql, peut-être que c'est déjà fait"
GRANT ALL PRIVILEGES on *.* to 'root'@'localhost' IDENTIFIED BY '$password';
FLUSH PRIVILEGES;
END

# TODO update /etc/mysql/debian.cnf or no !
