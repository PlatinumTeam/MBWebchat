<?php
namespace LBChat\Filter;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Utils\String;

class ProfanityFilter extends ChatFilter {
	//TODO: Make this list on SQL
	static $profanities = array(
		//    pattern/text to search   curse weight   text|regex|case    block entire msg  remove/replace it  what to replace with
		array("text" => "fubar",       "weight" => 1, "type" => "text",  "block" => false, "remove" => false, "replacement" => null),
		array("text" => "/je+f(?!f)/", "weight" => 2, "type" => "regex", "block" => false, "remove" => true,  "replacement" => null),
		array("text" => "higoo",       "weight" => 3, "type" => "text",  "block" => false, "remove" => true,  "replacement" => "higuy"),
		array("text" => "PQWHERe",     "weight" => 4, "type" => "case",  "block" => true,  "remove" => false, "replacement" => null),
	);

	/**
	 * @param ChatServer $server  The chat server
	 * @param ChatClient $client  The client who sent the message
	 * @param string     $message The message to filter
	 * @return boolean If the message should be shown
	 */
	public function filterMessage(&$message) {
		$words = String::getWordOptions($message);
		foreach ($words as &$word) {
			/* @var string $word */
			foreach (self::$profanities as $profanity) {
				/* @var array $profanity */

				$detect = false;

				//Case insensitive needs to use stristr
				switch ($profanity["type"]) {
				case "case":
					$detect = strstr($word, $profanity["text"]) !== FALSE;
					break;
				case "text":
					$detect = stristr($word, $profanity["text"]) !== FALSE;
					break;
				case "regex":
					//preg_match returns 1 if found, 0 if not, and FALSE on error. Who the hell designed that?
					$detect = preg_match($profanity["text"], $word) === 1;
					break;
				}

				//Don't care if it doesn't match
				if (!$detect) {
					continue;
				}

				if ($profanity["block"]) {
					//Your entire message is blocked
					return false;
				}

				//If we want to remove it, replace it
				if ($profanity["remove"]) {
					//Use the replacement or *** if we can't find it
					$word = $profanity["replacement"] === null ? "***" : $profanity["replacement"];
				}
			}
		}

		$message = implode(" ", $words);

		return true;
	}
}