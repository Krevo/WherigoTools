# WherigoTools
Tool(s) for reading information from Geocaching Wherigo<sup>TM</sup> Cartridge (.gwc files)

Usage :
```
php wherigoreader.php cartridgeFilename.gwc
```

This tool is like "unzip" but for a .gwc file : a directory `cartridgeFilename_files/` containing lua byte-code, lua source code, media files (jpg, mp3, ...) and a text file with header informations will be created.

/!\ Wherigo is a registered trademark of Groundspeak Inc. The Wherigo design, platform, and associated intellectual property are also owned by Groundspeak Inc.
