<?php

function __autoload($className) {
	$libraryFilename = 'library/' . str_replace('\\', '/', $className) . '.php';

	if (is_readable($libraryFilename)) {
		require $libraryFilename;
	}
}

function getSerpParser($searchRpcUrl) {
	return new \Collabim\Seznam\SerpParser(
		$searchRpcUrl,
		new \Collabim\Curl\CurlDownloader(
			new \Collabim\Curl\CurlDownloader\ResponseParser(
				new \Collabim\Curl\CurlDownloader\BodyDecoder()
			)
		),
		new \Collabim\FastRpc\RequestEncoder(
			new \Collabim\String\Utf8StringHandler()
		),
		new \Collabim\FastRpc\ResponseDecoder()
	);
}