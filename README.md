# WherigoTools
Tools for reading informations from Geocaching Wherigo<sup>TM</sup> Cartridge (.gwc files)

wherigoreader
---

This tool is like "unzip" but for a .gwc file : a directory `cartridgeFilename_files/` containing lua byte-code, lua source code, media files (jpg, mp3, ...) and a text file with header informations will be created.

Usage :
```
php wherigoreader.php cartridgeFilename.gwc
```

finduanswer
---

This tool help you find an answer that will be accepted by a wherigo created with Urwhigo. Instead of testing your answer directly, the lua code test a hash of the answer. So, if you find another answer with the same hash, .. the answer is considered correct ! Considering that the hash is only 16 bits long, it's quick and easy to find collisions. You will find the ```hash_to_find``` in the lua source code.

Usage :
```
php finduanswer.php hash_to_find [length_of_collision]
```
(length_of_collision must be at least 4 characters length)
    
readustring
---

When using Urwhigo, all strings in the generated lua source code are obfuscated. This tool will read a lua source code and output the same source code with additionnal comment line giving you the readable version of each string.

Usage :
```
php readustring.php lua_source_code > lua_source_code_with_decrypted_string
```



:warning: Wherigo is a registered trademark of Groundspeak Inc. The Wherigo design, platform, and associated intellectual property are also owned by Groundspeak Inc.
