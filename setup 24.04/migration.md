# Migration d'un serveur

soit `hew1` le serveur à migrer

soit `hew2` le serveur cible 

## web

retrouver le `code client`, le `login`, le `mdp` et le `quota`

### sur `hew2`

`sudo InstHeberHttp <code client> <domain> <subDomain> <login> <mdp> <quota>`

le quota est en Mo et -1 pour pas de quota

### sur `hew1`

```bash
$ cd /home/www/sites/<code client>/<domain>/<subDomain>
$ sudo tar -czf FTP.tar.gz FTP
$ scp -P 54422 FTP.tar.gz hew2.homeasyweb.net:.
```

### sur `hew2`

```bash
$ cd /home/www/sites/<code client>/<domain>/<subDomain>
$ rm -rf FTP
$ sudo tar -xzf /home/hew/FTP.tar.gz
$ rm /home/hew/FTP.tar.gz
$ # Vérifier le propriétaire et group des fichiers, corriger si besoin
$ # sinon
$ sudo find FTP -user <bad_user> -exec chown <good_user> {} \;
$ sudo find FTP -group <bad_group> -exec chgrp <good_group> {} \;
```

### sur `hew1`

```bash
$ cd /home/www/sites/<code client>/<domain>/<subDomain>
$ rm -f FTP.tar.gz
```

### bind

modifier le dns

## Mysql

### sur `hew1`

```bash
$ mysqldump -u<login> -p<mdp> <databaseName> > <databaseName>.sql
$ scp -P 54422 <databaseName>.sql hew2.homeasyweb.net:.
```

### sur `hew2`

```bash
$ mysql -u<login> -p<mdp> <databaseName> <<databaseName>.sql
```

## Mail

faire un échange de clé avec l'utilisateur `root` entre les deux serveurs pour que `root@hew1` puisse se connecter à `root@hew2`.

### sur `hew2``

Créer la boite mail, avec l'utilisation `vmail` créer ou mettre à jour le fichier `/home/vmail/<domain>/users`

puis lancer : `sudo updatePostfixVmail`

### sur `hew1`

```bash
$ sudo doveadm backup -u <email> ssh -p 54422 hew2.homeasyweb.net doveadm dsync-server -u <email>
```

```bash
for f in */alias; do echo $f; mv $f ${f}_sav; done
for f in */users; do echo $f; mv $f ${f}_sav; done

for f in */users.migration; do echo $f; mv $f ${f%.migration}; done
for f in */alias.migration; do echo $f; mv $f ${f%.migration}; done

sudo updatePostfixVmail
```

```bash
for f in */users; do echo $f; mv $f ${f}.migration; done
for f in */alias; do echo $f; mv $f ${f}.migration; done

for f in */users_sav; do echo $f; mv $f ${f%_sav}; done
for f in */alias_sav; do echo $f; mv $f ${f%_sav}; done
```
