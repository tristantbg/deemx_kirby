<?php

return function ($site, $page) {
  echo $page->id();
  $dataClass = new \Deemx\Home();
  $data = $dataClass->get();

  $data = [];
  $data['items'] = [];

  return [
    'data' => $data
  ];
};
