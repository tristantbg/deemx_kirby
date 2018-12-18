<?php

return function ($site, $page) {
  echo $page->id();
  $dataClass = new \Deemx\Event();
  // $data = $dataClass->get($page->slug());

  $data = [];
  $data['items'] = [];

  return [
    'data' => $data
  ];
};
