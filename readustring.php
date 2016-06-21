<?php 

  function extractStringFromLine($str, $startToken = '"', $endToken = '"') {
    $startIndex = strpos($str, $startToken) + strlen($startToken);
    $endIndex = strrpos($str, $endToken);
    return substr($str, $startIndex, $endIndex - $startIndex);
  }

  function luaStringToPhpString($luaStr) {
    $match = array();
    $res = preg_match_all("/\\\\[0-9]{3}/",$luaStr,$match);
    $search[] = "\\b"; $repl[] = "\x8"; // backslash
    $search[] = "\\v"; $repl[] = "\xB"; // vertical tab
    $search[] = "\\a"; $repl[] = "\x7"; // bell
    $search[] = "\\t"; $repl[] = "\t"; // tab
    $search[] = "\\r"; $repl[] = "\r"; 
    $search[] = "\\n"; $repl[] = "\n";
    $search[] = "\\f"; $repl[] = "\f";
    $search[] = "\\\\"; $repl[] = "\\";  
    $search[] = "\\\""; $repl[] = "\"";
    foreach($match[0] as $matched) {
      $code = substr($matched, 1, 3);
      $search[] = $matched;
      $repl[] = chr(intval($code));
    }
    return str_replace($search,$repl,$luaStr);
  }

/*
function _98v(str)
  local res = ""
  local dtable = "\024D}\005\tC)G\016;.\f&wL<i\021\027ht,7d_ Bb]Y\030Px~\nJm\004f5\002\017%V!j#0{9\\(qr@4vg\020uS\028e>XQ\019\025ZU\018HM\v\"Oy\014'n3/[+pz\022*:2I\b\rNF\006\026`kl1=s?\000W\aAR\0316oc\001\003\023\029^a-E8\015T$|K"
  for i = 1, #str do
    local b = str:byte(i)
    if b > 0 and b <= 127 then
      res = res .. string.char(dtable:byte(b))
    else
      res = res .. string.char(b)
    end
  end
  return res
end
*/

  function udecrypt($str,$dtable) {
    $res = "";
    for ($i = 0; $i < strlen($str); $i++) {
      $b = ord($str[$i]);
      if ($b > 0 && $b <= 127) {
        $res .= $dtable[$b-1];
      } else {
        $res .= $str[$i];
      }
    }
    return $res;
  }

  // Check number of arguments
  if (count($argv) < 2) {
    exit("Usage: php $argv[0] lua_source_file > lua_source_file_decrypted".PHP_EOL);
  }

  // Open file
  $filename = $argv[1];
  $fh = @fopen($filename,"rb");
  if ($fh === FALSE) {
    exit("Unable to open $filename !".PHP_EOL);
  }
  
  $functionStringCryptPattern = "/function (_[a-zA-Z0-9]{3,})\\(str/";
  $match = array();
  $dtable = "";
  $LOOK_FOR_DTABLE = false;
  $LOOK_FOR_FNAME = true;
  $uncryptStringFunctionName = "____";
  while (($line = fgets($fh)) !== false) {
    $match = array();
    if ($LOOK_FOR_FNAME && preg_match($functionStringCryptPattern, $line, $match)) { 
      $uncryptStringFunctionName = $match[1];
      $LOOK_FOR_DTABLE = true;
      $LOOK_FOR_FNAME = false;
    }
    if ($LOOK_FOR_DTABLE && strpos($line, "local dtable") !== FALSE) {
      $dtable = luaStringToPhpString(extractStringFromLine($line));
      $LOOK_FOR_DTABLE = false;
    }
    if (!$LOOK_FOR_FNAME && !$LOOK_FOR_DTABLE && strpos($line, $uncryptStringFunctionName) !== FALSE) {
      $results = array();
      // In the regular expression '?' is used for ungreedy match (in case of multiple usage of the uncryptStringFunction on the same line)
      // see http://www.perlhowto.com/match_the_shortest_possible_string
      preg_match_all('/'.$uncryptStringFunctionName.'\("(.*?)"\)/', $line, $results);
      foreach($results[1] as $item) {
        $chaineToDecrypt = luaStringToPhpString($item);
        $decryptedLine = udecrypt($chaineToDecrypt, $dtable);
        echo "-- ".$decryptedLine.PHP_EOL;
      }
    }
    echo $line;
  }

  fclose($fh);
  exit(0);

