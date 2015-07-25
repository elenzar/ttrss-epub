# ttrss-epub
epub generator extension for TinyTinyRSS

###state fo the work
####warning: project no longer pursued
####most recent problems (as of 25/07/2015)
* some of the articles of the generated files are not displaying (blank page + unable to flip page) on both Kobo Touch and the Aldiko app (Android); likely to be isuees about the articles html formatting, but I could not figure out so far the issuing parts
* the code to download the files from the prefs panel is not finished and cause some issues to the whole ttrss instance, so it has been deactivated (commented) 

####quick description
The init.php file, in the right folder (./plugins/epub/) and enabled as a system plugin (i.e. through the config.php file) should create an .epub file containing all the unread articles, showing the most recent ones first, for every user, in the cache directory (./cache/epubfiles). 

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
* ~~add files to meet the epub file standards~~
* clean content from articles of its problematic characters

to do if wished by someone:
* create a proper setting board to make it easy to change the request and the update frequency
* allow to send it by mail

