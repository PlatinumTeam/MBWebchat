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
	 * @param $str
	 * @return mixed
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
	 * @param $str
	 * @return mixed
	 */
	public static function decodeSpaces($str) {
		$str = str_replace("-SPC-", " ",  $str);
		$str = str_replace("-TAB-", "\t", $str);
		$str = str_replace("-NL-",  "\n", $str);
		return $str;
	}
}