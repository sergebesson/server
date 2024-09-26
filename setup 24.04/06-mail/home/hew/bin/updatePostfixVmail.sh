#!/bin/bash

vmaildomainsFile=/etc/postfix/vmaildomains
valiasFile=/etc/postfix/valias
echo -e "#\n# File generate by updatePostfixVmailbox\n#\n" | tee $vmaildomainsFile.tmp $valiasFile.tmp >/dev/null

if ls /home/vmail/*/users >/dev/null 2>&1; then
	for usersFile in /home/vmail/*/users; do
		dom=$(echo $usersFile | cut -d/ -f4)
		echo $dom >>$vmaildomainsFile.tmp
	done
fi

if ls /home/vmail/*/alias >/dev/null 2>&1; then
	for aliasFile in /home/vmail/*/alias; do
		dom=$(echo $aliasFile | cut -d/ -f4)
		echo "# $dom" >>$valiasFile.tmp
		awk -v dom=$dom '/^[ \t]*[^# \t]/ {
			indentation = $0
			sub(/\S.*/, "", indentation)
			printf("%s",indentation)

			for(i = 1; i <= NF; i++) {
				withDom = match($i, /@/)
				if (withDom == 0) {
					printf("%s@%s ", $i, dom)
				} else {
					printf("%s ", $i)
				}
			}
			printf("\n");
		}' $aliasFile >>$valiasFile.tmp
		echo >>$valiasFile.tmp
	done
fi

mv $vmaildomainsFile{.tmp,}
mv $valiasFile{.tmp,}
postmap $valiasFile
