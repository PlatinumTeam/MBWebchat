<?php
namespace LBChat\Command\Server;

use LBChat\ChatServer;

abstract class Command {
	/**
	 * @var ChatServer $server The chat server
	 */
	protected $server;
	/**
	 * @var string $name The command's name
	 */
	protected $name;
	/**
	 * @var string $data The command message data
	 */
	protected $data;

	public function __construct(ChatServer $server) {
		$this->server = $server;
	}
}