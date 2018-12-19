<?php

return function ($site, $page) {

  $currentPage = param('page') ? param('page') : 0;
  $nextPage = $currentPage ? $currentPage + 1 : null;

  $dataClass = new \Deemx\Home();
  $data = $dataClass->get(['page' => $nextPage, 'per_page' => 20]);

  return [
    'data' => $data,
    'nextPage' => $nextPage
  ];
};
