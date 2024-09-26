#!/bin/bash

set -e

. ../commun/tools.sh

echoTitre "Installation des packages"
apt -y install fetchmail

installFiles

echoTitre "Changement des droits"
chmod 00600 /etc/cron.d/hew-fetchmail
chown -R hew /home/fetchmail
chmod 00600 /home/fetchmail/*
chmod u+x /home/fetchmail/ex_fetchmail
