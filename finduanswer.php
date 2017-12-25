<?php

  require('ucommons.php');

  // Call number of arguments
  if (count($argv) < 2) {
    echo "Usage: php $argv[0] hash_to_find [length_of_collision]".PHP_EOL;
    echo "(length_of_collision must be at least 4 characters length)".PHP_EOL;
    die;
  }

  if (count($argv) > 1) {
    $hashToFind = intval($argv[1]);
  }

  $len = 4; // default value
  if (count($argv) > 2) {
    $len = max(4, intval($argv[2]));
  }

  $print = true;
  $stopOnFirstFound = true;
  echo "First collision found =>\n";
  echo findHash($hashToFind, $len)->current()."\n";

  $stopOnFirstFound = false;
  echo "All collisions found for length $len =>\n";
  foreach (findHash($hashToFind, $len) as $hash) {
    echo $hash."\n";
  };
