<?php

namespace Collabim\Curl\CurlDownloader;

class RawPostData implements IPostData {

	private $rawPostDataString;

	public function __construct($rawPostDataString) {
		if ((string)$rawPostDataString !== $rawPostDataString) {
			throw new \Exception('RAW POST data must be string');
		}

		$this->rawPostDataString = $rawPostDataString;
	}

	public function getRawPostDataString() {
		return $this->rawPostDataString;
	}

}
