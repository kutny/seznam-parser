<?php

namespace Collabim\Curl\CurlDownloader;

class ResponsesContainer {

	private $responses;

	/**
	 * @param Response[] $responses
	 */
	public function __construct(array $responses) {
		$this->responses = $responses;
	}

	public function getFirstResponse() {
		return $this->responses[0];
	}

	public function getLastResponse() {
		$lastIndex = count($this->responses) - 1;

		return $this->responses[$lastIndex];
	}

	public function getResponses() {
		return $this->responses;
	}

}
