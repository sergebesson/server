<?php
//------------------------------------------------------------------//
//                      FileConf                                    //
//------------------------------------------------------------------//
// ver : 1.1
// dat : 02-06-2009
// aut : sbesson
// but : Gestion d'un fichier de configuration
//
// 02-06-2009 : 1.1 : Ajout de la liste des rubriques et mise en place de supVide
//

class FileConf
{
	//------------------------------------------------------------------//
	//               Les attributs de l'objet                           //
	//------------------------------------------------------------------//
	private $FileConfVersion = '1.1';

	// Les constantes
	# La rubrique par defaut
	const RUB_DEFAULT = '__DEFAULT__';

	// Les attributs
	# Tableau contenant tous les parametres
	private $tabParam = array();
	# Liste des rubriques
	private $lstRub = array();

	//------------------------------------------------------------------//
	//               Le constructeur de l'objet                         //
	//------------------------------------------------------------------//
	# constructeur de l'objet
	# On donne le fichier de configuration, on merorise tous les parametres
	# en memoire dans le tableau tabParam
	public function __construct($fileCfg)
	{
		$rub = self::RUB_DEFAULT;
		$modeBloc = false;
		if ($fd = fopen($fileCfg, "r"))
		{
			while (!feof($fd))
			{
				$line = fgets($fd, 10240);
				if ($modeBloc)
				{
					if (trim($line) == "</". $bloc. ">")
					{
						$modeBloc = false;
						$this->tabParam[$rub][$bloc] = $val;
					}
					else
					{
						if ((substr(trim($line), 0 ,1) != '#') and (substr(trim($line), 0 ,2) != '//'))
						{
							$val .= $line;
						}
					}
				}
				else
				{
					$line = trim($line);
					if (($line != "") and (substr($line, 0 ,1) != '#') and (substr($line, 0 ,2) != '//'))
					{
						if (substr($line, 0 ,1) == '<')
						{
							$modeBloc=true;
							$bloc=substr($line, 1, strlen($line) - 2);
							$val="";
						}
						elseif (substr($line, 0 ,1) == '[')
						{
							$rub = trim(substr($line, 1, -1));
							$this->lstRub[] = $rub;
						}
						else
						{
							list($key, $val) = explode("=", $line, 2);
							$this->tabParam[$rub][trim($key)] = trim($val);
						}
					}
				}
			}
		}
		else
		{
			throw new Exception('Erreur ouverture du fichier de configuration ('. $fileCfg. ')');
		}
	}

	//------------------------------------------------------------------//
	//               Les methodes de l'objet                            //
	//------------------------------------------------------------------//
	# METHODE : getLstRub()
	# Permet de recupérer la liste des rubriques.
	# retourne un tableau (liste) des rubriques
	public function getLstRub()
	{
		return $this->lstRub;
	}

	# METHODE : getParam($rub, $nom, $siList=false)
	# Permet de recupérer la valeur d'un parametre.
	# $rub => rubrique du parametre
	# $nom => nom du parametre
	# $siList => si le parametre est une liste de valeur separer par une virgule
	#              mettre se parametre a true, par defaut à false.
	# retourne la valeur du parametre ou un tableau de valeur si $siList = true ou false si paramétre inconue
	public function getParam($rub, $nom, $siList=false, $separateur=',', $supVide=true)
	{
		if ($siList)
		{
			$var=array();
			if (isset($this->tabParam[$rub][$nom]))
			{
				foreach (explode($separateur, $this->tabParam[$rub][$nom]) as $val)
				{
					if (($val = trim($val)) != "" or !$supVide)
					{
						$var[]=$val;
					}
				}
			}
			return $var;
		}
		else
		{
			if (isset($this->tabParam[$rub][$nom]))
			{
				return $this->tabParam[$rub][$nom];
			}
			return '';
		}
	}

	# METHODE : getParamDef($nom, $siList=false)
	# Permet de recupérer la valeur d'un parametre de la rubrique DEFAUT.
	# $nom => nom du parametre
	# $siList => si le parametre est une liste de valeur separer par une virgule
	#              mettre se parametre a true.
	# retourne la valeur du parametre ou un tableau de valeur si $siList = true
	function	getParamDef($nom, $siList=false, $separateur=',', $supVide=true)
	{
		return $this->getParam(self::RUB_DEFAULT, $nom, $siList, $separateur, $supVide);
	}

	# METHODE : getParamRub($rub=self::RUB_DEFAULT)
	# Permet de recuperer tous les parametres d'une rubrique
	# $p_rub => nom de la rubrique
	# Retourne un tableau contenant tous les parametres de la rubrique
	# (en cle le nom du parametre, en valeur le contenu du parametre)
	# Attention, ne gere pas les parametre danc la valeur est une liste de
	# valeur separe par une virgule.
	function	getParamRub($rub=self::RUB_DEFAULT)
	{
		if (isset($this->tabParam[$rub]))
		{
			return $this->tabParam[$rub];
		}
		return array();
	}
	# METHODE : getParamVar(&$var, $rub, $nom, $siList=false)
	# Met a jour une variable avec le contenu d'un parametre si ce dernier existe
	# sinon ne change pas la variable
	# $var => variable passe en reference pour etre mise a jour
	# $rub => rubrique du parametre
	# $nom => nom du parametre
	# $siList => si le parametre est une liste de valeur separer par une virgule
	#              mettre se parametre a true.
	# ne retourne rien, mais met a jour si besoin $var
	function	getParamVar(&$var, $rub, $nom, $siList=false, $separateur=',', $supVide=true)
	{
		if (isset($this->tabParam[$rub][$nom]))
		{
			$var = $this->getParam($rub, $nom, $siList, $separateur, $supVide);
		}
	}
	# METHODE : getParamVarDef(&$var, $nom, $siList=false)
	# Met a jour une variable avec le contenu d'un parametre si ce dernier existe
	# sinon ne change pas la variable pour la rubrique DEFAUT
	# $var => variable passe en reference pour etre mise a jour
	# $nom => nom du parametre
	# $siList => si le parametre est une liste de valeur separer par une virgule
	#              mettre se parametre a true.
	# ne retourne rien, mais met a jour si besoin $var
	function	getParamVarDef(&$var, $nom, $siList=false, $separateur=',', $supVide=true)
	{
		$this->getParamVar($var, self::RUB_DEFAULT, $nom, $siList, $separateur, $supVide);
	}
}
?>