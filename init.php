<?php

class Epub extends Plugin {
	private $host;
	private $cache_dir;	

	function init($host) {
		$this->host = $host;

		$this->cache_dir = CACHE_DIR . "/epubfiles/";

		if (!is_dir($this->cache_dir)) {
			mkdir($this->cache_dir);}
		
		if (is_dir($this->cache_dir)) {

			if (!is_writable($this->cache_dir)) {
				chmod($this->cache_dir, 0777);}

			if (is_writable($this->cache_dir)) {
				$host->add_hook($host::HOOK_UPDATE_TASK, $this);
                                // $host->add_hook($host::HOOK_HOUSE_KEEPING, $this);
			} else {
				user_error("The epub files directory is not writable.", E_USER_WARNING);
			}

		} else {
			user_error("Unable to create EPUB cache directory.", E_USER_WARNING);
		}


	}

	function about() {
		return array(1.0,
			"Exports periodically all articles into an epub file",
			"lendar",
			true);
	}
        
/*	function csrf_ignore($method) {
		return false;
	}

	function before($method) {
		return true;
	}

	function after() {
		return true;
	}
*/
        
	function hook_update_task() {
		
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
                        $pathContent = $dossier ."content.opf"; 
			$fp = fopen($pathContent, "w");
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
					$articlePath = $dossier . $nomFichier;
					$article = fopen($articlePath, "w");
					fputs($article, "<?xml version='1.0' encoding='utf-8'?><html xmlns='http://www.w3.org/1999/xhtml'>");
                                        $contenu = str_replace(array("&nbsp;"), "", $line['content']);
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
