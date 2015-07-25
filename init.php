<?php

class Epub extends Plugin implements IHandler {
	private $host;
	private $cache_dir;	
	private $blacklist;
	
	function init($host) {
		$this->host = $host;

		$this->cache_dir = CACHE_DIR . "/epubfiles/";
		$this->blacklist = ["&nbsp;","class","style","alt"];

		if (!is_dir($this->cache_dir)) {
			mkdir($this->cache_dir);}
		
		if (is_dir($this->cache_dir)) {

			if (!is_writable($this->cache_dir)) {
				chmod($this->cache_dir, 0777);}

			if (is_writable($this->cache_dir)) {
                                $host->add_hook($host::HOOK_HOUSE_KEEPING, $this);
			} else {
				user_error("The epub files directory is not writable.", E_USER_WARNING);
			}
			//$host->add_hook($host::HOOK_PREFS_TAB, $this);

		} else {
			user_error("Unable to create EPUB cache directory.", E_USER_WARNING);
		}


	}

	function about() {
		return array(1.0,
			"Exports periodically all unread articles into an epub file",
			"lendar",
			true);
	}
	
	//functions csrf_ignore, before and after taken and adapted after the import_export plugin        
	function csrf_ignore($method) {
		return in_array($method, array("telechargement_fichier"));
	}

	function before($method) {
		return $_SESSION["uid"] != false;
	}

	function after() {
		return true;
	}
       
	//FONCTION D'INTERFACE
	function get_prefs_js() {
		return file_get_contents(dirname(__FILE__) . "/epub.js");
	}
 
        /*function hook_prefs_tab($args) {
		if ($args != "prefPrefs") return;

		print "<div dojoType=\"dijit.layout.AccordionPane\" title=\"".__('ePub file')."\">";
		print "<p>";

		$exportname = $this->cache_dir . '/output_user_'. $_SESSION['uid'] .'.epub';
	/*	print "<button dojoType=\"dijit.form.Button\" onclick=\"return exportEpub()\">".
			__('get latest generated ePub file')."</button> ";
	//	
		if (file_exists($exportname)) {
			//print "You can download your epub file by clicking the following link:</br><a href=backend.php?op=pluginhandler&plugin=epub&subop=telechargementfichier");
		} else {
			print "No epub file has been generated for you so far.";
		}
		
		print "<hr>";

		print "</p>";

		print "</div>"; # pane
	}*/

	function exportEpub() {

		print "<p style='text-align : center' id='export_epub_status_message'>Click the button below to get your file.</p>";

		print "<div align='center'>";
		print "<button dojoType=\"dijit.form.Button\"
			onclick=\"dijit\">".
			__('get epub file')."</button>";
		
	/*	print "<button dojoType=\"dijit.form.Button\"
			onclick=\"dijit.byId('epubExportDlg').prepare()\">".
			__('get epub file')."</button>";

	/*	print "<button dojoType=\"dijit.form.Button\"
			onclick=\"dijit.byId('dataExportDlg').hide()\">".
			__('Close this window')."</button>";
	*/
		print "</div>";
	

	}

	function telechargementfichier() {
	//same code as 'exportget' function in the import_export plugin, adapted to serve the epub file and with a different name to avoid conflict in case this plugin frontend ends on the same page as the import_export plugin frontend
		$exportname = $this->cache_dir . '/output_user_'. $_SESSION['uid'] .'.epub';

		if (file_exists($exportname)) {
			header("Content-type: application/epub+zip");
			header("Content-Disposition: attachment; filename=TinyTinyRSS_newspaper.epub");
			echo file_get_contents($exportname);
			echo $_SESSION['uid'];
			$f = fopen($this->cache_dir."testDL.txt");
			fputs($f,$_SESSION['uid']);
			fclose($f);
		} else {
			echo "File not found.";
		}
	}
	//PARTIE DE GENERATION DES FICHIERS EPUB
	function hook_house_keeping() {
		
		$limit = 250;

		$reqUtilisateurs = "SELECT id FROM ttrss_users";

		$dossierRacine = $this->cache_dir; 

		$utilisateurs = db_query($reqUtilisateurs);
		while ($ut = db_fetch_assoc($utilisateurs)) {
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
					ttrss_user_entries.owner_uid = " . $ut['id'] . "
				ORDER BY ttrss_entries.id DESC LIMIT $limit";
			$this->generateEpub($requete,$dossierRacine,$ut['id']);
		}
	 }

        //FONCTION PRINCIPALE
	function generateEpub($requete,$dossierRacine,$id) {
               //preparation de la variable zip 
                $nomZip = $dossierRacine . '/output_user_'.$id.'.epub';
                if (is_file($nomZip)) {unlink($nomZip);}
	        
		$zip = new ZipArchive();
	    
	        if ($zip->open($nomZip, ZipArchive::CREATE)!==TRUE) {
			exit("cannot open <$nomZip>\n");
	        }
 

                
                $dos = "epub_user_" . $id . '_' . date(DATE_ISO8601);
                $dossier = $dossierRacine . $dos . "/";
                mkdir($dossier);
               
		//coeur de la fonction: construction du zip et visite des articles a ajouter 
		if ($offset < 10000 && is_writable($dossier)) {
			$result = db_query($requete);

                        $epubOpfHead = '<?xml version="1.0"  encoding="UTF-8"?>
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
                        $pathContent = $dossier ."content.opf"; 
			$fp = fopen($pathContent, "w");
			fputs($fp, $epubOpfHead);
                        
                        //creation des chaines completant l'index
                        $manifest = '<manifest>
<item href="titlepage.html" id="titlepage" media-type="application/xhtml+xml"/>
                                    ';
                        $spine = '<spine toc="ncx">
<itemref idref="titlepage"/>
                                 ';

			if ($fp) {
                                $i = 1;
				while ($line = db_fetch_assoc($result)) {
                                        
                                    // bloc de création des fichiers .xml où se trouve le contenu + écriture dans l'index de l'epub
                                    // creation du fichier html contenant l'article
                                        $nomFichier = "article".$i.".html";
					$articlePath = $dossier . $nomFichier;
					$article = fopen($articlePath, "w");
					fputs($article, "<?xml version='1.0' encoding='utf-8'?><html xmlns='http://www.w3.org/1999/xhtml'>");
                                        $contenu = utf8_encode($line['content']);//str_replace($this->blacklist, "", $line['content']);
                                        $nouvelleEntree = 
						'<head><title><a href="'.$line['link'].'"></a>'.$line['title'].'</title></head>'.
						'<body><h1>'.$line['title'].'</h1><h4>(flux: '.$line['feed_title'].')</h4>'. $contenu .'</body>';
                                        
                                        fputs($article, $nouvelleEntree);
					fclose($article);
                                    //ajout des lignes qui vont bien pour l'index
                                        $manifest = $manifest.'<item href="'.$nomFichier.'" id="id'.$i.'" media-type="application/xhtml+xml"/>';
                                        $spine = $spine.'<itemref idref="id'.$i.'"/>';
					//insertion de l'article dans le zip; le $nomFichier permet d'avoir l'arborescence qui va bien dans le zip (tout a la racine)
					$zip->addFile($articlePath,$nomFichier);
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
				$zip->addFile($pathContent,"content.opf");
				
				//addition of some files to make a proper epub
				
				//mimetype
				$mimetype_file_path = $dossier . "mimetype"; 
				$mimetype_file = fopen($mimetype_file_path,"w");
				fputs($mimetype_file,"application/epub+zip");
				fclose($mimetype_file);
				$zip->addFile($mimetype_file_path,"mimetype");
				
				//container
				$container_content = 
'<?xml version="1.0" encoding="UTF-8"?>

<container version="1.0" xmlns="urn:oasis:names:tc:opendocument:xmlns:container">

   <rootfiles>

      <rootfile full-path="content.opf" media-type="application/oebps-package+xml"/>

   </rootfiles>

</container>';
				$container_path = $dossier . "container.xml"; 
				$container_file = fopen($container_path,"w");
				fputs($container_file,$container_content);
				fclose($container_file);
				$zip->addFile($container_path,"META-INF/container.xml");
				
				//titlepage
				$titlepage_content = '<?xml version="1.0" encoding="utf-8"?><html xmlns="http://www.w3.org/1999/xhtml">
<head><title>epub title</title></head>
<body><h1>unread TinyTinyRSS articles</h1></body>';
				$titlepage_file_path = $dossier . "mimetype"; 
				$titlepage_file = fopen($mimetype_file_path,"w");
				fputs($titlepage_file,$titlepage_content);
				fclose($titlepage_file);
				$zip->addFile($titlepage_file_path,"mimetype");

			}

		}
                

		$zip->close();
                
                //suppression des fichiers temporaires
                $this->deleteDir($dossierRacine.$dos.'/');
	}
        
        function api_version() {
		return 2;
	}


	//FONCTIONS GENERALES UTILES CI APRES
	//fonction tirée de http://stackoverflow.com/questions/3349753/delete-directory-with-files-in-it
	private function deleteDir($dirName) {
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

}
?>
