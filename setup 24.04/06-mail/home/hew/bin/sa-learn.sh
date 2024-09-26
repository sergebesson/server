#!/bin/bash

nbJour=${1:-2}

echo "$(date -Iseconds) : debut de l'apprentissage, $nbJour jours"
for path in $(ls -d /home/vmail/*/*/); do
	user=$(basename $path)
	domain=$(basename $(dirname $path))
	echo -n "$user@$domain"

	nbMail=0
	for mail in $(find /home/vmail/$domain/$user/cur -type f -mtime -${nbJour}); do
		sa-learn --ham $mail >/dev/null
		((nbMail++))
	done
	echo -n " - $nbMail mails appris"

	nbMail=0
	for mail in $(find /home/vmail/$domain/$user/.Junk/cur -type f -mtime -${nbJour}); do
		sa-learn --spam $mail >/dev/null
		((nbMail++))
	done
	echo " - $nbMail spam appris"
done
chown debian-spamd:debian-spamd /home/spamassassin/bayes_*
echo "$(date -Iseconds) : fin de l'apprentissage"
