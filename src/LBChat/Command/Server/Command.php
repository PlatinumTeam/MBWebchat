<?php
namespace LBChat\Command\Server;

use LBChat\ChatServer;

abstract class Command {
	/**
	 * @var ChatServer $server The chat server
	 */
	protected $server;

	public function __construct(ChatServer $server) {
		$this->server = $server;
	}
}