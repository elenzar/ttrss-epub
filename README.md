# ttrss-epub
epub generator extension for TinyTinyRSS

###state fo the work
The init.php file, in the right folder (./plugins/epub/) and enabled as a system plugin (i.e. through the config.php file) should create an .epub file containing all the unread articles, showing the most recent ones first, for every user, in the cache directory (./cache/epubfiles). 
####WIP
working on the interface in the prefs panel to allow the download fo the file from there

###note on this plugin
built on the basis of the import/export plugin

this plugin aims at generating daily a .epub file inside the plugin folder, so that it can be then moved to another folder where an e-ink tablet such as Kobo can fetch it. This is done in order to rest our eyes while reading our news feed.

license: GPLv3, following the TTRSS license

###to do list:
* ~~change the articles selections in the query~~
* ~~implement an automated .epub generation~~
	* ~~have a task running periodically~~
	* ~~generate an .epub file with that task~~
		* ~~get the right format inside the zip file (no parent folders)~~
		* ~~fix the missing articles file~~
* use housekeeping instead of update_task? or use a timer like feature to set the update frequency of the files?
	* ~~use housekeeping~~
	* implement timer
* allow download from prefs panel

to do if wished by someone:
* create a proper setting board to make it easy to change the request and the update frequency
* allow to send it by mail

