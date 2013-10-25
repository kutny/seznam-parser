<?php

namespace Collabim\Curl\CurlDownloader;

class Response {

	private $body;
	private $status;
	private $headers = array();

	public function __construct($body, $status, array $headers) {
		$this->body = $body;
		$this->status = $status;
		$this->headers = $headers;
	}

	public function getBody() {
		return $this->body;
	}

	public function getHeaders() {
		return $this->headers;
	}

	public function getStatus() {
		return $this->status;
	}

}
