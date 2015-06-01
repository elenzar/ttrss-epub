<?php
	//FONCTIONS GENERALES UTILES CI APRES
	function creationZip($dossier,$nomZip) {
	    $zip = new ZipArchive();
	    //changement de répertoire pour avoir l'arborescence qui va bien dans l'epub
	    chdir($dossier);
	    
	    if ($zip->open($nomZip, ZipArchive::CREATE)!==TRUE) {
		exit("cannot open <$nomZip>\n");
		fclose($test);
	    }

	    $files = glob('*', GLOB_MARK);
	    foreach ($files as $file) {
		
		if (!is_dir($file)) {
		    $zip->addFile($file);
		}
	    }
	    $zip->close();
	}

	//fonction tirée de http://stackoverflow.com/questions/3349753/delete-directory-with-files-in-it
	function deleteDir($parentDir,$dirName) {
	    chdir($parentDir);
	    if (! is_dir($dirName)) {
		throw new InvalidArgumentException("$dirName must be a directory");
	    }
	    if (substr($dirName, strlen($dirName) - 1, 1) != '/') {
		$dirName .= '/';
	    }
	    $files = glob($dirName . '*', GLOB_MARK);
	    foreach ($files as $file) {
		if (is_dir($file)) {
		    self::deleteDir($file);
		} else {
		    unlink($file);
		}
	    }
	    rmdir($dirName);
	}

class Epub extends Plugin implements IHandler {
	private $host;

	function init($host) {
		$this->host = $host;

		$host->add_hook($host::HOOK_UPDATE_TASK, $this);

		$host->add_hook($host::HOOK_PREFS_TAB, $this);
		//$host->add_command("xml-import", "import articles from XML", $this, ":", "FILE");
	}

	function about() {
		return array(1.0,
			"Exports all articles into an epub file",
			"lendar");
	}
        
        function csrf_ignore($method) {
		return false;
	}

	function before($method) {
		return true;


	function after() {
		return true;
	}


	function save() {
		$example_value = db_escape_string($_POST["example_value"]);

		echo "Value set to $example_value (not really)";

        
	function get_prefs_js() {
		return file_get_contents(dirname(__FILE__) . "/epub.js");
	}
        
	function hook_update_task() {
		
		$offset = (int) db_escape_string($_REQUEST['offset']);
		$limit = 250;

		$requete = "SELECT
				ttrss_entries.title,
				content,
				link,
				ttrss_feeds.title AS feed_title
			FROM
				ttrss_user_entries LEFT JOIN ttrss_feeds ON (ttrss_feeds.id = feed_id),
				ttrss_entries
			WHERE
				(unread = true OR feed_id IS NULL) AND
				ref_id = ttrss_entries.id AND
				ttrss_user_entries.owner_uid = " . $_SESSION['uid'] . "
			ORDER BY ttrss_entries.id DESC LIMIT $limit OFFSET $offset");
	
		$exportname = sha1($_SESSION['uid'] . $_SESSION['login']);

		$dossierRacine = dirname(__FILE__).'/'.$exportname; 

		generateEpub($requete,$dossierRacine);
	 }

        //FONCTION PRINCIPALE
	function generateEpub($requete,$dossierRacine) {
                
		$exported = 0;
                
                $cheminAbsolu = $dossierRacine;
                $nomZip = $cheminAbsolu . '/output.epub';

                if (is_file($nomZip)) {unlink($nomZip);}
                
		chdir($dossierRacine);
                $dos = "epub" . date(DATE_ISO8601);
                $dossier = $dos . "/";
                mkdir($dossier);
                
		if ($offset < 10000 && is_writable($dossier)) {
			$result = db_query($requete);

                        $epubOpfHead = '
<?xml version="1.0"  encoding="UTF-8"?>
<package xmlns="http://www.idpf.org/2007/opf" version="2.0" unique-identifier="uuid_id">
<metadata xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:opf="http://www.idpf.org/2007/opf" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:calibre="http://calibre.kovidgoyal.net/2009/metadata" xmlns:dc="http://purl.org/dc/elements/1.1/">
<dc:creator opf:role="aut" opf:file-as="TinyTinyRSS">TinyTinyRSS</dc:creator>
<meta name="calibre:title_sort" content="flux RSS de TTRSS"/>
<dc:description>epub genere par TinyTinyRSS</dc:description>
<meta name="calibre:timestamp" content="2012-10-09T18:06:38+00:00"/>
<dc:title>epub contenant les articles des flux de l\'instance TTRSS</dc:title>
<meta name="cover" content="cover"/>
<dc:date>0101-01-01T00:00:00+00:00</dc:date>
<dc:contributor opf:role="bkp">calibre (0.9.1) [http://calibre-ebook.com]</dc:contributor>
<dc:identifier id="uuid_id" opf:scheme="uuid">0624c687-07ae-4461-b99b-0d739ae5aa54</dc:identifier>
<dc:language>fr</dc:language>
</metadata>
';
                        
			$fp = fopen($dossier ."content.opf", "w");
			fputs($fp, $epubOpfHead);
                        
                        //creation des chaines completant l'index
                        $manifest = '<manifest>
                                    ';
                        $spine = '<spine toc="ncx">
                                 ';

			if ($fp) {
                                $i = 1;
				while ($line = db_fetch_assoc($result)) {
                                        
                                    // bloc de création des fichiers .xml où se trouve le contenu + écriture dans l'index de l'epub
                                    // creation du fichier html contenant l'article
                                        $nomFichier = "article".$i.".html";
					$article = fopen($dossier . $nomFichier, "w");
					fputs($article, "<?xml version='1.0' encoding='utf-8'?><html xmlns='http://www.w3.org/1999/xhtml'>");
						."<html xmlns='http://www.w3.org/1999/xhtml'>");
                                        $contenu = str_replace(array("&nbsp;"), "", $line['content']);
                                        $nouvelleEntree = 
						'<head><title><a href="'.$line['link'].'"></a>'.$line['title'].'</title></head>'.
						'<body><h1>'.$line['title'].'</h1><h4>(flux: '.$line['feed_title'].')</h4>'. $contenu .'</body>';
                                        
                                        fputs($article, $nouvelleEntree);
					fclose($article);
                                    //ajout des lignes qui vont bien pour l'index
                                        $manifest = $manifest.'<item href="'.$nomFichier.'" id="id'.$i.'" media-type="application/xhtml+xml"/>';
                                        $spine = $spine.'<itemref idref="id'.$i.'"/>';
                                    //incrément du compteur
                                        $i = $i+1;
                                    
				}

				$exported = db_num_rows($result);
                                
                                //cloture des textes de spine et manifest
                                $manifest = $manifest.'</manifest>
							';
                                $spine = $spine.'</spine>
						';

				if ($exported < $limit && $exported > 0) {
					fputs($fp, "");
				}
                                fputs($fp, $manifest);
                                fputs($fp, $spine);
				fclose($fp);
			}

		}
                                
                creationZip($dossier, $nomZip);
                //suppression des fichiers temporaires
                deleteDir($cheminAbsolu,$dos.'/');
                //print json_encode(array("exported" => $exported));
               
	}
        
        //FONCTIONS D'INTERFACE
        
        function exportDataEpub() {

		print "<p style='text-align : center' id='export_status_message'>You need to prepare exported data first by clicking the button below.</p>";

		print "<div align='center'>";
		print "<button dojoType=\"dijit.form.Button\"
			onclick=\"dijit.byId('epExportDlg').prepare()\">".
			__('Prepare data')."</button>";

		print "<button dojoType=\"dijit.form.Button\"
			onclick=\"dijit.byId('epExportDlg').hide()\">".
			__('Close this window')."</button>";

		print "</div>";


	}
        
        function hook_prefs_tab($args) {
		if ($args != "prefFeeds") return;

		print "<div dojoType=\"dijit.layout.AccordionPane\" title=\"".__('ePub generator')."\">";

		//print_notice(__("You can export and import your Starred and Archived articles for safekeeping or when migrating between tt-rss instances of same version."));

		print "<p>";

		print "<button dojoType=\"dijit.form.Button\" onclick=\"return exportDataEpub()\">".
			__('generate')."</button> ";

		print "<hr>";

		print "</p>";

		print "</div>"; # pane
	}
        
        function api_version() {
		return 2;
	}

}
?>
