<?php
namespace LBChat\Command;

use LBChat\ChatClient;

abstract class Command implements ICommand {
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

	public function __construct($client) {
		$this->client = $client;
	}
}