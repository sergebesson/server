#!/bin/bash

set -e

. ../commun/tools.sh

echoTitre "Installation des packages"
# mcrypt plus installé ! a voir si vraiment besoin
# mysqli et pdo-mysql plus nécessaire
apt -y install php-fpm php-curl php-gd php-mysql php-fxsl php-mbstring php-sqlite3 php-zip php-pspell php-intl

installFiles
sed -i "s/{{PASSWD}}/$password/g" /home/hew/bin/class/class_BatchMysql.php

echoTitre "Changement des droits"
chown -R hew:hew /home/hew/bin/class
find /home/hew/bin/class -type f -exec chmod 00600 {} \;
find /home/hew/bin/class -type d -exec chmod 00700 {} \;
