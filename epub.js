/*function exportDataEpub() {
	try {

		var query = "backend.php?op=pluginhandler&plugin=epub&method=exportDataEpub";

		if (dijit.byId("epExportDlg"))
			dijit.byId("epExportDlg").destroyRecursive();

		var exported = 0;

		dialog = new dijit.Dialog({
			id: "epExportDlg",
			title: __("Export Data"),
			style: "width: 600px",
			prepare: function() {

				notify_progress("Loading, please wait...");

				new Ajax.Request("backend.php", {
					parameters: "op=pluginhandler&plugin=epub&method=exportrun&offset=" + exported,
					onComplete: function(transport) {
						try {
							/*var rv = JSON.parse(transport.responseText);

							if (rv && rv.exported != undefined) {
								if (rv.exported > 0) {

									exported += rv.exported;

									$("export_status_message").innerHTML =
										"<img src='images/indicator_tiny.gif'> " +
										"Exported %d articles, please wait...".replace("%d",
											exported);

									setTimeout('dijit.byId("epExportDlg").prepare()', 2000);

								} else {

									$("export_status_message").innerHTML =
										ngettext("Finished, exported %d article. You can download the data <a class='visibleLink' href='%u'>here</a>.", "Finished, exported %d articles. You can download the data <a class='visibleLink' href='%u'>here</a>.", exported)
										.replace("%d", exported)
										.replace("%u", "backend.php?op=pluginhandler&plugin=epub&subop=exportget");

									exported = 0;

								}

							} else {
								$("export_status_message").innerHTML =
									"Error occured, could not export data.";
							}
						} catch (e) {
							exception_error("exportDataEpub", e, transport.responseText);
						}

						notify('');

					} });

			},
			execute: function() {
				if (this.validate()) {



				}
			},
			href: query});

		dialog.show();


	} catch (e) {
		exception_error("exportDataEpub", e);
	}
}*/

function exportDataEpub() {

				new Ajax.Request("backend.php", {
					parameters: "op=pluginhandler&plugin=epub&method=exportrun&offset=0",
					onComplete: function(transport) {
						try {
                                                    notify('succes!');
						} catch (e) {
							exception_error("exportDataEpub", e, transport.responseText);
						}

					} });

}

