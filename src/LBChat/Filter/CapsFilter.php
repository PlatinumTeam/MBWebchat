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

		// check to see if at least 50% of the message was caps.
		// if it was, replace the entire message with lowercase letters!
		$half = (int)floor(strlen($filteredMessage) / 2);
		if (preg_match_all("/(?<!\\w)\\w/", $filteredMessage, $matches) >= $half) {
			// lowercase the actual message
			preg_replace("/(?<!\\w)\\w/", "?<=\\w)\\w", $message);
		}

		return true;
	}
}