<?php

namespace Collabim\FastRpc;

class SearchRequest implements IRequest {

	const TYPE_ALL = 'all';

	private $type;
	private $q;
	private $from;
	private $pId;

	public function __construct($type, $q, $from, $pId = null) {
		$this->type = $type;
		$this->q = $q;
		$this->from = $from;
		$this->pId = $pId;
	}

	public function getMethod() {
		return 'search';
	}

	public function getData() {
		$params = array(
			'type' => $this->type,
			'q' => $this->q,
			'from' => $this->from
		);

		if ($this->pId) {
			$params['pId'] = $this->pId;
		}

		return $params;
	}

}
