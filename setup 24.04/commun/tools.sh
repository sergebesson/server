#!/bin/bash

set -e

echoTitre() {
	echo
	echo -e "\E[01;33m$@ ...\E[00m"
}
error() {
	echo -e "\E[01;41;97m**ERREUR** : $@\E[00m" >&2
	exit 1
}
alert() {
	echo -e "\E[01;43;97m**ATTENTION** : $@\E[00m"
}

if [ $(whoami) != "root" ]; then
	error "Doit-être lancer en tant que root"
fi

expdate=$(date +%Y%m%dT%H:%M:%S)
password=$(head -1 ../.passwd)

installFiles() {
	echoTitre "Installation des fichiers/repertoires/lien"
	for file in $(find . -type f | grep ^./.*/); do
		echo "  installation du fichier ${file#.}"
		mkdir -p $(dirname ${file#.})
		cp -rp $file ${file#.}
		chown root:root ${file#.}
	done
	for dir in $(find . -type d -empty); do
		echo "  création du répertoire ${dir#.}"
		mkdir -p ${dir#.}
	done
	for link in $(find . -type l); do
		echo "  création du lien ${link#.}"
		if [ ! -h ${link#.} ]; then
			mkdir -p $(dirname ${link#.})
			ln -s $(readlink $link) ${link#.}
		else
			alert "${link#.} est déjà un lien, aucune modification n'a été effectuée"
		fi
	done
}

installConfig() {
	cfgFile=$1
	fileName=$(basename $cfgFile)
	if [[ "$fileName" == *.* ]]; then
		cfgHewFile=${cfgFile%.*}-hew.${fileName##*.}
	else
		cfgHewFile=$cfgFile-hew
	fi
	if [ ! -e $cfgHewFile ]; then
		error "$cfgHewFile n'existe pas"
	fi
	hewFileName=$(basename $cfgHewFile)
	if [ ! -h $cfgFile ]; then
		if [ -e $cfgFile ]; then
			mv $cfgFile ${cfgFile}-$expdate
			echo "  $cfgFile existe. Sauvegardé en ${cfgFile}-$expdate"
		fi
		ln -s $hewFileName $cfgFile
	else
		if [[ $(readlink $cfgFile) = "$hewFileName" ]]; then
			alert "$cfgFile est déjà un lien sur $hewFileName, aucune modification"
		else
			error "$cfgFile est déjà un lien mais pas sur $hewFileName"
		fi
	fi
}
