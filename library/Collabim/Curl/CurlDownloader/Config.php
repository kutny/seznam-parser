<?php

namespace Collabim\Curl\CurlDownloader;

class Config {

	private $url;
	private $userAgent;
	private $connectionTimeout;
	private $cookiesStorageFile;
	private $maxRedirects;
	private $postData;
	private $headers = array();
	private $logFilePath;

	public function __construct($url) {
		$this->url = $url;
		$this->useCookies = false;
	}

	public function getUrl() {
		return $this->url;
	}

	public function setUserAgent($userAgent) {
		$this->userAgent = $userAgent;
	}

	public function getUserAgent() {
		return $this->userAgent;
	}

	public function setConnectionTimeout($connectionTimeout) {
		$this->connectionTimeout = $connectionTimeout;
	}

	public function getConnectionTimeout() {
		return $this->connectionTimeout;
	}

	public function setCookiesStorageFile($cookiesStorageFile) {
		$this->cookiesStorageFile = $cookiesStorageFile;
	}

	public function getCookiesStorageFile() {
		return $this->cookiesStorageFile;
	}

	public function setMaxRedirects($maxRedirects) {
		$this->maxRedirects = $maxRedirects;
	}

	public function getMaxRedirects() {
		return $this->maxRedirects;
	}

	public function setPostData(IPostData $postData) {
		$this->postData = $postData;
	}

	/** @return IPostData */
	public function getPostData() {
		return $this->postData;
	}

	public function setHeader($name, $value, $overwrite = false) {
		if (array_key_exists($name, $this->headers) && $overwrite === false) {
			throw new \Exception('Header "' . $name . '" already defined');
		}

		$this->headers[$name] = $value;
	}

	public function setHeaders(array $headers, $overwrite = false) {
		foreach ($headers as $name => $value) {
			$this->setHeader($name, $value, $overwrite);
		}
	}

	public function getHeaders() {
		return $this->headers;
	}

	public function hasHeaders() {
		return count($this->headers) > 0;
	}

	public function setLogFilePath($logFilePath) {
		$this->logFilePath = $logFilePath;
	}

	public function getLogFilePath() {
		return $this->logFilePath;
	}

}
