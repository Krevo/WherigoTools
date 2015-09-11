<?php

if (count($argv)<2) {
  echo "Usage: php $argv[0] wherigoCartridgeFilename".PHP_EOL;
  die;
}

$filename = $argv[1];
$pathInfo = pathinfo($filename);
$basename = $pathInfo['filename'];

$fp = @fopen($filename,"rb");
if ($fp === FALSE) {
	echo "Unable to open $filename !".PHP_EOL;
	die;
}

$signature = fread($fp, 7);
$nbOfObjects = fread($fp, 2);

echo bin2hex($signature)."\n";
echo bin2hex($nbOfObjects)."\n";

$nb = hexdec(bin2hex($nbOfObjects[1]).bin2hex($nbOfObjects[0]));
echo "$nb objets \n";

$adrTab = array(); // tab containing adress of each object

for ($i = 0; $i < $nb; $i++) {
  $objectId = fread($fp, 2);
  $address = fread($fp, 4);
  $idDec = hexdec(bin2hex($objectId[1]).bin2hex($objectId[0]));
  $adrDec = hexdec(bin2hex($address[3]).bin2hex($address[2]).bin2hex($address[1]).bin2hex($address[0]));
  $adrTab[$idDec] = $adrDec;
  echo "[Objet #$i - id = $idDec]\n";
  echo bin2hex($objectId)."\n";
  echo bin2hex($address)."\n";
}

fclose($fp);

$objectTypeExt = array(
 0 => "luac",
 1 => "bmp",
 2 => "png",
 3 => "jpg",
 4 => "gif",
 17 => "wav",
 18 => "mp3",
 19 => "fdl",
 20 => "snd",
 21 => "ogg",
 33 => "swf",
 49 => "txt",
);

// Re-open
$handle = fopen($filename, "r");
$contents = fread($handle, filesize($filename));
fclose($handle);

@mkdir($basename."_files");
foreach($adrTab as $k => $address) {
	//printf("%x \n",$address);
	$offset = 0;
    $objectType = 0;
	if ($k>0) {
		$offset = 5;
        $valid = hexdec(bin2hex(substr($contents,$address,1)));
        $objectTypeBinary = substr($contents,$address+1,4);
		$objectType = hexdec(bin2hex($objectTypeBinary[3]).bin2hex($objectTypeBinary[2]).bin2hex($objectTypeBinary[1]).bin2hex($objectTypeBinary[0]));
	}
	$lengthBinary = substr($contents,$address+$offset,4);
	$lengthDec = hexdec(bin2hex($lengthBinary[3]).bin2hex($lengthBinary[2]).bin2hex($lengthBinary[1]).bin2hex($lengthBinary[0]));
	file_put_contents($basename."_files/file_$k.".$objectTypeExt[$objectType],substr($contents,$address+$offset+4,$lengthDec));
// Lua file (id #0) can be decompiled with 
// java -jar unluac_2015_06_13.jar file_0.lub > file_0.lua

}
