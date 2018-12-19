<?php

return function ($site, $page) {
  $dataClass = new \Deemx\Event();
  $data = $dataClass->get($page->slug());

  return [
    'item' => $data
  ];
};
