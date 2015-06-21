function exportEpub() {
	try {
		new Ajax.Request("backend.php", {
			parameters: "op=pluginhandler&plugin=epub&subop=telechargementfichier",
			});

	} catch (e) {
		exception_error("exportEpub", e);
	}
}



