<?php

require 'library/Collabim/Curl/CurlDownloader/BodyDecoder.php';
require 'library/Collabim/Curl/CurlDownloader/Config.php';
require 'library/Collabim/Curl/CurlDownloader/EmptyResponseException.php';
require 'library/Collabim/Curl/CurlDownloader/IPostData.php';
require 'library/Collabim/Curl/CurlDownloader/RawPostData.php';
require 'library/Collabim/Curl/CurlDownloader/Response.php';
require 'library/Collabim/Curl/CurlDownloader/ResponseParser.php';
require 'library/Collabim/Curl/CurlDownloader/ResponsesContainer.php';
require 'library/Collabim/Curl/CurlDownloader.php';
require 'library/Collabim/FastRpc/BinaryDataEnvelope.php';
require 'library/Collabim/FastRpc/DataType.php';
require 'library/Collabim/FastRpc/IRequest.php';
require 'library/Collabim/FastRpc/RequestEncoder.php';
require 'library/Collabim/FastRpc/ResponseDecoder.php';
require 'library/Collabim/FastRpc/SearchRequest.php';
require 'library/Collabim/Seznam/SerpParser.php';
require 'library/Collabim/String/Utf8StringHandler.php';

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