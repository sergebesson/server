<?php

class	c_BatchMysql
{
	//------------------------------------------------------------------//
	//               Les attributs de l'objet                           //
	//------------------------------------------------------------------//
	const C_BatchMysql      = '$Id$';
	const C_MysqlDump       = '/usr/bin/mysqldump';
	const C_Mysql           = '/usr/bin/mysql';
	public $DatabaseName;
	private $Host;
	private $User;
	private $Mdp;
	private $CnxMysql;

	//------------------------------------------------------------------//
	//               Le constructeur/destructeur de l'objet             //
	//------------------------------------------------------------------//
	# constructeur de l'objet
	public function __construct($p_Cnx=true)
	{
		$this->Host = 'localhost';
		$this->User = 'root';
		$this->Mdp  = '{{PASSWD}}';

		if ($p_Cnx)
		{
			!$this->CnxMysql = new mysqli($this->Host, $this->User, $this->Mdp);
			if ($this->CnxMysql->connect_errno)
			{
				throw new Exception
				(
					"Erreur lors de la connection à Mysql.\n\n".
					"Mysql a retourne l'erreur suivante :\n".
					$this->CnxMysql->connect_error
				);
			}
		}
	}
	# destructeur de l'objet
	public function __destruct()
	{
	}

	//------------------------------------------------------------------//
	//               Les méthodes de l'objet public                     //
	//------------------------------------------------------------------//
	# Création User et base Mysql pour new hébergement
	public function CreationUserMysql($p_Domaine, $p_SousDomaine, $p_Login, $p_Mdp)
	{
		$NameDataBase = $this->CalDatabaseName($p_Domaine, $p_SousDomaine);

		$this->Query("CREATE DATABASE `". $NameDataBase. "` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci");
		$this->Query("CREATE USER '". $p_Login. "'@'localhost' IDENTIFIED BY '". $p_Mdp. "'");
		$this->Query("GRANT FILE ON *.* TO '". $p_Login. "'@'localhost' IDENTIFIED BY '". $p_Mdp. "' WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0");
		$this->Query("GRANT ALL PRIVILEGES ON `". str_replace('_', '\_', $NameDataBase). "`.* TO '". $p_Login. "'@'localhost'");
	}

	public function CalDatabaseName($p_Domaine, $p_SousDomaine)
	{
		$this->DatabaseName = str_replace('.', '_', $p_SousDomaine. '.'. $p_Domaine);
		return $this->DatabaseName;
	}

	# Dump d'une base Mysql
	public function DumpDatabase($p_Domaine, $p_SousDomaine, $p_RepSav)
	{
		$NameDataBase = $this->CalDatabaseName($p_Domaine, $p_SousDomaine);
		$NameFile = $NameDataBase. '_'. date('Y_m_d'). '.sql';

		unset($outPut);
 		exec(self::C_MysqlDump. ' -u'. $this->User. ' -p\''. $this->Mdp. '\' -h '. $this->Host. ' -Q '. $NameDataBase. ' > '. $p_RepSav. '/'. $NameFile, $outPut, $cdRet);

		if ($cdRet == 0)
		{
			return $NameFile;
		}
		else
		{
			return array($cdRet, $outPut);
		}
	}

	# Restauration d'une base Mysql
	public function RestoreDatabase($p_Domaine, $p_SousDomaine, $p_NameFile)
	{
		$NameDataBase = $this->CalDatabaseName($p_Domaine, $p_SousDomaine);

		unset($outPut);
 		exec(self::C_Mysql. ' -B -u'. $this->User. ' -p\''. $this->Mdp. '\' -h '. $this->Host. ' -D '. $NameDataBase. ' < '. $p_NameFile, $outPut, $cdRet);

		if ($cdRet == 0)
		{
			return true;
		}
		else
		{
			return array($cdRet, $outPut);
		}
	}

	# Selection d'une base de données
	public function SelectDataBase($p_DataBase)
	{
		if (!$this->CnxMysql->select_db($p_DataBase))
		{
			throw new Exception
			(
				"Erreur lors du mysql_select_db($p_DataBase)\n".
				"Mysql a retourne l'erreur suivante :".
				$this->CnxMysql->error
			);
		}
	}

	# Méthode qui execute une requête !
	public function Query($p_Rsq)
	{
		if (!$res = $this->CnxMysql->query($p_Rsq))
		{
			throw new Exception
			(
				"Erreur execution d'une requête mysql\n\n".
				"Requête : $p_Rsq\n".
				"Erreur Mysql : ". $this->CnxMysql->error
			);
		}

		return $res;
	}

	# Méthode qui execute une requête qui peut retourner une erreur que l'on veut gérer
	public function QueryErreur($p_Rsq)
	{
		$res = $this->CnxMysql->query($p_Rsq);
		return $res;
	}

	# Méthode qui fait un select qui retourne 1 seule ligne, la méthode retourne alors
	# un tableau de hachage comportant cette ligne
	# return false si 0 element retourne
	public function SelectOne($p_Rsq)
	{
		$res = $this->Query($p_Rsq);
		if ($res->num_rows($res) < 1)
			return false;
		else
			return $res->fetch_assoc($res);
	}

	# Méthode qui execute un select retournant 2 champs et retourne le résultat dans un tableau hachage
	# Le 1er champ doit être la cle et le 2eme la valeur
	# ex : si vous voulez faire ca :
	#	$res = $this->m_Query($rsq, __file__, __line__);
	#	while($elt = mysql_fetch_row($res))
	#			$Tab[$elt[0]]=$elt[1];
	# Faire :
	#	$Tab = $o_Connexion->m_SelectTab($rsq, __file__, __line__);
	public function SelectTab($p_Rsq)
	{
		$TabReturn=array();
		$res = $this->Query($p_Rsq);
		while($elt = $res->fetch_row())
			$TabReturn[$elt[0]] = $elt[1];

		return $TabReturn;
	}

	# Méthode qui execute un select et retourne le résultat dans un tableau hachage
	# ex : si vous voulez faire ca :
	#	$res = $this->m_Query($rsq, __file__, __line__);
	#	while($elt = mysql_fetch_array($res, MYSQL_ASSOC))
	#		$Tab[$elt['cle']]=$elt;
	# Faire :
	#	$Tab = $o_Connexion->m_SelectTabHach($rsq, 'cle', __file__, __line__);
	public function SelectTabHach($p_Rsq, $p_Cle)
	{
		$TabReturn=array();
		$res = $this->Query($p_Rsq);
		while($elt =$res->fetch_assoc($res))
			if ($p_Cle != '')
				$TabReturn[$elt[$p_Cle]] = $elt;
			else
				$TabReturn[] = $elt;

		return $TabReturn;
	}

	//------------------------------------------------------------------//
	//               Les méthodes de l'objet prives                     //
	//------------------------------------------------------------------//
}

?>
