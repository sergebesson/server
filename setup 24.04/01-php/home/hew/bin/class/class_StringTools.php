<?php
//------------------------------------------------------------------//
//                      StringTools                                    //
//------------------------------------------------------------------//
// ver : 1.0
// dat : 02-06-2009
// aut : sbesson
// but : Outils pour gérer les strings
//

class StringTools
{
	//------------------------------------------------------------------//
	//               Les attributs de l'objet                           //
	//------------------------------------------------------------------//
	private $StringToolsVersion = '1.0';

	//------------------------------------------------------------------//
	//               Le constructeur de l'objet                         //
	//------------------------------------------------------------------//
	# constructeur de l'objet
	public function __construct()
	{
	}

	# destructeur de l'objet
	public function __destruct()
	{
	}

	//------------------------------------------------------------------//
	//               Les methodes de l'objet                            //
	//------------------------------------------------------------------//
	# METHODE : arrayKeyMaj($tab)
	# Passe toutes les cles d'un tableau en majuscule
	# $tab => tableau a traiter
	# retourne le tableau modifier
	public function	arrayKeyMaj($tab)
	{
		$tabRet = array();
		foreach ($tab as $key => $val)
		{
			$tabRet[strtoupper($key)] = $val;
		}

		return $tabRet;
	}
	# METHODE : arrayValMaj($tab)
	# Passe toutes les valeur d'un tableau en majuscule
	# $tab => tableau a traiter
	# retourne le tableau modifier
	public function	arrayValMaj($tab)
	{
		foreach ($tab as $key => $val)
		{
			$tab[$key] = strtoupper($val);
		}
		return $tab;
	}
	# METHODE : arrayAllMaj($tab)
	# Passe toutes les cle et les valeurs d'un tableau en majuscule
	# $tab => tableau a traiter
	# retourne le tableau modifier
	public function	arrayAllMaj($tab)
	{
		$tabRet = array();
		foreach ($tab as $key => $val)
		{
			$tabRet[strtoupper($key)] = strtoupper($val);
		}

		return $tabRet;
	}
	# METHODE : AarrayKeyMin($tab)
	# Passe toutes les cles d'un tableau en minuscule
	# $tab => tableau a traiter
	# retourne le tableau modifier
	public function	arrayKeyMin($tab)
	{
		$tabRet = array();
		foreach ($tab as $key => $val)
		{
			$tabRet[strtolower($key)] = $val;
		}

		return $tabRet;
	}
	# METHODE : arrayValMin($tab)
	# Passe toutes les valeur d'un tableau en minuscule
	# $tab => tableau a traiter
	# retourne le tableau modifier
	public function	arrayValMin($tab)
	{
		foreach ($tab as $key => $val)
		{
			$tab[$key] = strtolower($val);
		}

		return $tab;
	}
	# METHODE : arrayAllMin($p_tab)
	# Passe toutes les cle et les valeurs d'un tableau en minuscule
	# $p_tab => tableau a traiter
	# retourne le tableau modifier, si l'on passe le tableau en ref (&$tab)
	# Le tableau sera aussi modifié
	public function	arrayAllMin($tab)
	{
		$tabRet = array();
		foreach ($tab as $key => $val)
		{
			$tabRet[strtolower($key)] = strtolower($val);
		}

		return $tabRet;
	}
	# METHODE : arrayTrim($tab)
	# Trim toutes les valeurs d'un tableau
	# $tab => tableau a traiter (peut-etre une chaine de caractere)
	# retourne le tableau modifier
	public function	arrayTrim($tab)
	{
		if (is_array($tab))
		{
			foreach ($tab as $key => $val)
			{
				$tab[$key] = $this->arrayTrim($val);
			}
		}
		else
		{
			$tab = trim($tab);
		}

		return $tab;
	}
	# METHODE : strim($tab, $replace = ' ')
	# trim et enleve les doublons et remplace les espaces par $replace si renseigné
	# $tab => tableau a traiter (peut-etre une chaine de caractere)
	# retourne le tableau ou la chaine modifier
	public function	strim($tab, $replace = ' ')
	{
		if (is_array($tab))
		{
			foreach ($tab as $key => $val)
			{
				$tab[$key] = $this->strim($val, $replace);
			}
		}
		else
		{
			$tab = preg_replace("/\s+/", $replace, trim($tab));
		}

		return $tab;
	}
	# METHODE : arrayQuotesEncode($tab)
	# Protege tous les caracteres neccessaire pour faire une requete
	# de tous les champs du tableau $tab
	# $tab => tableau a traiter (peut-etre une chaine de caractere)
	# retourne le tableau ou la chaine modifier
	public function arrayQuotesEncode($tab)
	{
		if (is_array($tab))
		{
			foreach($tab as $key => $val)
			{
				$tab[$key] = $this->arrayQuotesEncode($val);
			}
		}
		else
		{
			$tab = addslashes($tab);
		}

		return $tab;
	}
	# METHODE : arrayQuotesDecode($tab)
	# Deprotege tous les caracteres neccessaire pour faire une requete
	# de tous les champs du tableau $tab
	# $tab => tableau a traiter (peut-etre une chaine de caractere)
	# retourne le tableau ou la chaine modifier
	public function	arrayQuotesDecode($tab)
	{
		if (is_array($tab))
		{
			foreach($tab as $key => $val)
			{
				$tab[$key] = $this->arrayQuotesDecode($val);
			}
		}
		else
		{
			$tab = stripslashes($tab);
		}

		return $tab;
	}
	# METHODE : parseTpl($tab, $tpl)
	# Parse la template $tpl avec les elements du tableau $tab (dans la template les variables
	# doivent-etre encadre d'accolate ! ex : {titre}
	# $tab => Tableau de hachage des valeurs a transformer
	# $tpl => Template a transformer
	# retourne la template modifiée.
	public function	parseTpl($tab, $tpl)
	{
		foreach ($tab as $key => $val)
		{
			if (is_scalar($val))
			{
				$tpl = str_replace("{". $key. "}", $val, $tpl);
			}
		}
		return $tpl;
	}
	# METHODE : parseTplLst($ligne, $tpl, $nbcols=1, $fct="", $obj="")
	# Permet de creer des lignes de template !
	# $ligne => doit contenir soit le resultat d'un select mysql
	#           soit un tableau de tableau de hachage contenant les champs a remplacer
	# $tpl   => la template correspondant a 1 ligne (elle sera repete autant de fois que
	#           necessaire
	# $nbcols => nombre de colonne dans $tpl
	# $fct   => si renseigne, nom de la fonction a appeler a chaque element contenu dans $ligne
	#           Pour formatage des donnees ou creation de new champ
	# $obj   => si renseigne, alors $fct est une methode de l'object $obj
	# retourne la template parse !
	public function	parseTplLst($ligne, $tpl, $nbcols=1, $fct="", $obj="")
	{
		# C'est la template de resultat !
		# Celle qui sera retourné par la methode
		$r_tpl="";
		# C'est le n° de col en cour
		$col_en_cour=0;
		# On va mettre dans ce tableau la liste de tous les champs a remplacer
		# Ceci pour finir les colonne dans le cas de plusieurs colonne
		$tab_keys = array();
		# Tableau temporaire de tous les champs a remplacer avec le N° de colonne
		# EX : s'il existe le champ my_champ et que la template possede 3 colonne
		#      alors tab_temp possedera les champs my_champ_1, my_champ_2 et my_champ_3
		$tab_temp = array();
		# Tableau contenant les champs des lignes a créer (idem $ligne si $ligne n'est pas
		# le resultat d'une requete).
		$tab_enr = array();

		# mise a jour de tab_enr !
		if (is_resource($ligne))
		{
			while($elt = mysql_fetch_array($ligne, MYSQL_ASSOC))
			{
				$tab_enr[]=$elt;
			}
		}
		else
		{
			$tab_enr=$ligne;
		}
		reset($tab_enr);

		# Gestion des templates ! Soit on a 1 template soit une liste
		# dans le dernier cas, le 1er element de $tpl est la template
		# principale.
		# dans tous les cas, $w_tpl doit contenir la template principale
		# et $tpl les autres.
		if (is_array($tpl))
		{
			$w_tpl=array_shift($tpl);
		}
		else
		{
			$w_tpl=$tpl;
			$tpl=array();
		}

		# -------- gestion de la 1er ligne (1er element de $tab_enr)
		# Recuperation du 1er element
		if (!($elt = current($tab_enr))) return false;

		# appel la fonction pour des mise en forme possible
		# et creation de nouveau element ! :)
		if ($obj != "")
		{
			$obj->$fct($elt);
		}
		elseif ($fct != "")
		{
			$fct($elt);
		}

		# Création des elements suplementaire a partir des templates
		# du tableau $tpl
		foreach ($tpl as $key => $val)
		{
			$elt[$key] = $this->parseTpl($elt, $val);
		}

		# recuperation de toutes les cles de elt
		# On connait comme ca tous les champs a remplacer :)
		$tab_keys = array_keys($elt);

		# On test si l'on doit gerer plusieurs colonne
		if ($nbcols > 1)
		{
			# on gere les colonnes, donc creation dans $tab_temp
			# des champs avec le N° de colonne
			foreach ($elt as $key => $val)
			{
				$tab_temp[$key. "_". ($col_en_cour + 1)] = $val;
			}
			++$col_en_cour;
		}
		else
		{
			# on ne gere pas de colonne, alors on parse la template qu'on concatene a r_tpl
			# et on fait un eval pour remplacer certaine variable en global :)
			eval('$r_tpl .= "'. addcslashes($this->parseTpl($elt, $w_tpl), '"'). '";');
		}

		# -------- gestion des autres lignes
		while($elt = next($tab_enr))
		{
			# appel la fonction pour des mise en forme possible
			if ($obj != "")
			{
				$obj->$fct($elt);
			}
			elseif ($fct != "")
			{
				$fct($elt);
			}

			# Création des elements suplementaire a partir des templates
			# du tableau $p_tpl
			foreach ($tpl as $key => $val)
			{
				$elt[$key] = $this->parseTpl($elt, $val);
			}

			# On test si l'on doit gerer plusieurs colonne
			if ($nbcols > 1)
			{
				# on gere les colonnes, donc creation dans $tab_temp
				# des champs avec le N° de colonne
				foreach ($elt as $key => $val)
				{
					$tab_temp[$key. "_". ($col_en_cour + 1)] = $val;
				}
				#gestion des colonnes, si besoin on parse la template !
				if (++$col_en_cour >= $nbcols)
				{
					eval('$r_tpl .= "'. addcslashes($this->parseTpl($tab_temp, $w_tpl), '"'). '";');
					$col_en_cour=0;
				}
			}
			else
			{
				# on ne gere pas de colonne, alors on parse la template qu'on concatene a r_tpl
				# et on fait un eval pour remplacer certaine variable en global :)
				eval('$r_tpl .= "'. addcslashes($this->parseTpl($elt, $w_tpl), '"'). '";');
			}
		}
		if ($col_en_cour)
		{
			for ($col_en_cour++; $col_en_cour <= $nbcols; $col_en_cour++)
			{
				foreach ($tab_keys as $key)
				{
					$tab_temp[$key. "_". $col_en_cour] = "";
				}
			}
			eval('$r_tpl .= "'. addcslashes($this->parseTpl($tab_temp, $w_tpl), '"'). '";');
		}
		return $r_tpl;
	}
	# METHODE : strDecoupeMot($str, $largeur)
	# Decoupe une chaine de caractere en ligne de largeur max $largeur sans couper de mot.
	# Si un mot contient plus de $largeur caractere alors le mot sera coupé a $largeur !
	# $str     => Doit contenir la chaine a decouper !
	# $largeur => Largeur max d'une ligne
	# retourne un tableau contenant toutes les lignes
	public function	strDecoupeMot($str, $largeur)
	{
		# On control que $largeur soit plus grand que zero !
		# Si c'est pas le cas on retourne un tableau vide !
		if ($largeur < 1)
		{
			return array();
		}

		# on supprime tous les espaces en trop (debut de chaine, fin de chaine et doublon)
		$str = $this->strim($str);
		# On decoupe en mot
		$tab_mot = explode(" ", $str);

		# le tableau des lignes
		$tab_ligne = array();
		$tab = array();
		foreach ($tab_mot as $mot)
		{
			if (count($tab) > 0 and strlen(implode(" ", array_merge($tab, array($mot)))) > $largeur)
			{
				$tab_ligne[] = implode(" ", $tab);
				$tab = array();
			}

			while (strlen($mot) > $largeur)
			{
				$tab_ligne[] = substr($mot, 0, $largeur);
				$mot = substr($mot, $largeur);
			}
			$tab[] = $mot;
		}
		if (count($tab) > 0)
		{
			$tab_ligne[] = implode(" ", $tab);
		}

		return $tab_ligne;
	}

	# METHODE : formatDateMysql($dateMysql, $formatDate, $lang)
	# retourne une date formaté en fonction de $dateMysql (date au format mysql aaaa-mm-jj) et de
	# $formatDate, le tout dans la langue $lang
	# $formatDate contient le format a utilise ex : %d/%m/%Y pour avoir une date au format jj/mm/aaaa
	# Code utilisable :
	# 	%a - nom abrégé du jour de la semaine. Affichage different en fonction de $p_Lang
	# 	%A - nom complet du jour de la semaine. Affichage different en fonction de $p_Lang
	# 	%b - nom abrégé du mois. Affichage different en fonction de $p_Lang
	# 	%B - nom complet du mois. Affichage different en fonction de $p_Lang
	# 	%C - Numéro de siècle (l'année, divisée par 100 et arrondie entre 00 et 99)
	# 	%d - jour du mois en numérique (intervalle 00 à 31)
	# 	%D - same as %m/%d/%y
	# 	%e - numéro du jour du mois. Les chiffres sont précédés d'un espace ( de ' 1' à '31')
	# 	%h - identique à %b
	# 	%j - jour de l'année, en numérique (intervalle 001 à 366)
	# 	%m - mois en numérique (intervalle 1 à 12)
	# 	%n - newline character
	# 	%t - tabulation
	# 	%u - le numéro de jour dans la semaine, de 1 à 7. (1 représente Lundi)
	# 	%U - numéro de semaine dans l'année, en considérant le premier dimanche de l'année comme le premier jour de la première semaine.
	# 	%V - le numéro de semaine comme défini dans l'ISO 8601:1988, sous forme décimale, de 01 à 53. La semaine 1 est la première semaine qui a plus de 4 jours dans l'année courante, et dont Lundi est le premier jour.
	# 	%W - numéro de semaine dans l'année, en considérant le premier lundi de l'année comme le premier jour de la première semaine
	# 	%w - jour de la semaine, numérique, avec Dimanche = 0
	# 	%x - format préféré de représentation de la date sans l'heure
	# 	%y - l'année, numérique, sur deux chiffres (de 00 à 99)
	# 	%Y - l'année, numérique, sur quatre chiffres
	# 	%Z - fuseau horaire, ou nom ou abréviation
	# 	%% - un caractère `%' littéral
	# $lang est vide par defaut et dans ce cas la langue utilisé est celle de la variable d'environnement LANG
	# Donc de la langue par defaut du serveur. Pour connaitre tous les codes langues utilisable, executer la commande
	# locale -a sur le serveur.
	public function	formatDateMysql($dateMysql, $formatDate, $lang='')
	{

		# On configure la langue
		setlocale (LC_TIME, $lang);

		# Je formate la date :)
		list($aa, $mm, $jj)=explode("-", $dateMysql);
		return strftime($formatDate, mktime(0,0,0,$mm, $jj, $aa));
	}

	# METHODE : formatDateTimeMysql($dateTimeMysql, $formatDate, $lang)
	# retourne une date formaté en fonction de $dateTimeMysql (date au format mysql aaaa-mm-jj hh:mm:ss) et de
	# $formatDate, le tout dans la langue $lang
	# $formatDate contient le format a utilise ex : %d/%m/%Y pour avoir une date au format jj/mm/aaaa
	# Code utilisable :
	# 	%a - nom abrégé du jour de la semaine. Affichage different en fonction de $p_Lang
	# 	%A - nom complet du jour de la semaine. Affichage different en fonction de $p_Lang
	# 	%b - nom abrégé du mois. Affichage different en fonction de $p_Lang
	# 	%B - nom complet du mois. Affichage different en fonction de $p_Lang
	#	%c : représentation préférée pour les dates et heures, en local.
	# 	%C - Numéro de siècle (l'année, divisée par 100 et arrondie entre 00 et 99)
	# 	%d - jour du mois en numérique (intervalle 00 à 31)
	# 	%D - same as %m/%d/%y
	# 	%e - numéro du jour du mois. Les chiffres sont précédés d'un espace ( de ' 1' à '31')
	#	%g : identique à %G, sur 2 chiffres.
	#	%G : L'année sur 4 chiffres correspondant au numéro de semaine (voir %V). Même format et valeur que %Y, excepté que si le numéro de la semaine appartient à l'année précédente ou suivante, l'année courante sera utilisé à la place.
	# 	%h - identique à %b
	#	%H : heure de la journée en numérique, et sur 24-heures (intervalle de 00 à 23)
	#	%I : heure de la journée en numérique, et sur 12- heures (intervalle 01 à 12)
	# 	%j - jour de l'année, en numérique (intervalle 001 à 366)
	# 	%m - mois en numérique (intervalle 1 à 12)
	#	%M : minute en numérique
	# 	%n - newline character
	#	%p : soit `am' ou `pm' en fonction de l'heure absolue, ou en fonction des valeurs enregistrées en local.
	#	%r : l'heure au format a.m. et p.m.
	#	%R : l'heure au format 24h
	#	%S : secondes en numérique
	# 	%t - tabulation
	#	%T : l'heure actuelle (égal à %H:%M:%S)
	# 	%u - le numéro de jour dans la semaine, de 1 à 7. (1 représente Lundi)
	# 	%U - numéro de semaine dans l'année, en considérant lprivatee premier dimanche de l'année comme le premier jour de la première semaine.
	# 	%V - le numéro de semaine comme défini dans l'ISO 8601:1988, sous forme décimale, de 01 à 53. La semaine 1 est la première semaine qui a plus de 4 jours dans l'année courante, et dont Lundi est le premier jour.
	# 	%W - numéro de semaine dans l'année, en considérant le premier lundi de l'année comme le premier jour de la première semaine
	# 	%w - jour de la semaine, numérique, avec Dimanche = 0
	# 	%x - format préféré de représentation de la date sans l'heure
	#	%X : format préféré de représentation de l'heure sans la date
	# 	%y - l'année, numérique, sur deux chiffres (de 00 à 99)
	# 	%Y - l'année, numérique, sur quatre chiffres
	# 	%Z - fuseau horaire, ou nom ou abréviation
	# 	%% - un caractère `%' littéral
	# $lang est vide par defaut et dans ce cas la langue utilisé est celle de la variable d'environnement LANG
	# Donc de la langue par defaut du serveur. Pour connaitre tous les codes langues utilisable, executer la commande
	# locale -a sur le serveur.
	public function	formatDateTimeMysql($dateTimeMysql, $formatDate, $lang='')
	{
		# On configure la langue
		setlocale (LC_TIME, $lang);

		# Je formate la date :)
		list($date, $time)=explode(" ", $dateTimeMysql);
		list($aa, $mm, $jj)=explode("-", $date);
		list($hh, $mn, $ss)=explode(":", $time);
		return strftime($formatDate, mktime($hh, $mn, $ss, $mm, $jj, $aa));
	}

	public function	nombreEnToutesLettres($nombre)
	{

		# Definition des tableaux des mots ...
		$nomGroupe = array('', 'mille', 'million', 'milliard');

		# Traitement de base sur le Nombre
		$tempNombre = str_replace(',', '.', $nombre);
		settype ($tempNombre, 'double');

		# limite ....
		if ($tempNombre > 999999999999.99)
		{
			$tempNombre = 0;
		}

		# On va formater le nombre de facon a le traite facilement
		$tempNombre = number_format($tempNombre, 2, '.', ',');

		# On sépare les decimals et les differents groupe (mille, million, milliard)
		list($entier, $decimal) = explode('.', $tempNombre);
		$lstGroupe = array_reverse(explode(',', $entier));

		# on traite l'entier ...
		if (!$entier)
		{
			$entier = 'zéro ';
		}
		else
		{
			$entier = '';
			# les milliard !
			if (isset($lstGroupe[3]) and $lstGroupe[3] > 0)
			{
				$entier .= $this->nombreEnToutesLettresInf1000($lstGroupe[3]). ' '. $nomGroupe[3]. (($nomGroupe[3]>1)?'s ':' ');
			}
			# les million !
			if (isset($lstGroupe[2]) and $lstGroupe[2] > 0)
			{
				$entier .= $this->nombreEnToutesLettresInf1000($lstGroupe[2]). ' '. $nomGroupe[2]. (($nomGroupe[2]>1)?'s ':' ');
			}
			# les mille (invariant) et on dit pas un mille
			if (isset($lstGroupe[1]) and $lstGroupe[1] > 0)
			{
				$entier .= (($lstGroupe[1]>1)?($this->nombreEnToutesLettresInf1000($lstGroupe[1]). ' '):''). $nomGroupe[1]. ' ';
			}
			# et les unite
			if ($lstGroupe[0] > 0)
			{
				$entier .= $this->nombreEnToutesLettresInf1000($lstGroupe[0]). ' ';
			}

			# cas du cent => le 'cent' se met au pluriel s'il n'est pas suivi d'autre chose.
			if ($lstGroupe[0] > 100 and substr($entier, -4) == 'cent')
			{
				$entier .= 's ';
			}
		}
		$entier .= "euro". (($nombre>=2)?"s":"");

		# Les decimals
		if ($decimal > 0)
		{
			return $entier. " et ". $this->nombreEnToutesLettresInf1000($decimal). " cent". (($decimal>1)?"s": "");
		}

		return $entier;

	}

	private function nombreEnToutesLettresInf1000($nombre)
	{
		$lstUnite = array
		(
			'',
			'un',
			'deux',
			'trois',
			'quatre',
			'cinq',
			'six',
			'sept',
			'huit',
			'neuf',
			'dix',
			'onze',
			'douze',
			'treize',
			'quatorze',
			'quinze',
			'seize',
			'dix-sept',
			'dix-huit',
			'dix-neuf'
		);
		$lstDizaine = array
		(
			'',
			'dix',
			'vingt',
			'trente',
			'quarante',
			'cinquante',
			'soixante',
			'',
			'quatre-vingt',
			''
		);

		# Recuperation des 3 chiffres
		$nombre = sprintf('%03d',$nombre);
		$unite    = substr($nombre, 2, 1);
		$dizaine  = substr($nombre, 1, 1);
		$centaine = substr($nombre, 0, 1);

		$nombreEnLettre='';

		# on commence par les centaines
		if ($centaine)
		{
			# on ne dit pas 'un cent' mais simpement 'cent'
			if ($centaine > 1)
			{
				$nombreEnLettre .= $lstUnite[$centaine]. ' ';
			}
			$nombreEnLettre .= 'cent ';
		}

		# Maintenant les dizaine
		# on gere des dizaines a partir de 20 ...
		if ($dizaine > 1)
		{
			# gestion differente pour 70 et 90
			if ($dizaine == 7 or $dizaine == 9)
			{
				$dizaine--;
				$unite += 10;
			}
			$nombreEnLettre .= $lstDizaine[$dizaine]. ' ';
		}
		else
		{
			$unite += 10*$dizaine;
		}

		# Le cas du et !
		# on dit ving et un, quarante et un, mais quatre-vingt onze
		if ($unite == 1 and $dizaine > 1 and $dizaine < 8)
		{
			$nombreEnLettre .= 'et ';
		}

		# Et maintenant les unite :)
		$nombreEnLettre .= $lstUnite[$unite];

		return trim($nombreEnLettre);

	}
}
?>
