<?php

  namespace Deemx;

  class Home extends Api {
    public function get($opts = null) {
      $data = $this->query("/types/event", $opts);
      return $data;
    }
  }
