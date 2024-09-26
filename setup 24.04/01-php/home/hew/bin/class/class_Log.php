<?php
//------------------------------------------------------------------//
//                      Log                                         //
//------------------------------------------------------------------//
// ver : 1.1
// dat : 12-05-2009
// aut : sbesson
// but : Gestion de log
//
// 02-06-2009 : 1.1 : Modification de la methode setDebug (passage d'un boolean),
//                    ajout du utf8_decode pour le corps du mail
//

class Log
{
	//------------------------------------------------------------------//
	//               Les attributs de l'objet                           //
	//------------------------------------------------------------------//
	private $LogVersion = '1.1';

	// Les attributs
	# email de notification
	private $debug=false;
	private $emailFrom = '';
	private $emailTo = '';
	private $emailBody = '';
	private $emailSubject = '';
	private $emailAEnvoyer = false;

	//------------------------------------------------------------------//
	//               Le constructeur de l'objet                         //
	//------------------------------------------------------------------//
	# constructeur de l'objet
	# On donne le nom du programme
	public function __construct($namePgm, $debug=false)
	{
		$this->debug = ($debug===true);
		openlog($namePgm, LOG_PID, LOG_USER);
		$this->emailSubject = 'Rapport d\'execution de '. $namePgm;
	}

	# le destructeur de l'object
	public function __destruct()
	{
		if ($this->emailTo != '' and $this->emailBody != '' and $this->emailAEnvoyer)
		{
			mail($this->emailTo, $this->emailSubject, utf8_decode($this->emailBody), 'From: '. $this->emailFrom. "\r\n", '-f'. $this->emailFrom);
		}
		closelog();
	}

	//------------------------------------------------------------------//
	//               Les methodes de l'objet                            //
	//------------------------------------------------------------------//
	# METHODE : setEmail($email)
	# mets à jour l'email de notification.
	public function setEmails($emailFrom, $emailTo)
	{
		$this->emailFrom = $emailFrom;
		$this->emailTo = $emailTo;
	}

	# METHODE : setDebug($debug)
	# mets à jour l'email de notification.
	public function setDebug($debug)
	{
		$this->debug = ($debug===true);
	}

	# METHODE : debug($message)
	# log un message de debug.
	public function debug($message)
	{
		if ($this->debug)
		{
			$this->emailAEnvoyer = true;
			$this->emailBody .= '** DEBUG ** '. $message. "\n";
			syslog(LOG_DEBUG, '** DEBUG ** '. $message);
		}
	}

	# METHODE : info($message)
	# log un message d'information.
	public function info($message)
	{
		$this->emailBody .= '** INFO ** '. $message. "\n";
		syslog(LOG_INFO, '** INFO ** '. $message);
	}

	# METHODE : warning($message)
	# log un message d'alerte.
	public function warning($message)
	{
		$this->emailAEnvoyer = true;
		$this->emailBody .= '** WARNING ** '. $message. "\n";
		syslog(LOG_WARNING, '** WARNING ** '. $message);
	}

	# METHODE : error($message)
	# log un message d'erreur.
	public function error($message)
	{
		$this->emailAEnvoyer = true;
		$this->emailBody .= '** ERROR ** '. $message. "\n";
		syslog(LOG_ERR, '** ERROR ** '. $message);
	}

}
