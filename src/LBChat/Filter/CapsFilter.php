<?php
namespace LBChat\Filter;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Utils\String;

class CapsFilter extends ChatFilter {
	/**
	 * @param string $message The message to filter
	 * @return boolean If the message should be shown
	 */
	public function filterMessage(&$message) {
		// first things first. strip formatting
		$filteredMessage = String::stripFormatting($message);

		// check to see if at least 50% of the message was caps.
		// if it was, replace the entire message with lowercase letters!
		$half = (int)floor(strlen($filteredMessage) / 2);
		if (preg_match_all("/[A-Z]/", $filteredMessage, $matches) >= $half) {
			// lowercase the actual message

			//Replace all letters that have a letter before them (in groups), use a callback function to lowercase
			$message = preg_replace_callback("/((?<=\\w)\\w+)/", function($matches) {
				return strtolower($matches[1]);
			}, $message);
		}

		return true;
	}
}