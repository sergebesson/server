#!/usr/bin/php
<?php

# Definition des constantes
define('ROOT_DIR', dirname($argv[0]));
define('C_PathHosts', '/home/bind/zones/');
define('C_FileNamed', '/home/bind/named.conf.local');
# sur kimsufi => ns.kimsufi.com (c le secondaire)
# sur sd => sdns1.ovh.net
define('C_ModelHosts',
'$ttl 86400
{DOM}.	IN	SOA	ns1.homeasyweb.net. tech.homeasyweb.net. (
				{DATE}01
				21600
				3600
				604800
				86400 )
		IN	NS	ns1.homeasyweb.net.
		IN	NS	ns2.homeasyweb.net.
		IN	MX	10 mail1.homeasyweb.net.

		IN	A	87.98.128.192
mail		IN	CNAME	mail1.homeasyweb.net.
mysql		IN	CNAME	mysql1.homeasyweb.net.
*		IN	CNAME	http1.homeasyweb.net.
');
define('C_ModelNamed1',
'
zone "{DOM}" {
	type master;
	file "'. C_PathHosts. '{DOM}.hosts";
	};
');
define('C_ModelNamed2',
'
zone "{DOM}" {
        type slave;
        file "'. C_PathHosts. '{DOM}.hosts";
        masters {
                87.98.128.192;
                };
        };
');
#define('C_TypeDNS', (trim(shell_exec('uname -n'))=='ns1.homeasyweb.net')?'Primaire':'Secondaire');
define('C_TypeDNS', 'Primaire');

# doit etre execute en tant que root
if (trim(shell_exec('id |cut -d"(" -f2 |cut -d")" -f1')) != 'root')
{
	print "ce script doit etre lancé par root\n";
	exit(1);
}

# on verifie le domaine
$Domaine = strtolower($_SERVER['argv'][1]);
if (trim($Domaine) == '')
{
	print 'usage : '. $_SERVER['argv'][0]. ' domaine.tld'. "\n";
	exit(2);
}
if (strpos(file_get_contents(C_FileNamed), '"'. $Domaine. '"') !== false)
{
	print 'Ce domaine ('. $Domaine. ') existe deja'. "\n";
	exit(3);
}

# On traite en fonction du serveur primaire ou secondaire
require(ROOT_DIR. '/class/class_StringTools.php');
$stringTools = new StringTools();
if (C_TypeDNS == 'Primaire')
{
	# Gestion du DNS Primaire
	# 1. Création du fichier hosts
	$StrFile = $stringTools->parseTpl
	(
		array
		(
			'DOM'	=> $Domaine,
			'DATE'	=> date('Ymd'),
		),
		C_ModelHosts
	);
	file_put_contents(C_PathHosts. $Domaine. '.hosts', $StrFile);
	# 2. Modification du fichier named.conf
	$StrFile = $stringTools->parseTpl(array('DOM' => $Domaine), C_ModelNamed1);
	file_put_contents(C_FileNamed, $StrFile, FILE_APPEND);
}
else
{
	# Gestion du DNS Secondaire
	# 1. Modification du fichier named.conf
	$StrFile = $stringTools->parseTpl(array('DOM' => $Domaine), C_ModelNamed2);
	file_put_contents(C_FileNamed, $StrFile, FILE_APPEND);
}

# On relance named
system('systemctl reload bind9 2>&1');
sleep(1);

# On control que tout c'est bien passe
system("dig @localhost $Domaine a |egrep '^$Domaine'", $CdRet);
if ($CdRet)
	print 'ERREUR : La mise a jour n\'a pas fonctionnee'. "\n";
else
{
	print 'Mise a jour effective'. "\n";
	if (C_TypeDNS == 'Primaire')
		print 'Pensez à mettre à jour le DNS secondaire'. "\n";
}
?>
