<?php
namespace LBChat\Filter;

use LBChat\ChatClient;
use LBChat\ChatServer;

abstract class ChatFilter {
	protected $server;
	protected $client;

	public function __construct(ChatServer $server, ChatClient $client) {
		$this->server = $server;
		$this->client = $client;
	}

	/**
	 * @param string $message The message to filter
	 * @return boolean If the message should be shown
	 */
	public abstract function filterMessage(&$message);
}