<?php
namespace LBChat\Misc;

use Ratchet\AbstractConnectionDecorator;

/**
 * Class PlainConnection
 * Simple wrapper connection that appends newlines to messages so that
 * plaintext connections can parse data.
 * @package LBChat\Misc
 */
class PlainConnection extends AbstractConnectionDecorator {

	/**
	 * Send data to the connection
	 *
	 * @param  string $data
	 *
	 * @return \Ratchet\ConnectionInterface
	 */
	function send($data) {
		$this->getConnection()->send($data . "\n");
	}

	/**
	 * Close the connection
	 */
	function close() {
		$this->getConnection()->close();
	}
}