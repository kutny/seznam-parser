<?php

namespace Collabim\FastRpc;

/**
 * Based on https://github.com/seznam/JAK/blob/master/util/frpc.js
 */
class ResponseDecoder {

	public function decode($data) {
		$dataEnvelope = new BinaryDataEnvelope($data);

		$magic1 = $dataEnvelope->getByte();
		$magic2 = $dataEnvelope->getByte();

		if ($magic1 != 0xCA || $magic2 != 0x11) {
			throw new \Exception('Missing FRPC magic');
		}

		/* zahodit zbytek hlavicky */
		$dataEnvelope->getByte();
		$dataEnvelope->getByte();

		$first = $this->getInt(1, $dataEnvelope);
		$type = $first >> 3;
		if ($type == DataType::TYPE_FAULT) {
			$num = $this->parseValue($dataEnvelope);
			$msg = $this->parseValue($dataEnvelope);
			throw new \Exception('FRPC/'. $num . ': ' . $msg);
		}

		$result = null;

		switch ($type) {
			case DataType::TYPE_RESPONSE:
				$result = $this->parseValue($dataEnvelope);
				if (!$dataEnvelope->allDataProcessed()) {
					throw new \Exception('Garbage after FRPC data');
				}
				break;

			case DataType::TYPE_CALL:
				$nameLength = $this->getInt(1, $dataEnvelope);
				$name = $dataEnvelope->getBytes($nameLength);
				$params = array();
				while (!$dataEnvelope->allDataProcessed()) {
					$params[] = $this->parseValue($dataEnvelope);
				}

				return array(
					'method' => $name,
					'params' => $params
				);

			default:
				throw new \Exception('Unsupported FRPC type ' . $type);
		}

		return $result;
	}

	private function parseValue(BinaryDataEnvelope $dataEnvelope) {
		$first = $this->getInt(1, $dataEnvelope);
		$type = $first >> 3;

		switch ($type) {

			case DataType::TYPE_STRING:
				$lengthBytes = ($first & 7) + 1;
				$length = $this->getInt($lengthBytes, $dataEnvelope);
				return $dataEnvelope->getBytes($length);

			case DataType::TYPE_STRUCT:
				$result = array();
				$lengthBytes = ($first & 7) + 1;
				$members = $this->getInt($lengthBytes, $dataEnvelope);
				while ($members--) {
					$result = $this->parseMember($result, $dataEnvelope);
				}
				return $result;

			case DataType::TYPE_ARRAY:
				$result = array();
				$lengthBytes = ($first & 7) + 1;
				$members = $this->getInt($lengthBytes, $dataEnvelope);
				while ($members--) {
					$result[] = $this->parseValue($dataEnvelope);
				}
				return $result;

			case DataType::TYPE_BOOL:
				return ($first & 1 ? true : false);

			case DataType::TYPE_INT:
				$length = $first & 7;

				$max = pow(2, 8 * $length);
				$result = $this->getInt($length, $dataEnvelope);
				if ($result >= $max / 2) { $result -= $max; }
				return $result;

			case DataType::TYPE_DOUBLE:
				return $this->getDouble($dataEnvelope);

			case DataType::TYPE_BINARY:
				$lengthBytes = ($first & 7) + 1;
				$length = $this->getInt($lengthBytes, $dataEnvelope);
				$result = array();
				while ($length--) {
					$result[] = $dataEnvelope->getByte();
				}
				return $result;

			case DataType::TYPE_INT8P:
				$length = ($first & 7) + 1;
				return $this->getInt($length, $dataEnvelope);

			case DataType::TYPE_INT8N:
				$length = ($first & 7) + 1;
				return -$this->getInt($length, $dataEnvelope);

			case DataType::TYPE_NULL:
				return null;

			case DataType::TYPE_DATETIME:
				throw new \Exception('Not implemented yet: TYPE_DATETIME');

			default:
				throw new \Exception('Unkown FRPC type ' . $type);
				break;
		}
	}

	private function getInt($bytes, BinaryDataEnvelope $dataEnvelope) {
		$result = 0;
		$factor = 1;
	
		for ($i = 0; $i < $bytes; $i++) {
			$result += $factor * $dataEnvelope->getByte();

			$factor *= 256;
		}
			
		return $result;
	}

	private function getDouble(BinaryDataEnvelope $dataEnvelope) {
		$bytes = array();
		$index = 8;
		while ($index--) {
			$bytes[$index] = $dataEnvelope->getByte();
		}

		$sign = ($bytes[0] & 0x80 ? 1 : 0);
        
        $exponent = ($bytes[0] & 127) << 4;
        $exponent += $bytes[1] >> 4;
        
        if ($exponent == 0) {
			return pow(-1, $sign) * 0;
		}
        
        $mantissa = 0;
        $byteIndex = 1;
        $bitIndex = 3;
        $index = 1;
        
        do {
			$bitValue = ($bytes[$byteIndex] & (1 << $bitIndex) ? 1 : 0);
                $mantissa += $bitValue * pow(2, -$index);
                
                $index++;
                $bitIndex--;
                if ($bitIndex < 0) {
					$bitIndex = 7;
					$byteIndex++;
				}
        } while ($byteIndex < count($bytes));
        
        if ($exponent == 0x7ff) {
			if ($mantissa) {
				return null;
			}
			else {
				return pow(-1, $sign) * log(0);
			}
		}
        
        $exponent -= (1 << 10) - 1;

        return pow(-1, $sign) * pow(2, $exponent) * (1+$mantissa);
	}
	
	private function parseMember($result, BinaryDataEnvelope $dataEnvelope) {
		$nameLength = $this->getInt(1, $dataEnvelope);
		$name = $dataEnvelope->getBytes($nameLength);
		$result[$name] = $this->parseValue($dataEnvelope);

		return $result;
	}
	
}
