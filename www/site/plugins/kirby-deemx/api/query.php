<?php

  namespace Deemx;

  use Kirby\Http\Remote;

  class Api {
    protected function query($url, $payload) {
      // $ch = curl_init();

      // // curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      // //   'Content-Type: application/graphql',
      // //   'X-Shopify-Storefront-Access-Token: '.$token
      // // ));

      // curl_setopt($ch, CURLOPT_URL, "https://ccs-backend.ngx.host/data/".$url);
      // // curl_setopt($ch, CURLOPT_URL, "http://localhost:5000/data/".$url);

      // curl_setopt($ch, CURLOPT_POST, true);
      // curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
      // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
      // curl_setopt($ch, CURLOPT_VERBOSE, true );
      // curl_setopt($ch, CURLOPT_ENCODING, '' );
      // $output = curl_exec($ch);
      // curl_close($ch);

      // return json_decode($request);


    	$response = \Kirby\Http\Remote::get("https://ccs-backend.ngx.host/data/".$url, [
			'headers' => [
				// 'Authorization' => 'Basic ' . base64_encode($email . ':' . $password)
			],
			'method' => 'POST'
		]);

		$page = json_decode($response->content());
    }
  }
