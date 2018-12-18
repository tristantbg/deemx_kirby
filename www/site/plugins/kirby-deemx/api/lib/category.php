<?php
  namespace Deemx;

  class Category extends Api {
    public function get($handle, $opts = null) {
      $payload = $this->build($handle, $opts);
      $data = $this->getData($payload);
      return $data;
    }
  }
