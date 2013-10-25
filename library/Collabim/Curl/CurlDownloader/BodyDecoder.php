<?php

namespace Collabim\Curl\CurlDownloader;

class BodyDecoder {

	public function decodeBody($body, $contentEncoding) {
		// Decode any content-encoding (gzip or deflate) if needed
		switch (strtolower($contentEncoding)) {

			// Handle gzip encoding
			case 'gzip':
				$body = $this->decodeGzip($body);
				break;

			// Handle deflate encoding
			case 'deflate':
				$body = $this->decodeDeflate($body);
				break;
		}

		return $body;
	}

	/**
	 * Decode a gzip encoded message (when Content-encoding = gzip)
	 * Currently requires PHP with zlib support
	 */
	private function decodeGzip($body) {
		if (!function_exists('gzinflate')) {
			throw new \Exception('zlib extension is required in order to decode "gzip" encoding');
		}

		return @gzinflate(substr($body, 10));
	}

	/**
	 * Decode a zlib deflated message (when Content-encoding = deflate)
	 * Currently requires PHP with zlib support
	 */
	private function decodeDeflate($body)
	{
		if (!function_exists('gzuncompress')) {
			throw new \Exception('zlib extension is required in order to decode "deflate" encoding');
		}

		/**
		 * Some servers (IIS ?) send a broken deflate response, without the
		 * RFC-required zlib header.
		 *
		 * We try to detect the zlib header, and if it does not exsit we
		 * teat the body is plain DEFLATE content.
		 *
		 * This method was adapted from PEAR HTTP_Request2 by (c) Alexey Borzov
		 *
		 * @link http://framework.zend.com/issues/browse/ZF-6040
		 */
		$zlibHeader = unpack('n', substr($body, 0, 2));

		if ($zlibHeader[1] % 31 == 0) {
			return @gzuncompress($body);
		}
		else {
			return @gzinflate($body);
		}
	}

}
