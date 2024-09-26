<?php
//------------------------------------------------------------------//
//                      GetOpt                                      //
//------------------------------------------------------------------//
// ver : 1.1
// dat : 09-09-2009
// aut : sbesson
// but : Gestion des options standard en ligne de commande
//
// 2009-09-09 :
//   supression de setHelpDescriptionOption
//   Ajout de addOpt
//   Modification de getOpt suppression du seul paramétre $strOpt
//

class GetOpt
{
	//------------------------------------------------------------------//
	//               Les attributs de l'objet                           //
	//------------------------------------------------------------------//
	private $GetOptVersion = '1.1';

	// Les attributs
	private $log = NULL;
	private $pgmName = '';
	private $pgmVersion = '';
	private $fileConf = '';
	private $modeDebug = false;
	private $helpDescription = '';
	private $tabOptDescription = NULL;
	private $strOptSupp = '';
	private $tailleOption = 10;

	//------------------------------------------------------------------//
	//               Le constructeur de l'objet                         //
	//------------------------------------------------------------------//
	# constructeur de l'objet
	public function __construct($log, $pgmName, $pgmVersion, $helpDescription)
	{
		$this->log = $log;
		$this->pgmName = $pgmName;
		$this->pgmVersion = $pgmVersion;
		$this->helpDescription = $helpDescription;
		$this->fileConf = '/etc/hew/'. $this->pgmName. '.conf';
		$this->tabOptDescription = array
		(
			array
			(
				'opt'  => '-d',
				'desc' => 'Mode debug'
			),
			array
			(
				'opt'  => '-h',
				'desc' => 'Retourne cette aide'
			),
			array
			(
				'opt'  => '-v',
				'desc' => 'Affiche le numéro de version'
			),
			array
			(
				'opt'  => '-c fichier',
				'desc' => 'Permet de spécifier un fichier de configuration spécifique, Par défaut '. $this->fileConf
			),
		);
		$this->tailleOption = 10;
	}

	//------------------------------------------------------------------//
	//               Les methodes de l'objet                            //
	//------------------------------------------------------------------//
	# METHODE : addOpt($opt, $optDescription, $param=false)
	public function addOpt($opt, $description, $param=false)
	{
		$opt = trim($opt);
		$this->tabOptDescription[] = array
		(
			'opt'  => '-'. $opt,
			'desc' => $description
		);
		if (strlen($opt) + 1 > $this->tailleOption)
		{
			$this->tailleOption = strlen($opt) + 1;
		}
		$this->strOptSupp .= $opt[0]. (($param)?':':'');
	}

	# METHODE : getOpt($strOpt)
	public function getOpt($strOpt='')
	{
		$options = getopt('dc:vh'. $this->strOptSupp);
		if (isset($options['d']))
		{
			$this->log->setDebug($this->modeDebug=true);
			unset($options['d']);
		}
		$this->log->debug('Commande : '. implode(' ', $_SERVER['argv']));
		foreach($options as $option => $valOption)
		{
			switch ($option)
			{
				case 'v':
					echo 'version: '. $this->pgmVersion. "\n";
					exit(1);
					break;
				case 'h':

					define('ROOT_DIR', dirname($argv[0]));
					require(ROOT_DIR. '/../commun/class_StringTools.php');
					$stringTools = new StringTools();

					echo 'Usage: '. $this->pgmName. ' [OPTION...]'. "\n";
					echo implode("\n", $stringTools->strDecoupeMot($this->helpDescription, 78)). "\n";
					echo "\n";
					foreach($this->tabOptDescription as $optionDescription)
					{
						$lstLigneDesc = $stringTools->strDecoupeMot($optionDescription['desc'], 76 - $this->tailleOption);
						printf('%-'. $this->tailleOption. 's  %s'. "\n", $optionDescription['opt'], array_shift($lstLigneDesc));
						foreach($lstLigneDesc as $ligneDesc)
						{
							echo str_repeat(' ', $this->tailleOption + 2). $ligneDesc. "\n";
						}
					}
/*					echo '-d           Mode debug'. "\n";
					echo '-h           Retourne cette aide'. "\n";
					echo '-v           Affiche le numéro de version'. "\n";
					echo '-c fichier   Permet de spécifier un fichier de configuration spécifique'. "\n";
					echo '             Par défaut /etc/hew/backup.conf'. "\n";*/
					exit(1);
					break;
				case 'c':
					$this->log->debug('Option c trouvée');
					$this->fileConf = $valOption;
					unset($options['c']);
					break;
			}
		}
		$this->log->debug('fichier de configuration : '. $this->fileConf);

		return $options;
	}

	# METHODE : getFileConf()
	public function getFileConf()
	{
		return $this->fileConf;
	}

	# METHODE : getModeDebug()
	public function getModeDebug()
	{
		return $this->modeDebug;
	}
}