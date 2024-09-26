#!/bin/bash

# Avant de lancer ce script, lancer le step 00 qui va rebooter le serveur

set -e

for step in {01..10}; do
	stepName=$(basename $(ls -d ${step}-*/ | cut -d- -f2))
	echo -------------------------------
	echo "   $step: $stepName"
	echo -------------------------------
	cd ${step}-*/
	bash index.sh
	cd ..
	echo
done

rm .passwd

cat /home/hew/*.info
