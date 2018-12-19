<?php

  namespace Deemx;

  class Event extends Api {
    public function get($handle, $opts = null) {
      // $payload = $this->build($handle, $opts);
      // $data = $this->getData($payload);
      $data = $this->query("/items/".$handle, $opts);
      return $data;
    }
  }
