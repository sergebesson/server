#!/usr/bin/php
<?php
define('ROOT_DIR', dirname($argv[0]));

# doit être execute en tant que root
if (trim(shell_exec('id |cut -d"(" -f2 |cut -d")" -f1')) != 'root') f_exit(100, 'ce script doit etre lancé par root');

# Récupération des paramètres : ex : InstHerbHttp.php <CodeClient> <Domaine> <SousDomaine> <Login> <mdp> <quota>
$Cli   = trim($argv[1]);
$Dom   = trim($argv[2]);
$SDom  = trim($argv[3]);
$Login = trim($argv[4]);
$Mdp   = trim($argv[5]);
$Quo   = trim($argv[6]);

# je control les paramètres
# Aucun doit être vide
if ($Cli == '')   f_exit(101, 'Code client non renseigné');
if ($Dom == '')   f_exit(102, 'Domaine non renseigné');
if ($SDom == '')  f_exit(103, 'Sous domaine non renseigné');
if ($Login == '') f_exit(104, 'Login non renseigné');
if ($Mdp == '')   f_exit(105, 'mot de passe non renseigné');
if ($Quo == '')   f_exit(106, 'Quota non renseigné');

# caractère autorisé pour le client : [a-z][A-Z]][0-9]-_.
if (!preg_match("/^[a-zA-Z0-9_\-\.]+$/", $Cli)) f_exit(110, 'code client incorrecte');
# caractère autorisé pour le domaine : [a-z][0-9]-.
if (!preg_match("/^[a-z0-9\-\.]+$/", $Dom)) f_exit(111, 'Domaine incorrecte');
# caractère autorisé pour le sous-domaine : [a-z][0-9]-.
if (!preg_match("/^[a-z0-9\-\.]+$/", $SDom)) f_exit(112, 'Sous domaine incorrecte');
# caractère autorisé pour le Login : [a-z][A-Z]][0-9]-_
if (!preg_match("/^[a-zA-Z0-9_\-]+$/", $Login) or strlen($Login) > 16) f_exit(113, 'Login incorrecte');
# caractère autorisé pour le mot de passe : [a-z][A-Z][0-9]
if (!preg_match("/^[a-zA-Z0-9]+$/i", $Mdp)) f_exit(114, 'Mot de passe incorrecte');
# Quota doit être compris entre 1 et 1024
if (($Quo < 1 or $Quo > 1024) and $Quo != -1) f_exit(115, 'Quota incorrecte');

# Création du repertoire client
@mkdir('/home/www/sites/'. $Cli);
if (!chown('/home/www/sites/'. $Cli, 'hew')) f_exit(120, "erreur : chown('/home/www/sites/$Cli', 'hew')");
if (!chgrp('/home/www/sites/'. $Cli, 'www-data')) f_exit(121, "erreur : chgrp('/home/www/sites/$Cli', 'www-data')");
if (!chmod('/home/www/sites/'. $Cli, 02750)) f_exit(122, "erreur : chmod('/home/www/sites/$Cli', 02750)");

# Création du repertoire domaine
@mkdir('/home/www/sites/'. $Cli. '/'. $Dom);
if (!chown('/home/www/sites/'. $Cli. '/'. $Dom, 'hew')) f_exit(130, "erreur : chown('/home/www/sites/$Cli/$Dom', 'hew')");
if (!chgrp('/home/www/sites/'. $Cli. '/'. $Dom, 'www-data')) f_exit(131, "erreur : chgrp('/home/www/sites/$Cli/$Dom', 'www-data')");
if (!chmod('/home/www/sites/'. $Cli. '/'. $Dom, 0750)) f_exit(132, "erreur : chmod('/home/www/sites/$Cli/$Dom', 0750)");

# On cré le user et le group
system('/usr/sbin/groupadd '. $Login, $CdRet);
if ($CdRet) f_exit(140, "erreur : /usr/sbin/groupadd $Login");
system('/usr/sbin/useradd -g '. $Login. ' -G www-data -d /home/www/sites/'. $Cli. '/'. $Dom. '/'. $SDom. '/FTP -s /bin/false '. $Login. ' -p \''. password_hash($Mdp, PASSWORD_DEFAULT). '\'', $CdRet);
if ($CdRet) f_exit(141, "erreur : /usr/sbin/useradd -g $Login -G www-data -d /home/www/sites/$Cli/$Dom/$SDom -s /bin/false $Login -p '". password_hash($Mdp, PASSWORD_DEFAULT). "'");
# On ajout le user hew au nouveau group
system('/usr/sbin/usermod -a -G '. $Login. ' hew', $CdRet);
if ($CdRet) f_exit(142, "erreur : /usr/sbin/usermod -a -G $Login hew");
# On ajout le user www-data au nouveau group
system('/usr/sbin/usermod -a -G '. $Login. ' www-data', $CdRet);
if ($CdRet) f_exit(143, "erreur : /usr/sbin/usermod -a -G $Login www-data");

# Création du repertoire sous domaine
@mkdir('/home/www/sites/'. $Cli. '/'. $Dom. '/'. $SDom);
if (!chown('/home/www/sites/'. $Cli. '/'. $Dom. '/'. $SDom, 'hew')) f_exit(150, "erreur : chown('/home/www/sites/$Cli/$Dom/$SDom', 'hew')");
if (!chgrp('/home/www/sites/'. $Cli. '/'. $Dom. '/'. $SDom, $Login)) f_exit(151, "erreur : chgrp('/home/www/sites/$Cli/$Dom/$SDom', '$Login')");
if (!chmod('/home/www/sites/'. $Cli. '/'. $Dom. '/'. $SDom, 02750)) f_exit(152, "erreur : chmod('/home/www/sites/$Cli/$Dom/$SDom', 02750)");

# Copie des repertoire model
system('cp -Rpd /home/www/configuration//modele/* /home/www/sites/'. $Cli. '/'. $Dom. '/'. $SDom, $CdRet);
if ($CdRet) f_exit(160, "erreur : cp -Rpd /home/www/sites/_CONF/modele/* /home/www/sites/$Cli/$Dom/$SDom");
system('find /home/www/sites/'.$Cli. '/'. $Dom. '/'. $SDom. ' -user root -exec chown '. $Login. ' {} \;', $CdRet);
if ($CdRet) f_exit(161, "find /home/www/sites/$Cli/$Dom/$SDom -user root -exec chown $Login {} \;");
system('find /home/www/sites/'.$Cli. '/'. $Dom. '/'. $SDom. ' -group root -exec chgrp '. $Login. ' {} \;', $CdRet);
if ($CdRet) f_exit(162, "find /home/www/sites/$Cli/$Dom/$SDom -group root -exec chgrp $Login {} \;");

# Mise a jour des fichier de conf (virtualhost, pool.conf et php.conf)
f_MajFile('/home/www/sites/'. $Cli. '/'. $Dom. '/'. $SDom. '/CONF/apache2/VirtualHost.conf', $Cli, $Dom, $SDom, $Login);
f_MajFile('/home/www/sites/'. $Cli. '/'. $Dom. '/'. $SDom. '/CONF/php/pool.conf', $Cli, $Dom, $SDom, $Login);
f_MajFile('/home/www/sites/'. $Cli. '/'. $Dom. '/'. $SDom. '/CONF/php/php.conf', $Cli, $Dom, $SDom, $Login);

# Ajout des liens dans /home/www/sites/configuration/php-fmp-pool.d, /home/www/sites/configuration/sites.d
system('ln -s /home/www/sites/'. $Cli. '/'. $Dom. '/'. $SDom. '/CONF/php/pool.conf /home/www/configuration/php-fmp-pool.d/10-'. $SDom. '.'. $Dom. '.conf', $CdRet);
if ($CdRet) f_exit(180, "ln -s /home/www/sites/$Cli/$Dom/$SDom/CONF/php/pool.conf /home/www/configuration/php-fmp-pool.d/10-$SDom.$Dom.conf");
system('ln -s /home/www/sites/'. $Cli. '/'. $Dom. '/'. $SDom. '/CONF/apache2/VirtualHost.conf /home/www/configuration/sites.d/10-'. $SDom. '.'. $Dom. '.conf', $CdRet);
if ($CdRet) f_exit(180, "ln -s /home/www/sites/$Cli/$Dom/$SDom/CONF/apache2/VirtualHost.conf /home/www/configuration/sites.d/10-$SDom.$Dom.conf");

# mise en place du quota
if ($Quo != -1)
{
	$Quota = $Quo * 1024;
	system('/usr/sbin/setquota -u '. $Login. ' '. intval($Quota). ' '. intval($Quota). ' 0 0 -a', $CdRet);
	if ($CdRet) f_exit(191, "erreur : /usr/sbin/setquota -u $Login ". intval($Quota). " ". intval($Quota). " 0 0 -a");
}

require_once(ROOT_DIR. '/class/class_BatchMysql.php');
$o_BatchMysql = new c_BatchMysql();
$o_BatchMysql->CreationUserMysql($Dom, $SDom, $Login, $Mdp);

# Reload de php et d'apache
system('/usr/bin/systemctl reload php8.3-fpm');
system('/usr/bin/systemctl reload apache2');

exit(0);

#-------------------------------------------------------
# FONCTION
#-------------------------------------------------------

# On quitte avec un message d'erreur
function f_exit($p_NumErr, $p_MessErr='')
{
	if ($p_MessErr != '') print $p_MessErr. "\n\n";

	exit($p_NumErr);
}

# Mise a jour de fichier template
function f_MajFile($p_File, $p_Client, $p_Domaine, $p_SousDomaine, $p_User)
{
	$StrTmpl = file_get_contents($p_File);
	if ($StrTmpl)
	{
		$StrTmpl = str_replace('{Client}', $p_Client, $StrTmpl);
		$StrTmpl = str_replace('{Domaine}', $p_Domaine, $StrTmpl);
		$StrTmpl = str_replace('{SousDomaine}', $p_SousDomaine, $StrTmpl);
		$StrTmpl = str_replace('{User}', $p_User, $StrTmpl);
		if (!file_put_contents($p_File, $StrTmpl)) f_exit(170, "erreur mise à jour de $p_File");
	}
	else
		f_exit(171, "erreur lecture de $p_File");
}

?>
