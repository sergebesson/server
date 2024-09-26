# Installation du server hew1 (ns3101180)

## Accès ssh

`ssh ubuntu@91.121.108.105`

## Initialisation

### changement du nom du server

`sudo hostnamectl set-hostname hew1.homeasyweb.net`

### Configuration de sshd

modifie la ligne `KbdInteractiveAuthentication no` du fichier `/etc/ssh/sshd_config` pour `yes`

### Création de l'utilisateur hew

```bash
sudo -i
groupadd hew
useradd -g hew -G adm,cdrom,dip,plugdev,lxd,sudo -m -s '/bin/bash' hew
passwd hew
```

### On bloque l'utilisateur ubuntu

```bash
sudo -i
usermod -L ubuntu
rm /etc/sudoers.d/90-cloud-init-users
```
