<?php
namespace LBChat\Misc;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

/**
 * Class PlainServer
 * Simple wrapper interface that wraps connections with a PlainConnection
 * so that plaintext clients can parse the server data.
 * @package LBChat\Misc
 */
class PlainServer implements MessageComponentInterface {

	/**
	 * @var MessageComponentInterface $component
	 */
	protected $component;

	/**
	 * @var \SplObjectStorage $connectionMap
	 */
	protected $connectionMap;

	/**
	 * @param MessageComponentInterface $component
	 */
	public function __construct(MessageComponentInterface $component) {
		$this->component = $component;
		$this->connectionMap = new \SplObjectStorage();
	}

	/**
	 * When a new connection is opened it will be passed to this method
	 *
	 * @param  ConnectionInterface $conn The socket/connection that just connected to your application
	 *
	 * @throws \Exception
	 */
	function onOpen(ConnectionInterface $conn) {
		$this->connectionMap->attach($conn, new PlainConnection($conn));
		$this->component->onOpen($this->connectionMap[$conn]);
	}

	/**
	 * This is called before or after a socket is closed (depends on how it's closed).  SendMessage to $conn will not result in an error if it has already been closed.
	 *
	 * @param  ConnectionInterface $conn The socket/connection that is closing/closed
	 *
	 * @throws \Exception
	 */
	function onClose(ConnectionInterface $conn) {
		$this->component->onClose($this->connectionMap[$conn]);
	}

	/**
	 * If there is an error with one of the sockets, or somewhere in the application where an Exception is thrown,
	 * the Exception is sent back down the stack, handled by the Server and bubbled back up the application through this method
	 *
	 * @param  ConnectionInterface $conn
	 * @param  \Exception          $e
	 *
	 * @throws \Exception
	 */
	function onError(ConnectionInterface $conn, \Exception $e) {
		$this->component->onError($this->connectionMap[$conn], $e);
	}

	/**
	 * Triggered when a client sends data through the socket
	 *
	 * @param  \Ratchet\ConnectionInterface $from The socket/connection that sent the message to your application
	 * @param  string                       $msg The message received
	 *
	 * @throws \Exception
	 */
	function onMessage(ConnectionInterface $from, $msg) {
		$this->component->onMessage($this->connectionMap[$from], $msg);
	}
}