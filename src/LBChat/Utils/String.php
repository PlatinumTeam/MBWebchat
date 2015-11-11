<?php
namespace LBChat\Utils;

/**
 * Class String
 * Basic string utility functions that have nowhere else to go
 * @package LBChat\Utils
 */
abstract class String {
	/**
	 * Encodes names and other values, swapping spaces with -SPC- . Use this whenever
	 * you need to send space-delimited commands to a client.
	 * @param string $str
	 * @return string
	 */
	public static function encodeSpaces($str) {
		$str = str_replace(" ",  "-SPC-", $str);
		$str = str_replace("\t", "-TAB-", $str);
		$str = str_replace("\n", "-NL-",  $str);
		return $str;
	}

	/**
	 * Decodes names and other values, swapping -SPC- with spaces. Use this whenever
	 * receiving space-delimited commands from a client.
	 * @param string $str
	 * @return string
	 */
	public static function decodeSpaces($str) {
		$str = str_replace("-SPC-", " ",  $str);
		$str = str_replace("-TAB-", "\t", $str);
		$str = str_replace("-NL-",  "\n", $str);
		return $str;
	}

	/**
	 * Split a string into word-separated arguments.
	 * @param string $str
	 * @return array
	 */
	public static function getWordOptions($str) {
		//PHP is a bitch: If the string is empty then explode() returns an invalid array
		if (strlen($str) == 0)
			return array();

		//Try to split it by spaces
		$words = explode(" ", $str);

		//PHP is a bitch: If the string is empty then explode() returns false
		if ($words === false) {
			return array();
		}

		//Decode spaces in every word in the array
		return array_map(function($word) {
			return self::decodeSpaces($word);
		}, $words);
	}
	/**
	 * Split a word into tokens, and return an array of them
	 * @param string $word
	 * @return array
	 */
	public static function token_to_array($word){
		$array = array();
		$token = strtok($word, ",");
		array_push($array, $token);
		while ($token !== false){
			$token = strtok(",");
			array_push($array, $token);
		}
		return $array;
	}

	/**
	 * Decode the weak string encryption from MBP
	 * @param string $str The string to decode
	 * @return string The decoded string
	 */
	public static function degarbledeguck($string) {
		if (substr($string, 0, 3) !== "gdg")
			return $string;
		$finish = "";
		for ($i = 3; $i < strLen($string); $i += 2) {
			$hex = substr($string, $i, 2);
			$val = hexdec($hex);
			$char = chr(128 - $val);
			$finish = $char . $finish;
		}
		return $finish;
	}
}