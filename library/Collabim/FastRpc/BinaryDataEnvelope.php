<?php

namespace Collabim\FastRpc;

class BinaryDataEnvelope {

	private $data;
	private $pointer;

	public function __construct($data) {
		$this->data = $data;
		$this->pointer = 0;
	}

	public function getByte() {
		if (($this->pointer + 1) > strlen($this->data)) {
			throw new \Exception('Cannot read byte from buffer');
		}

		return hexdec(bin2hex($this->data[$this->pointer++]));
	}

	public function getBytes($length) {
		if (!$length) {
			return '';
		}

		$remain = $length;
		$result = '';

		do {
			$remain--;
			$result .= $this->data[$this->pointer++];
		}
		while ($remain > 0);

		return $result;
	}

	public function allDataProcessed() {
		return $this->pointer >= strlen($this->data);
	}
	
}
