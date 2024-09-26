#!/usr/bin/php
<?php

# doit être execute en tant que root
if (trim(shell_exec('id |cut -d"(" -f2 |cut -d")" -f1')) != 'root') f_exit(100, 'ce script doit etre lancé par root');

# Récupération des paramètres : ex : InstHerbFtp.php <CodeClient> <Domaine> <SousDomaine> <Login> <mdp> <mdpftp> <quota>
$Cli   = trim($argv[1]);
$Dom   = trim($argv[2]);
$SDom  = trim($argv[3]);
$Login = trim($argv[4]);
$Mdp   = trim($argv[5]);
$MdpFtp= trim($argv[6]);
$Quo   = trim($argv[7]);

# je control les paramètres
# Aucun doit être vide
if ($Cli == '')   f_exit(101, 'Code client non renseigné');
if ($Dom == '')   f_exit(102, 'Domaine non renseigné');
if ($SDom == '')  f_exit(103, 'Sous domaine non renseigné');
if ($Login == '') f_exit(104, 'Login non renseigné');
if ($Mdp == '')   f_exit(105, 'mot de passe non renseigné');
if ($MdpFtp == '')   f_exit(105, 'mot de passe FTP non renseigné');
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
# caractère autorisé pour le mot de passe : [a-z][A-Z][0-9]
if (!preg_match("/^[a-zA-Z0-9]+$/i", $MdpFtp)) f_exit(115, 'Mot de passe FTP incorrecte');
# Quota doit être compris entre 1 et 1024
if (($Quo < 1 or $Quo > 5120) and $Quo != -1) f_exit(116, 'Quota incorrecte');

# Création du repertoire client
@mkdir('/home/ftp/'. $Cli);
if (!chown('/home/ftp/'. $Cli, 'hew')) f_exit(120, "erreur : chown('/home/ftp/$Cli', 'hew')");
if (!chgrp('/home/ftp/'. $Cli, 'hew')) f_exit(121, "erreur : chgrp('/home/ftp/$Cli', 'hew')");
if (!chmod('/home/ftp/'. $Cli, 02755)) f_exit(122, "erreur : chmod('/home/ftp/$Cli', 02755)");

# Création du repertoire domaine
@mkdir('/home/ftp/'. $Cli. '/'. $Dom);
if (!chown('/home/ftp/'. $Cli. '/'. $Dom, 'hew')) f_exit(130, "erreur : chown('/home/ftp/$Cli/$Dom', 'hew')");
if (!chgrp('/home/ftp/'. $Cli. '/'. $Dom, 'hew')) f_exit(131, "erreur : chgrp('/home/ftp/$Cli/$Dom', 'hew')");
if (!chmod('/home/ftp/'. $Cli. '/'. $Dom, 0755)) f_exit(132, "erreur : chmod('/home/ftp/$Cli/$Dom', 0755)");

# On cré le user et le group
system('/usr/sbin/groupadd '. $Login, $CdRet);
//if ($CdRet) f_exit(140, "erreur : /usr/sbin/groupadd $Login");
# On cré le user _admftp
system('/usr/sbin/useradd -g '. $Login. ' -d /home/ftp/'. $Cli. '/'. $Dom. '/'. $SDom. ' -s /bin/false '. $Login. '_admftp -p \''. password_hash($Mdp, PASSWORD_DEFAULT). '\'', $CdRet);
if ($CdRet) f_exit(141, "erreur : /usr/sbin/useradd -g $Login -d /home/ftp/$Cli/$Dom/$SDom -s /bin/false ${Login}_admftp -p '*********'");
# On cré le user _ftp
system('/usr/sbin/useradd -g '. $Login. ' -d /home/ftp/'. $Cli. '/'. $Dom. '/'. $SDom. ' -s /bin/false '. $Login. '_ftp -p \''. password_hash($MdpFtp, PASSWORD_DEFAULT). '\'', $CdRet);
if ($CdRet) f_exit(142, "erreur : /usr/sbin/useradd -g $Login -d /home/ftp/$Cli/$Dom/$SDom -s /bin/false ${Login}_ftp -p '*********'");
# On ajout le user hew au nouveau group
system('/usr/sbin/usermod -a -G '. $Login. ' hew', $CdRet);
if ($CdRet) f_exit(143, "erreur : /usr/sbin/usermod -a -G $Login hew");

# Création du repertoire sous domaine
@mkdir('/home/ftp/'. $Cli. '/'. $Dom. '/'. $SDom);
if (!chown('/home/ftp/'. $Cli. '/'. $Dom. '/'. $SDom, $Login.'_admftp')) f_exit(150, "erreur : chown('/home/ftp/$Cli/$Dom/$SDom', 'hew')");
if (!chgrp('/home/ftp/'. $Cli. '/'. $Dom. '/'. $SDom, $Login)) f_exit(151, "erreur : chgrp('/home/ftp/$Cli/$Dom/$SDom', '$Login')");
if (!chmod('/home/ftp/'. $Cli. '/'. $Dom. '/'. $SDom, 02750)) f_exit(152, "erreur : chmod('/home/ftp/$Cli/$Dom/$SDom', 02750)");

# mise en place du quota
if ($Quo != -1)
{
	$Quota = $Quo * 1024;
	system('/usr/sbin/setquota -u '. $Login. '_admftp '. intval($Quota). ' '. intval($Quota). ' 0 0 -a', $CdRet);
	if ($CdRet) f_exit(160, "erreur : /usr/sbin/setquota -u ${Login}_admftp ". intval($Quota). " ". intval($Quota). " 0 0 -a");
	system('/usr/sbin/setquota -u '. $Login. '_ftp '. intval($Quota). ' '. intval($Quota). ' 0 0 -a', $CdRet);
	if ($CdRet) f_exit(161, "erreur : /usr/sbin/setquota -u ${Login}_ftp ". intval($Quota). " ". intval($Quota). " 0 0 -a");
}

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

?>
