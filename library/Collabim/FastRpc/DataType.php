<?php

namespace Collabim\FastRpc;

class DataType {

	const TYPE_MAGIC = 25;
	const TYPE_CALL = 13;
	const TYPE_RESPONSE = 14;
	const TYPE_FAULT = 15;

	const TYPE_INT = 1;
	const TYPE_BOOL = 2;
	const TYPE_DOUBLE = 3;
	const TYPE_STRING = 4;
	const TYPE_DATETIME = 5;
	const TYPE_BINARY = 6;
	const TYPE_INT8P = 7;
	const TYPE_INT8N = 8;
	const TYPE_STRUCT = 10;
	const TYPE_ARRAY = 11;
	const TYPE_NULL = 12;

}
