<?php
namespace LBChat\Filter;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Utils\String;

class CapsFilter extends ChatFilter {
	/**
	 * @param ChatServer $server The chat server
	 * @param ChatClient $client The client who sent the message
	 * @param string $message The message to filter
	 * @return boolean If the message should be shown
	 */
	public function filterMessage(&$message) {
		// first things first. strip formatting
		$filteredMessage = String::stripFormatting($message);

		$len = strlen($filteredMessage);

		// check to see if everything is caps
		preg_match("/^[A-Z0-9_]+/", $filteredMessage, $matches);
		if (count($matches) == $len) {
			// every character was caps, numbers, or _
			return false;
		}

		// check to see if at least 50% of the message was caps.
		// if it was, reject the message.
		preg_match("/[A-Z]*/", $filteredMessage, $matches);
		$half = (int)floor($len / 2);
		if (count($matches) >= $half) {
			// at least half were caps lock'd, fail
			return false;
		}

		return true;
	}
}