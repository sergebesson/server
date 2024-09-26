#!/bin/bash

set -e

. ../commun/tools.sh

echoTitre "Installation de phpmyadmin"
version=$(ls phpMyAdmin-*-all-languages.zip | cut -d- -f2)
rm -rf /home/www/default.site/FTP/web/phpMyAdmin-${version}-all-languages 2>/dev/null || true
rm /home/www/default.site/FTP/web/phpmyadmin 2>/dev/null || true
unzip -q phpMyAdmin-*-all-languages.zip -d /home/www/default.site/FTP/web
sudo -u www.default ln -s phpMyAdmin-${version}-all-languages /home/www/default.site/FTP/web/phpmyadmin

installFiles

echoTitre "Configuration de phpmyadmin"
blowfishSecret=$(tail -1 ../.passwd)
sed -i "s/{{BLOWFISH_SECRET}}/$blowfishSecret/g" /home/www/default.site/FTP/web/phpmyadmin/config.inc.php

echoTitre "Changement des droits"
chown -R www.default:www.default /home/www/default.site/FTP/web/phpMyAdmin-${version}-all-languages
chown www.default:www.default /home/www/default.site/FTP/web/phpmyadmin
find /home/www/default.site/FTP/web/phpMyAdmin-${version}-all-languages -type f -exec chmod 00640 {} \;
find /home/www/default.site/FTP/web/phpMyAdmin-${version}-all-languages -type d -exec chmod 02750 {} \;
