# ttrss-epub
epub generator extension for TinyTinyRSS

WARNING
=====================
these files are work in progress; the files in this folder are an early test about generating .epub files from the TTRSS database; the "testing_hooks" folder contains what is used at the moment (15/06/2015) as a plugin to test if the UPDATE_TASK and HOUSEKEEPING hooks work on my install (these hooks being the ones needed to continue this project)

note on the early code
=====================
built on the basis of the import/export plugin

this plugin aims at generating daily a .epub file inside the plugin folder, so that it can be then moved to another folder where an e-ink tablet such as Kobo can fetch it. This is done in order to rest our eyes while reading our news feed.

at the moment the generation of an .epub file is done manually from the preferences board and erase the previous .epub file.

to do list: 
* change the articles selctions in the query
* implement an automated .epub generation

to do if wished by someone:
* create a proper setting board to make it easy to change the request and the update frequency

license: GPLv3, following the TTRSS license
