<?php
namespace LBChat\Command\Chat;

use LBChat\ChatClient;
use LBChat\ChatServer;

abstract class Command {
	/**
	 * @var ChatServer $server The chat server
	 */
	protected $server;
	/**
	 * @var ChatClient $client The client to which the command is attached
	 */
	protected $client;
	/**
	 * @var string $name The command's name
	 */
	protected $name;
	/**
	 * @var string $data The command message data
	 */
	protected $data;

	public function __construct(ChatServer $server, ChatClient $client) {
		$this->server = $server;
		$this->client = $client;
	}
}