<?php

  namespace Deemx;

  use Kirby\Http\Remote;

  class Api {
    protected function query($url, $payload) {

		$response = \Kirby\Http\Remote::get("https://ccs-backend.ngx.host/data/".$url, [
			'headers' => [
				'Content-Type' => 'application/json'
			],
			'method' => 'POST'
		]);

		return json_decode($response->content());

    }
  }
