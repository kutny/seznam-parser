<?php

namespace Collabim\Seznam;

use Collabim\Curl\CurlDownloader;
use Collabim\Curl\CurlDownloader\RawPostData;
use Collabim\Curl\CurlDownloader\Config;
use Collabim\FastRpc\RequestEncoder;
use Collabim\FastRpc\ResponseDecoder;
use Collabim\FastRpc\SearchRequest;

class SerpParser {

	private $searchRpcUri;
	private $cookieJarPath;
	private $httpClient;
	private $fastRpcRequestEncoder;
	private $fastRpcResponseDecoder;

	public function __construct(
		$searchRpcUri,
		$cookieJarPath,
		CurlDownloader $httpClient,
		RequestEncoder $fastRpcRequestEncoder,
		ResponseDecoder $fastRpcResponseDecoder
	) {
		$this->searchRpcUri = $searchRpcUri;
		$this->cookieJarPath = $cookieJarPath;
		$this->httpClient = $httpClient;
		$this->fastRpcRequestEncoder = $fastRpcRequestEncoder;
		$this->fastRpcResponseDecoder = $fastRpcResponseDecoder;
	}

	public function getResults($query, $from, $forceNewSeznamLayout = true) {
		if ($forceNewSeznamLayout) {
			$this->forceNewSeznamLayout();
		}

		$searchRequest = new SearchRequest(SearchRequest::TYPE_ALL, $query, $from);

		$payload = base64_encode($this->fastRpcRequestEncoder->encode($searchRequest));

		$postData = new RawPostData($payload);

		$downloaderConfig = new Config($this->searchRpcUri);
		$downloaderConfig->setCookiesStorageFile($this->cookieJarPath);
		$downloaderConfig->setPostData($postData);
		$downloaderConfig->setConnectionTimeout(30);
		$downloaderConfig->setUserAgent('Mozilla/5.0 (Windows NT 6.1; rv:24.0) Gecko/20100101 Firefox/24.0');
		$downloaderConfig->setHeaders(array(
			'Connection' => 'close',
			'Accept' => 'application/x-base64-frpc',
			'Accept-Encoding' => 'gzip, deflate',
			'Accept-Language' => 'cs,en-us;q=0.7,en;q=0.3',
			'Accept-Charset' => 'utf-8,ISO-8859-1;q=0.7,*;q=0.7',
			'Content-Type' => 'application/x-base64-frpc; charset=UTF-8'
		));

		$response = $this->httpClient->downloadPage($downloaderConfig)->getLastResponse();

		$base64Data = $response->getBody();

		return $this->fastRpcResponseDecoder->decode(base64_decode($base64Data));
	}

	private function forceNewSeznamLayout() {
		$getCookieConfig = new Config('http://search.seznam.cz/switch?layout=matrix');
		$getCookieConfig->setCookiesStorageFile($this->cookieJarPath);
		$getCookieConfig->setConnectionTimeout(30);
		$getCookieConfig->setUserAgent('Mozilla/5.0 (Windows NT 6.1; rv:24.0) Gecko/20100101 Firefox/24.0');

		$this->httpClient->downloadPage($getCookieConfig);
	}

}
