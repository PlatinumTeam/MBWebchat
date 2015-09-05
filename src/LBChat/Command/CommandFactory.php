<?php
namespace LBChat\Command;

abstract class CommandFactory {
	/**
	 * @param $msg
	 * @return Command
	 */
	public static function construct($client, $msg) {
		$words = explode(" ", $msg);
		if (count($words) == 0)
			return null;

		$first = array_shift($words);
		$rest = implode(" ", $words);

		switch ($first) {
		case "IDENTIFY": return new IdentifyCommand($client, $rest);

		}
	}
}