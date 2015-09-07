<?php
namespace LBChat\Misc;

use Ratchet\ConnectionInterface;

/**
 * Class PlainConnection
 * Simple wrapper connection that appends newlines to messages so that
 * plaintext connections can parse data.
 * @package LBChat\Misc
 */
class PlainConnection implements ConnectionInterface {

	/**
	 * @var ConnectionInterface $interface
	 */
	protected $interface;

	public $resourceId;

	/**
	 * @param ConnectionInterface $interface
	 */
	public function __construct(ConnectionInterface $interface) {
		$this->interface = $interface;
		$this->resourceId = "dup {$interface->resourceId}";
	}

	/**
	 * Send data to the connection
	 *
	 * @param  string $data
	 *
	 * @return \Ratchet\ConnectionInterface
	 */
	function send($data) {
		$this->interface->send($data . "\n");
	}

	/**
	 * Close the connection
	 */
	function close() {
		$this->interface->close();
	}
}