<?php
//plugin written only to try to understand where calling the hooks fails, assuming that the simple test consisting in writing in a file is not the issuing point
//using this code as a base, tinkering with the include/rssfuncs.php and update.php allows to better understand the how it works and try to grab where the issue stands

	function testecrit($chaine,$dossier) {
	
				$nom = $dossier."epubTest.txt";
   
                 $fp = fopen($dossier, "w");
                  fputs($fp, $chaine."\n");
                  fclose($fp);
	}

class Essai extends Plugin {
	private $host;
	private $cache_dir;
	
	function about() {
		return array(1.0,
			"essaie de faire tourner une fonction periodiquement",
			"elenzar",
			true);
	}

	function init($host) {
		$this->host = $host;
		//print "initialisation du plugin 'essai' \n";
		$this->cache_dir = CACHE_DIR . "/essaiEpub/";

		if (!is_dir($this->cache_dir)) {
			mkdir($this->cache_dir);}
		
		if (is_dir($this->cache_dir)) {

			if (!is_writable($this->cache_dir)) {
				chmod($this->cache_dir, 0777);}

			if (is_writable($this->cache_dir)) {
				$host->add_hook($host::HOOK_UPDATE_TASK, $this);
                                 $host->add_hook($host::HOOK_HOUSE_KEEPING, $this);
				testEcrit("ecriture en date du ".date(DATE_ISO8601),$this->cache_dir."epubtest.txt");
				//$nb = count(PluginHost::getInstance()->get_hooks(7));
				//print "test sur les hooks apres addition des hooks de 'essai' : ".$nb." hooks de type 7 (update_task)";
			} else {
				user_error("Starred cache directory is not writable.", E_USER_WARNING);
				testEcrit("marqueur#2",$this->cache_dir."epubtest2.txt");	
	}

		} else {
			user_error("Unable to create EPUB cache directory.", E_USER_WARNING);
			testEcrit("marqeur#3",$this->cache_dir."epubtest3.txt");	
		}
	}

	function hook_house_keeping($arg){
		testecrit("je tente, il est ".date(DATE_ISO8601),$this->cache_dir."epubhook.txt");
	}


	function hook_update_task($arg) {
		testecrit("je tente, il est ".date(DATE_ISO8601),$this->cache_dir."epubhook.txt");
	}


	function api_version() {
		return 2;
	}
}
?>
