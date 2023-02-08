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

  /*
    This function will yield collisions for the desired hash
  */
  function findHash($hashToFind, $len = 4)
  {
    $max = pow(26, $len);
    $s = str_pad('',$len,"a",STR_PAD_LEFT);
    for ($i=0; $i<$max; $i++, $s++) {
      if (RSHash($s)==$hashToFind) {
        yield $s;
      }
    }
  }
