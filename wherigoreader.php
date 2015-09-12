<?php

  const OFFSET_SIGNATURE = 0;
  const LENGTH_SIGNATURE = 7;
  const OFFSET_NB_OBJECTS = 7;
  const LENGTH_NB_OBJECTS = 2;
  const OFFSET_FILE_HEADER = 9;
  const LENGTH_OBJECT_ID = 2;
  const LENGTH_OBJECT_ADR = 4;
  
  const WHERIGO_SIGNATURE = "\x02\x0aCART\x00";

  // Call number of arguments
  if (count($argv)<2) {
    echo "Usage: php $argv[0] wherigoCartridgeFilename".PHP_EOL;
    die;
  }

  // Read settings, if no local settings copy settings sample file
  $settingsSampleFilename = "settings.ini.sample";
  $settingsFilename = "settings.ini";

  $hasSettings = file_exists($settingsFilename);
  if (!$hasSettings) {
    echo "No settings.ini file found !".PHP_EOL;
    $hasSettingsSample = file_exists($settingsSampleFilename);
    if ($hasSettingsSample) {
      echo "Copying $settingsSampleFilename to $settingsFilename".PHP_EOL;
      copy($settingsSampleFilename,$settingsFilename);
      $hasSettings = file_exists($settingsFilename);
    }
  }

  // Open cartridge file
  $filename = $argv[1];
  $pathInfo = pathinfo($filename);
  $basename = $pathInfo['filename'];
  $extension = $pathInfo['extension'];

  $fh = @fopen($filename,"rb");
  if ($fh === FALSE) {
    echo "Unable to open $filename !".PHP_EOL;
    die;
  }
  $contents = fread($fh, filesize($filename));
  fclose($fh);

  $signature   = substr($contents, OFFSET_SIGNATURE, LENGTH_SIGNATURE);
  $nbOfObjects = substr($contents, OFFSET_NB_OBJECTS, LENGTH_NB_OBJECTS);

  // Check if the header of the file match the signature of a wherigo cartridge
  if (WHERIGO_SIGNATURE!==$signature) {
    echo "Incorrect header : the file $filename doesn't seem to be a wherigo cartridge.\n";
    die;
  }

  echo "Reading Wherigo cartridge $basename.$extension\n";
  $nb = hexdec(bin2hex($nbOfObjects[1]).bin2hex($nbOfObjects[0]));
  $adrTab = array(); // tab containing adress of each object
  for ($i = 0; $i < $nb; $i++) {
    $objectId = substr($contents, OFFSET_FILE_HEADER + $i * (LENGTH_OBJECT_ID + LENGTH_OBJECT_ADR), LENGTH_OBJECT_ID);
    $address = substr($contents, OFFSET_FILE_HEADER + $i * (LENGTH_OBJECT_ID + LENGTH_OBJECT_ADR) + LENGTH_OBJECT_ID, LENGTH_OBJECT_ADR);
    $idDec = hexdec(bin2hex($objectId[1]).bin2hex($objectId[0]));
    $adrDec = hexdec(bin2hex($address[3]).bin2hex($address[2]).bin2hex($address[1]).bin2hex($address[0]));
    $adrTab[$idDec] = $adrDec;
  }

  // Creating destination dir
  @mkdir($basename."_files");

  // Extraction information header (Name of cartridge, ...)
  $INFORMATION_HEADER_OFFSET = OFFSET_FILE_HEADER + $nb * (LENGTH_OBJECT_ID + LENGTH_OBJECT_ADR);
  $LENGTH_INFORMATION_HEADER = 2;
  $headerLentghBinary = substr($contents, $INFORMATION_HEADER_OFFSET, $LENGTH_INFORMATION_HEADER);
  $headerLength = hexdec(bin2hex($headerLentghBinary[1]).bin2hex($headerLentghBinary[0]));
  file_put_contents($basename."_files/".$basename."_header.bin",substr($contents,$INFORMATION_HEADER_OFFSET + $LENGTH_INFORMATION_HEADER,$headerLength));
   
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
  $nbPerType = array();

  $i = 0;
  $msg = "";
  foreach($adrTab as $k => $address) {
    $i++;
    if ($i>1) {
      for ($j = 0; $j < strlen($msg); $j++) {
       echo "\010";
      }
    }
    $msg = sprintf("Processing object %d of %d (%01.0f %%)",$i,$nb,($i/$nb)*100);
    echo $msg;
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
    file_put_contents($basename."_files/".$basename."_$k.".$objectTypeExt[$objectType],substr($contents,$address+$offset+4,$lengthDec));
    if (!isset($nbPerType[$objectTypeExt[$objectType]])) {
      $nbPerType[$objectTypeExt[$objectType]] = 0;
    }
    $nbPerType[$objectTypeExt[$objectType]]++;
  }
  echo PHP_EOL;

  // Displaying number of files found per type
  asort($nbPerType);
  $msg = "Found ";
  $msgPart = array();
  foreach($nbPerType as $ext => $nb) {
    $msgPart[] = "$nb .$ext file".(($nb>1)?"s":"");
  }
  $msg .= implode($msgPart, ", ");
  echo $msg.PHP_EOL;
  
  // Trying to decompile the lua byte-code  
  if ($hasSettings) {
    $settings = parse_ini_file($settingsFilename,true);
    if (isset($settings["Lua decompiler"]["command"])) {
      echo "Trying to decompile the lua byte-code with command :".PHP_EOL;
      $execCommand = sprintf(
          $settings["Lua decompiler"]["command"], 
          $basename."_files/".$basename."_0.".$objectTypeExt[0],
          $basename."_files/".$basename."_0.lua"
          );
      echo "> $execCommand".PHP_EOL;
      $output = array();
      exec($execCommand,$output,$returnCode);
      if ($returnCode==0) {
        echo "Success !".PHP_EOL;
      }
    }
  }


