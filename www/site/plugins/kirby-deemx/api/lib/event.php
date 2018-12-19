<?php

  namespace Deemx;

  class Event extends Api {
    public function get($handle, $params = []) {
      // $payload = $this->build($handle, $opts);
      // $data = $this->getData($payload);
      $data = $this->query("/items/".$handle, $params);
      return $data;
    }
  }
