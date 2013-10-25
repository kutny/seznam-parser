<?php

namespace Collabim\String;

class Utf8StringHandler {

	public function encodeUtf8($str) {
		$result = array();

		for ($i = 0; $i < mb_strlen($str); $i++) {
			$char = mb_substr($str, $i, 1);
			$c = $this->ordUnicode($char);

			if ($c < 128) {
				$result[] = $c;
			}
			else if (($c > 127) && ($c < 2048)) {
				$result[] = (($c >> 6) | 192);
				$result[] = (($c & 63) | 128);
			}
			else {
				$result[] = (($c >> 12) | 224);
				$result[] = ((($c >> 6) & 63) | 128);
				$result[] = (($c & 63) | 128);
			}
		}

		return $result;
	}

	private function ordUnicode($u) {
		$k = mb_convert_encoding($u, 'UCS-2LE', 'UTF-8');
		$k1 = ord(substr($k, 0, 1));
		$k2 = ord(substr($k, 1, 1));
		return $k2 * 256 + $k1;
	}

}
