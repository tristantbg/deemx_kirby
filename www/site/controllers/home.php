<?php

return function ($site, $page) {

  $pagination = param('page');
  if($pagination) {
    $nextPage = $pagination + 1;
  }
  $dataClass = new \Deemx\Home();
  $data = $dataClass->get();

  if(!$data) {
  	// $data = [];
  }

  return [
    'data' => $data,
    'nextPage' => $nextPage ?? null
  ];
};
