<?php
namespace LBChat\Filter;

use LBChat\ChatClient;
use LBChat\ChatServer;

interface IChatFilter {
	/**
	 * @param ChatServer $server The chat server
	 * @param ChatClient $client The client who sent the message
	 * @param string $message The message to filter
	 * @return boolean If the message should be shown
	 */
	public static function filterMessage(ChatServer $server, ChatClient $client, &$message);
}