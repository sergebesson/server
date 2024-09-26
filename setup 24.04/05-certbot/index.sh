#!/bin/bash

set -e

. ../commun/tools.sh

echoTitre "Installation des packages"
apt -y install certbot python3-certbot-apache

installFiles

echoTitre "s'enregistrer"
certbot register || true

echoTitre "Changement des droits"
chown -R hew:hew /home/hew/certbot
chmod 00755 /home/hew/certbot/post-hooks/*.sh
chmod 00755 /home/hew/certbot/getCert*.sh

echo -e "
\e[1;4;7;33m05-certbot : information\e[0m

On peut trouver des post hooks dans /home/hew/certbot/post-hooks/
qui permettent de relancer des services après un renouvellement de certificat.

Pour créer un certificat :
\e[1;103m certbot certonly -n --apache -d serge.famille-besson.com \e[0m
Ajouter l'option \e[1;103m --dry-run \e[0m pour tester sans créer de certificat.
" | tee /home/hew/05-certbot.info
