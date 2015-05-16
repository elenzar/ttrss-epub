function exportDataEpub() {
//requete visant uniquement à générer l'epub; on ne récupère pas le résultat car inutile pour nous
				new Ajax.Request("backend.php", {
					parameters: "op=pluginhandler&plugin=epub&method=exportrun&offset=0"
                                    });

}
