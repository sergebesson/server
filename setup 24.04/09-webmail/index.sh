#!/bin/bash

set -e

. ../commun/tools.sh

echoTitre "Installation de roundcube"
version=$(ls roundcubemail-*-complete.tar.gz | cut -d- -f2)
rm -rf /home/www/default.site/FTP/web/roundcubemail-${version} 2>/dev/null || true
rm /home/www/default.site/FTP/web/webmail 2>/dev/null || true
tar -xf roundcubemail-*-complete.tar.gz -C /home/www/default.site/FTP/web
sudo -u www.default ln -s roundcubemail-${version} /home/www/default.site/FTP/web/webmail
rm -rf /home/www/default.site/FTP/web/webmail/installer

installFiles

echoTitre "Changement des droits"
chown -R www.default:www.default /home/www/default.site/FTP/web/roundcubemail-${version}
chown www.default:www.default /home/www/default.site/FTP/web/webmail
find /home/www/default.site/FTP/web/roundcubemail-${version} -type f -exec chmod 00640 {} \;
find /home/www/default.site/FTP/web/roundcubemail-${version} -type d -exec chmod 02750 {} \;
