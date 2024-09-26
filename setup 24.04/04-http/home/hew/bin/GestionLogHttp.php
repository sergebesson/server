#!/usr/bin/php
<?php

define('ROOT_DIR', dirname($argv[0]));
define('C_PATH_HTTP', '/home/www/sites/');
define('C_PATH_LOG', C_PATH_HTTP. '*/*/*/FTP/logs-apache/access.log');
define('C_PATH_ARCHIVE', 'FTP/sauvegarde/apache/access.log');
define('C_PATH_ERROR_APACHE', 'FTP/logs-apache/error.log');
define('C_PATH_ERROR_PHP', 'FTP/logs-php/php.log');
define('C_PATH_ERROR_PHP_MAIL', 'FTP/logs-php/mail.log');
define('C_REP_SAUV_MYSQL', 'FTP/sauvegarde/mysql/');
define('C_NB_SAUV_MYSQL', 4);
define('C_PATH_LOG_DEFAULT', C_PATH_HTTP. '../default.site/FTP/logs-apache/access.log');

define('C_SUDO', '/usr/bin/sudo');
define('C_ZIP', '/usr/bin/zip');
define('C_Rm', '/bin/rm');

$DateNow=date('Ymd');

#----------------------------------------------------
# Etape 1 déplacement des log
#----------------------------------------------------

# Déplacement des logs
$TabFilesLog = array();
$LstFileLog = glob(C_PATH_LOG);
foreach ($LstFileLog as $FileLog)
{
	list( , , , ,$CdClient, $Domaine, $SousDomaine) = explode('/', $FileLog);
	$FileDst = C_PATH_HTTP. $CdClient. '/'. $Domaine. '/'. $SousDomaine. '/'. C_PATH_ARCHIVE. '.'. $DateNow;
	$TabFilesLog[] = array
	(
		'CdClient'	=> $CdClient,
		'Dom'		=> $Domaine,
		'SousDom'	=> $SousDomaine,
		'FileLog'	=> $FileDst,
	);
	rename($FileLog, $FileDst);
}

# On vide les log du site par défaut
unlink(C_PATH_LOG_DEFAULT);

# Reload d'apache
system(C_SUDO. ' systemctl reload apache2');
print 'Reload Apache : '. date('d-m-Y h:i:s'). "\n";

#----------------------------------------------------
# Etape 2 Traitement des log
#----------------------------------------------------

foreach ($TabFilesLog as $TabFileLog)
{

	#----------------------------------------------------
	# Etape 2.2 : Archivage et réinitialisation des log
	#----------------------------------------------------
	f_ZipFile($TabFileLog['FileLog']);
	# je vide les log d'erreur
	shell_exec(C_SUDO. ' sh -c "> '. C_PATH_HTTP. $TabFileLog['CdClient']. '/'. $TabFileLog['Dom']. '/'. $TabFileLog['SousDom']. '/'. C_PATH_ERROR_APACHE. '"');
	if (is_file(C_PATH_HTTP. $TabFileLog['CdClient']. '/'. $TabFileLog['Dom']. '/'. $TabFileLog['SousDom']. '/'. C_PATH_ERROR_PHP))
		shell_exec(C_SUDO. ' sh -c "> '. C_PATH_HTTP. $TabFileLog['CdClient']. '/'. $TabFileLog['Dom']. '/'. $TabFileLog['SousDom']. '/'. C_PATH_ERROR_PHP. '"');
	if (is_file(C_PATH_HTTP. $TabFileLog['CdClient']. '/'. $TabFileLog['Dom']. '/'. $TabFileLog['SousDom']. '/'. C_PATH_ERROR_PHP_MAIL))
		shell_exec(C_SUDO. ' sh -c "> '. C_PATH_HTTP. $TabFileLog['CdClient']. '/'. $TabFileLog['Dom']. '/'. $TabFileLog['SousDom']. '/'. C_PATH_ERROR_PHP_MAIL. '"');

	#----------------------------------------------------
	# Etape 2.3 : Sauvegarde Mysql
	#----------------------------------------------------

	$RepSauvMysql = C_PATH_HTTP. $TabFileLog['CdClient']. '/'. $TabFileLog['Dom']. '/'. $TabFileLog['SousDom']. '/'. C_REP_SAUV_MYSQL;

	if (is_dir($RepSauvMysql))
	{
		require_once(ROOT_DIR. '/class/class_BatchMysql.php');
		$o_BatchMysql = new c_BatchMysql(false);
		$FileName = $o_BatchMysql->DumpDatabase($TabFileLog['Dom'], $TabFileLog['SousDom'], $RepSauvMysql);

		# Compression
		f_ZipFile($RepSauvMysql. $FileName);

		# Suppression des anciens
		chdir($RepSauvMysql);
		$NbSav = shell_exec('ls '. $o_BatchMysql->DatabaseName. '*.sql.zip 2>/dev/null | wc -l');
		if ($NbSav > C_NB_SAUV_MYSQL)
		{
			$NbSup = $NbSav - C_NB_SAUV_MYSQL;
			system('rm -f $(ls '. $o_BatchMysql->DatabaseName. '*.sql.zip -rt | head -n '. $NbSup. ')');
		}
	}
}

# je vide les log d'erreur du site par default
shell_exec(C_SUDO. ' sh -c "> '. C_PATH_HTTP. '../default.site/'. C_PATH_ERROR_APACHE. '"');
if (is_file(C_PATH_HTTP. '../default.site/'. C_PATH_ERROR_PHP))
	shell_exec(C_SUDO. ' sh -c "> '. C_PATH_HTTP. '../default.site/'. C_PATH_ERROR_PHP. '"');
if (is_file(C_PATH_HTTP. '../default.site/'. C_PATH_ERROR_PHP_MAIL))
	shell_exec(C_SUDO. ' sh -c "> '. C_PATH_HTTP. '../default.site/'. C_PATH_ERROR_PHP_MAIL. '"');

exit(0);

#-------------------------------------------------------
# FONCTION
#-------------------------------------------------------

# On compresse un fichier
function f_ZipFile($p_file)
{
	$FileName = basename($p_file);
	$DirName = dirname($p_file);
	shell_exec('cd '. $DirName. '; '. C_ZIP. ' -9 -q '. $FileName. '.zip '. $FileName. ' && '. C_Rm. ' -f '.$FileName);
}

?>
