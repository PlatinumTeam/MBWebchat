<?php
namespace LBChat\Command;

use LBChat\ChatClient;
use LBChat\ChatServer;

abstract class Command implements ICommand {
	/**
	 * @var ChatClient $client The client to which the command is attached
	 */
	protected $client;
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

	public function __construct(ChatClient $client, ChatServer $server) {
		$this->client = $client;
		$this->server = $server;
	}
}