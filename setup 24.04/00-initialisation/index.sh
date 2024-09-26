#!/bin/bash

set -e

. ../commun/tools.sh

echoTitre "Test de l'existence du user hew"
if ! getent passwd hew >/dev/null 2>&1; then
	error "utilisateur hew non trouvé"
fi
echo "  Utilisateur hew trouvé"

echoTitre "Mise a jour du hostname si besoin"
if [[ $(hostname -d) = "" ]]; then
	hostnamectl set-hostname $(hostname -s).homeasyweb.net
	echo "  Mise à jour effectuée"
	hostnamectl
fi

echoTitre "Mise à jour du /etc/issue si besoin"
if ! grep 'ip: ' /etc/issue >/dev/null; then
	ip=""
	for link in $(ip link | grep '^[[:digit:]]' | grep -v lo: | cut -d':' -f2); do
		ip="$ip \4{$link}"
	done
	echo -e "ip: $ip\n" >>/etc/issue
fi

echoTitre "Désactivation de services au démarrage"
systemctl disable apport-autoreport.path || true

systemctl disable apport || true
systemctl disable iscsid || true
systemctl disable lvm2-monitor || true
systemctl disable ModemManager || true
systemctl disable networkd-dispatcher || true
systemctl disable open-iscsi || true
systemctl disable open-vm-tools || true
systemctl disable snapd.apparmor || true
systemctl disable snapd.autoimport || true
systemctl disable snapd.core-fixup || true
systemctl disable snapd.recovery-chooser-trigger || true
systemctl disable snapd.seeded || true
systemctl disable snapd || true
systemctl disable snapd.system-shutdown || true
systemctl disable sysstat || true
systemctl disable ua-reboot-cmds || true
systemctl disable ubuntu-advantage || true
systemctl disable udisks2 || true
systemctl disable vgauth || true

systemctl disable apport-forward.socket || true
systemctl disable dm-event.socket || true
systemctl disable iscsid.socket || true
systemctl disable lvm2-lvmpolld.socket || true
systemctl disable lxd-installer.socket || true
systemctl disable snapd.socket || true

systemctl disable apport-autoreport.timer || true
systemctl disable mdcheck_continue.timer || true
systemctl disable mdcheck_start.timer || true
systemctl disable mdcheck_continue.timer || true
systemctl disable mdcheck_start.timer || true
systemctl disable mdmonitor-oneshot.timer || true
systemctl disable snapd.snap-repair.timer || true
systemctl disable sysstat-collect.timer || true
systemctl disable sysstat-summary.timer || true
systemctl disable ua-timer.timer || true
systemctl disable update-notifier-download.timer || true
systemctl disable ua-timer.timer || true
systemctl disable update-notifier-download.timer || true

echoTitre "Désinstallation de cloud-init"
apt -y purge cloud-init
rm -rf /etc/cloud
rm -rf /var/lib/cloud/

echoTitre "Mise à jour du système"
/usr/bin/apt update
DEBIAN_FRONTEND=noninteractive /usr/bin/apt -y full-upgrade

echoTitre "Installation des packages"
apt -y install \
	lsof \
	telnet \
	gawk \
	findutils \
	grep \
	sudo \
	mount \
	openssh-server \
	quota \
	quotatool \
	xinetd \
	curl \
	htop \
	zip \
	unzip \
	net-tools \
	bat
/usr/bin/apt -y autoremove
/usr/bin/apt autoclean

installFiles

echoTitre "Changement des droits"
chmod 00600 /etc/cron.d/apt
chmod 00600 /etc/sudoers.d/hew
chmod 00700 /root/.ssh
chmod 00600 /root/.ssh/* /root/.bashrc
find /home/etc -type f -exec chmod 00644 {} \;
find /home/etc -type d -exec chmod 00755 {} \;
chown -R hew:hew /home/hew
chmod 00700 /home/hew/.ssh /home/hew/bin
chmod 00600 /home/hew/.ssh/authorized_keys /home/hew/.bashrc

echoTitre "Customize crontab"
awk -F= '{
	if ($0 != "MAILTO=tech@homeasyweb.net" &&
		$0 != "TERM=dumb")
	{
		print $0
		if ($1 == "PATH")
		{
			print "MAILTO=tech@homeasyweb.net"
			print "TERM=dumb"
		}
	}
}' /etc/crontab >/tmp/crontab
chmod 00644 /tmp/crontab
mv /tmp/crontab /etc/crontab
if [ ! -h /etc/cron.d ]; then
	if [ -e /home/etc/cron.d ]; then
		mv /home/etc/cron.d /home/etc/cron.d-$expdate
		echo "  /home/etc/cron.d existe déjà. Déplacé en /home/etc/cron.d-$expdate"
	fi
	mv /etc/cron.d /home/etc
	ln -s /home/etc/cron.d/ /etc/cron.d
else
	alert "/etc/cron.d est déjà un lien, aucune modification n'a été effectuée"
fi

echoTitre "customize xinetd"
if [ ! -e /home/etc/xinetd.d ]; then
	mkdir /home/etc/xinetd.d
fi
awk '{
	if ($0 != "includedir /home/etc/xinetd.d")
	{
		print $0
	}
}' /etc/xinetd.conf >/tmp/xinetd.conf
chmod 00644 /tmp/xinetd.conf
mv /tmp/xinetd.conf /etc/xinetd.conf
echo "includedir /home/etc/xinetd.d" >>/etc/xinetd.conf
systemctl restart xinetd
if [ ! -h /etc/services ]; then
	if [ -e /home/etc/services ]; then
		mv /home/etc/services /home/etc/services-$expdate
		echo "  /home/etc/services existe déjà. Déplacé en /home/etc/services-$expdate"
	fi
	mv /etc/services /home/etc
	ln -s /home/etc/services /etc/services
else
	alert "/etc/services est déjà un lien, aucune modification n'a été effectuée"
fi

echoTitre "Customize /etc/hew"
if [ ! -h /etc/hew ]; then
	if [ ! -e /home/etc/hew ]; then
		mkdir /home/etc/hew
	fi
	ln -s /home/etc/hew/ /etc/hew
else
	alert "/etc/hew est déjà un lien, aucune modification n'a été effectuée"
fi

echoTitre "Ajout du quota pour /"
if ! grep -E '\s+/\s+ext4.*,usrquota' /etc/fstab >/dev/null 2>&1; then
	awk '{if ($1 != "#" && $2 == "/") {printf("%s %s %s %s,usrquota %s %s\n", $1, $2, $3, $4, $5, $6)} else {print $0}}' /etc/fstab >/tmp/fstab
	chmod 00644 /tmp/fstab
	mv /tmp/fstab /etc/fstab
	mount -o remount /
	quotacheck -amf
else
	echo "  / déjà avec l'option usrquota"
fi

echo -e "
\e[1;4;7;33m00-initialisation : information\e[0m

$(hostnamectl)
" | tee /home/hew/00-initialisation.info

alert "redémarrage du serveur !"
reboot
