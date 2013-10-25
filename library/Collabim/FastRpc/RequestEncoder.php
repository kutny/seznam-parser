<?php

namespace Collabim\FastRpc;
use Collabim\String\Utf8StringHandler;

/**
 * Based on https://github.com/seznam/JAK/blob/master/util/frpc.js
 */
class RequestEncoder {

	private $utf8StringHandler;

	public function __construct(Utf8StringHandler $utf8StringHandler) {
		$this->utf8StringHandler = $utf8StringHandler;
	}

	public function encode(IRequest $request) {
		$result = array();
		$this->serializeValue($result, array($request->getData()));

		/* utrhnout hlavicku pole (dva bajty) */
		array_shift($result);
		array_shift($result);
		array_shift($result);
		array_shift($result);

		$encodedMethod = $this->utf8StringHandler->encodeUTF8($request->getMethod());

		for ($i = count($encodedMethod) - 1; $i >= 0; $i--) {
			array_unshift($result, $encodedMethod[$i]);
		}

		array_unshift($result, count($encodedMethod));

		array_unshift($result, DataType::TYPE_CALL << 3);
		array_unshift($result, 0xCA, 0x11, 0x02, 0x00);

		return implode('', array_map('chr', $result));
	}

	private function serializeValue(&$result, $value) {
		if ($value === null) {
			$result[] = DataType::TYPE_NULL << 3;
			return;
		}

		switch (gettype($value)) {
			case 'string':
				$strData = $this->utf8StringHandler->encodeUtf8($value);
				$intData = $this->encodeInt(count($strData));

				$first = DataType::TYPE_STRING << 3;
				$first += (count($intData) - 1);

				$result[] = $first;

				$this->append($result, $intData);
				$this->append($result, $strData);
				break;

			case 'double':
				throw new \Exception('Not Implemented: float serialization');

			case 'integer':
				$first = ($value > 0 ? DataType::TYPE_INT8P : DataType::TYPE_INT8N);
				$first = $first << 3;

				$data = $this->encodeInt(abs($value));
				$first += count($data) - 1;

				$result[] = $first;
				$this->append($result, $data);
				break;

			case 'boolean':
				$data = DataType::TYPE_BOOL << 3;
				if ($value) {
					$data += 1;
				}

				$result[] = $data;
				break;

			case 'array':
				$this->serializeStruct($result, $value);
				break;

			default:
				throw new \Exception('FRPC does not allow value ' . $value);
				break;
		}
	}

	private function append(&$arr1, $arr2) {
		$len = count($arr2);
		
		for ($i = 0; $i < $len; $i++) {
			$arr1[] = $arr2[$i];
		}
	}

	private function encodeInt($data) {
		if (!$data) {
			return [0];
		}

		$result = array();
		$remain = $data;

		while ($remain) {
			$value = $remain % 256;
			$remain = ($remain - $value) / 256;
			$result[] = $value;
		}

		return $result;
	}

	private function serializeStruct(&$result, $data) {
		$numMembers = 0;
		
		foreach ($data as $value) {
			$numMembers++;
		}
	
		$first = DataType::TYPE_STRUCT << 3;
		$intData = $this->encodeInt($numMembers);
		$first += (count($intData) - 1);
		
		$result[] = $first;
		$this->append($result, $intData);

		foreach ($data as $p => $value) {
			$strData = $this->utf8StringHandler->encodeUtf8($p);

			$result[] = count($strData);

			$this->append($result, $strData);
			$this->serializeValue($result, $data[$p]);
		}
	}

}
