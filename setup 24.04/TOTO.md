# TODO

## for certbot

pour les mails création du certificat :

`certbot certonly -n --standalone -d hew1.homeasyweb.net`
et voir la conf de postfix et dovecot
mais aussi les hook de certbot

pour apache :

* sudo apt install python3-certbot-apache
* revoir la conf de certbot pour les hook
* revoir les fichiers /etc/letsencrypt/renewal/*.conf pour changer `authenticator = standalone` en `authenticator = apache`

Création d'un cert :
`certbot certonly -n --apache -d serge.famille-besson.com --dry-run`

pour node
modifier hook de certbot pour copier les fichiers du cert et changer les droit pour hew et relancer le service en tant que hew

## changement port de ssh

1. s'assurer de l'option `KbdInteractiveAuthentication no` dans `/etc/ssh/sshd_config`

2. changer le port : <[Sécuriser un VPS - OVHcloud](https://help.ovhcloud.com/csm/fr-vps-security-tips?id=kb_article_view&sysparm_article=KB0047708)> attention bien faire un `sudo systemctl restart ssh.socket` et pas le service !
