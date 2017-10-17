<?php

  /*
  function _Urwigo.Hash(str)
    local b = 378551
    local a = 63689
    local hash = 0
    for i = 1, #str do
    hash = hash * a + string.byte(str, i)
    hash = math.fmod(hash, 65535)
    a = a * b
    a = math.fmod(a, 65535)
    end
    return hash
  end

  */

 /*
  * Urwhigo.Hash() is in fact a variant of the Robert Sedgewick's Hash Algorithm
  */
  function RSHash($string) {
    $a = 63689;
    $b = 378551;
    $hash = 0;

    for ($i = 0, $x = strlen($string); $i < $x; $i++) {
      $hash = $hash * $a + (int) ord($string[$i]);
      $hash = fmod($hash, 65535);
      $a = $a * $b;
      $a = fmod($a, 65535);
    }

    return $hash;
  }

  // Call number of arguments
  if (count($argv)<3) {
    echo "Usage: php $argv[0] hash_to_find length_of_collision".PHP_EOL;
    echo "(length_of_collision should be at least 4 characters length)".PHP_EOL;
    die;
  }

  function convBase($numberInput, $fromBaseInput, $toBaseInput)
  {
    if ($fromBaseInput==$toBaseInput) return $numberInput;
    $fromBase = str_split($fromBaseInput,1);
    $toBase = str_split($toBaseInput,1);
    $number = str_split($numberInput,1);
    $fromLen=strlen($fromBaseInput);
    $toLen=strlen($toBaseInput);
    $numberLen=strlen($numberInput);
    $retval='';
    if ($toBaseInput == '0123456789')
    {
        $retval=0;
        for ($i = 1;$i <= $numberLen; $i++)
            $retval = bcadd($retval, bcmul(array_search($number[$i-1], $fromBase),bcpow($fromLen,$numberLen-$i)));
        return $retval;
    }
    if ($fromBaseInput != '0123456789')
        $base10=convBase($numberInput, $fromBaseInput, '0123456789');
    else
        $base10 = $numberInput;
    if ($base10<strlen($toBaseInput))
        return $toBase[$base10];
    while($base10 != '0')
    {
        $retval = $toBase[bcmod($base10,$toLen)].$retval;
        $base10 = bcdiv($base10,$toLen,0);
    }
    return $retval;
  }

  $hashToFind = intval($argv[1]);
  $len = max(4, intval($argv[2]));

  $max = pow(26,$len);
  echo "Collisions found =>\n";
  for ($i=0; $i<$max; $i++) {
    $s = str_pad(convBase($i, '0123456789', 'abcdefghijklmnopqrstuvwxyz'),$len,"a",STR_PAD_LEFT);
    if (RSHash($s)==$hashToFind) echo $s."\n";
  }


