<?php

  namespace Deemx;

  use Kirby\Http\Remote;

  class Api {
    protected function query($url, $params = [], $payload = null) {

		$response = \Kirby\Http\Remote::get("https://ccs-backend.ngx.host/data/".$url."?".http_build_query($params), [
			'headers' => [
				'Content-Type' => 'application/json'
			],
			'method' => 'POST',
		]);

		return json_decode($response->content());

    }
  }
