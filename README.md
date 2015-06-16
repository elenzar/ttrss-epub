# ttrss-epub
epub generator extension for TinyTinyRSS

warning
--------------------
these files are work in progress; at the moment it runs a task periodically, but some problems about the zip creation result in creating a empty .epub file 

note on this plugin
--------------------
built on the basis of the import/export plugin

this plugin aims at generating daily a .epub file inside the plugin folder, so that it can be then moved to another folder where an e-ink tablet such as Kobo can fetch it. This is done in order to rest our eyes while reading our news feed.

license: GPLv3, following the TTRSS license

to do list:
-------------------- 
* ~~change the articles selctions in the query
* implement an automated .epub generation
	* ~~have a task running periodically
	* generate an .epub file with that task
		* get the right format inside the zip file (no parent folders)
		* fix the missing articles file

to do if wished by someone:
* create a proper setting board to make it easy to change the request and the update frequency
* allow to send it by mail

