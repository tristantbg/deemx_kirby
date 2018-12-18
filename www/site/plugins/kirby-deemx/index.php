<?php

if (!class_exists('Deemx\Api')) {
    require_once __DIR__ . '/api/api.php';
}

Kirby::plugin('deemx/frontend', [

  'routes' => [
    // [
    //   'pattern' => '/',
    //   'action'  => function () {
    //     return new Page([
    //       'slug' => $handle,
    //       'template' => 'home',
    //       'content' => [
    //         'title' => 'Home',
    //       ]
    //     ]);
    //   }
    // ],
    [
      'pattern' => 'event/(:any)',
      'action'  => function ($handle) {
        return new Page([
          'slug' => $handle,
          'template' => 'event',
          'content' => [
            'title' => 'Event',
          ]
        ]);
      }
    ]
  ]

]);
