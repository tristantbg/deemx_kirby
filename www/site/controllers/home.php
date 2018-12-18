<?php

return function ($site, $page) {

  $pagination = param('page');
  if($pagination) {
    $nextPage = $pagination + 1;
  }
  $dataClass = new \Deemx\Home();
  $data = $dataClass->get();

  $data = [];
  $data['items'] = [];

  return [
    'data' => $data,
    'nextPage' => $nextPage ?? null
  ];
};
