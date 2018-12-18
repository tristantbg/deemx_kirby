<?php
  // CURL QUERIER
  require(__DIR__ . '/query.php');

  // LIBRARY
  $files = glob(__DIR__ . '/lib/*.php');
  foreach ($files as $file) {
    if ($file != __FILE__) {
      require($file);
    }
  }