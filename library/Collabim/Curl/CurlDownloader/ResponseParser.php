<?php

namespace Collabim\Curl\CurlDownloader;

class ResponseParser {

	private $bodyDecoder;

	public function __construct(BodyDecoder $bodyDecoder) {
		$this->bodyDecoder = $bodyDecoder;
	}

	public function extractCode($responseString)
	{
		preg_match("|^HTTP/[\d\.x]+ (\d+)|", $responseString, $m);

		if (isset($m[1])) {
			return (int) $m[1];
		}
		else {
			return false;
		}
	}

	/**
	 * Extract the headers from a response string
	 *
	 * @param   string $responseString
	 * @return  array
	 */
	public function extract($responseString, $ttl = 10)
	{
		$responses = array();

		$this->parsePart($responseString, $responses, $ttl);

		return $responses;
	}

	private function parsePart($responseString, &$responses, $ttl) {
		if ($ttl <= 0) {
			throw new \Exception('Too many function calls');
		}

		$ttl--;

		// First, split body and headers
		$parts = preg_split('~(?:\r?\n){2}~m', $responseString, 2);

		if (!$parts[0]) {
			return;
		}

		$headers = $this->extractHeaders($parts[0]);
		$statusCode = $this->extractCode($parts[0]);

		if ($this->partIsHeadersPart($parts[1])) {
			$responses[] = new Response(null, $statusCode, $headers);

			$this->parsePart($parts[1], $responses, $ttl);
		}
		else {
			$contentEncoding = $this->getHeader('content-encoding', $headers);

			$decodedBody = $this->bodyDecoder->decodeBody($parts[1], $contentEncoding);

			$responses[] = new Response($decodedBody, $statusCode, $headers);
		}
	}

	private function getHeader($name, $headers) {
		return array_key_exists($name, $headers) ? $headers[$name] : null;
	}

	private function partIsHeadersPart($content) {
		return (bool) preg_match('~^HTTP/1~', trim($content));
	}

	private function extractHeaders($headersString) {
		$headers = array();

		// Split headers part to lines
		$lines = explode("\n", $headersString);
		unset($parts);
		$last_header = null;

		foreach($lines as $line) {
			$line = trim($line, "\r\n");
			if ($line == "") break;

			// Locate headers like 'Location: ...' and 'Location:...' (note the missing space)
			if (preg_match("|^([\w-]+):\s*(.+)|", $line, $m)) {
				unset($last_header);
				$h_name = strtolower($m[1]);
				$h_value = $m[2];

				if (isset($headers[$h_name])) {
					if (! is_array($headers[$h_name])) {
						$headers[$h_name] = array($headers[$h_name]);
					}

					$headers[$h_name][] = $h_value;
				} else {
					$headers[$h_name] = $h_value;
				}
				$last_header = $h_name;
			} elseif (preg_match("|^\s+(.+)$|", $line, $m) && $last_header !== null) {
				if (is_array($headers[$last_header])) {
					end($headers[$last_header]);
					$last_header_key = key($headers[$last_header]);
					$headers[$last_header][$last_header_key] .= $m[1];
				} else {
					$headers[$last_header] .= $m[1];
				}
			}
		}

		return $headers;
	}

}
