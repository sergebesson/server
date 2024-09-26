#!/bin/bash

set -e

. ../commun/tools.sh

echoTitre "Récupération de la version de php"
phpVersion=$(php -v | head -n 1 | cut -d' ' -f2 | cut -d'.' -f1,2)
echo "  Version de php: $phpVersion"

echoTitre "Installation des packages"
apt -y install apache2 libapache2-mod-xsendfile

installFiles
cp -r /etc/php/_version_/. /etc/php/$phpVersion
rm -rf /etc/php/_version_

echoTitre "Création du l'utilisateur www.default"
if ! getent group www.default >/dev/null 2>&1; then
	groupadd www.default
else
	echo "  le groupe www.default existe déjà"
fi
if ! getent passwd www.default >/dev/null 2>&1; then
	useradd -g www.default -G www-data -d /home/www/default.site/FTP/ -s /bin/false www.default
	usermod -a -G www.default www-data
	usermod -a -G www.default,www-data hew
else
	echo "  l'utilisateur www.default existe déjà"
fi

echoTitre "Changement des droits"
chmod 00600 /etc/cron.d/hew-admin-web
chown hew:hew /home/hew/bin/GestionLogHttp.php \
	/home/hew/bin/InstHeberHttp.php \
	/home/hew/bin/getDomSousDom.php \
	/home/hew/bin/web
chmod 00700 /home/hew/bin/GestionLogHttp.php \
	/home/hew/bin/InstHeberHttp.php
chmod 00700 /usr/bin/InstHeberHttp
chmod 00755 /home/hew/bin/getDomSousDom.php \
	/home/hew/bin/web
chmod 00755 /usr/bin/getDomSousDom \
	/usr/bin/web

chmod 00644 /etc/apache2/sites-available/*hew.conf \
	/etc/php/${phpVersion}/mods-available/*hew.ini \
	/etc/php/${phpVersion}/fpm/pool.d/*hew.conf

chmod 00755 /home/www
chown -R hew:hew /home/www/*
find /home/www/configuration -type f -exec chmod 00600 {} \;
find /home/www/configuration -type d -exec chmod 00700 {} \;

chgrp -R root /home/www/configuration/modele/*
find /home/www/configuration/modele/* -type f -exec chmod 00640 {} \;
find /home/www/configuration/modele/* -type d -exec chmod 02750 {} \;
chown root /home/www/configuration/modele/FTP/logs-php \
	/home/www/configuration/modele/FTP/hors-site \
	/home/www/configuration/modele/FTP/tmp \
	/home/www/configuration/modele/FTP/web
find /home/www/configuration/modele/CONF -type f -exec chmod 00600 {} \;
find /home/www/configuration/modele/CONF -type d -exec chmod 00700 {} \;

chmod 00750 /home/www/default.site
chown -R www.default /home/www/default.site/FTP/logs-php \
	/home/www/default.site/FTP/hors-site \
	/home/www/default.site/FTP/tmp \
	/home/www/default.site/FTP/web
chgrp -R www.default /home/www/default.site
find /home/www/default.site/FTP -type f -exec chmod 00640 {} \;
find /home/www/default.site/FTP -type d -exec chmod 02750 {} \;
find /home/www/default.site/CONF -type f -exec chmod 00600 {} \;
find /home/www/default.site/CONF -type d -exec chmod 00700 {} \;

echoTitre "Ajout de l'alias web"
if ! grep "alias web=" /home/hew/.bash_aliases >/dev/null 2>&1; then
	echo "alias web=\". web\"" >>/home/hew/.bash_aliases
fi

echoTitre "Activation/désactivation mods et pool de php"
phpenmod -s fpm hew
mv /etc/php/${phpVersion}/fpm/pool.d/www.conf{,.disabled} || alert "www.conf n'existe pas"

echoTitre "activation du site par défaut"
if [ ! -h /home/www/configuration/php-fmp-pool.d/00-default.site.conf ]; then
	ln -s ../../default.site/CONF/php/pool.conf /home/www/configuration/php-fmp-pool.d/00-default.site.conf
else
	alert "/home/www/configuration/php-fmp-pool.d/00-default.site.conf est déjà un lien, aucune modification n'a été effectuée"
fi
if [ ! -h /home/www/configuration/sites.d/00-default.site.conf ]; then
	ln -s ../../default.site/CONF/apache2/VirtualHost.conf /home/www/configuration/sites.d/00-default.site.conf
else
	alert "/home/www/configuration/sites.d/00-default.site.conf est déjà un lien, aucune modification n'a été effectuée"
fi

echoTitre "Activation/désactivation des mods, conf et site d'apache2"
a2enmod -q proxy_fcgi setenvif rewrite ssl xsendfile
a2enconf -q php${phpVersion}-fpm
a2dissite -q 000-default
a2ensite -q hew

echoTitre "relance des services (php8.1-fpm, apache2)"
systemctl reload php${phpVersion}-fpm || systemctl start php${phpVersion}-fpm
systemctl reload apache2 || systemctl start apache2
