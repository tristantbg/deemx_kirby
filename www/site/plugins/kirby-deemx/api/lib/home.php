<?php

  namespace Deemx;

  class Home extends Api {
    public function get($params = []) {
      $data = $this->query("/types/event", $params);
      return $data;
    }
  }
