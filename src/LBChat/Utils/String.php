<?php
namespace LBChat\Utils;

abstract class String {

	public static function encodeSpaces($str) {
		$str = str_replace(" ",  "-SPC-", $str);
		$str = str_replace("\t", "-TAB-", $str);
		$str = str_replace("\n", "-NL-",  $str);
		return $str;
	}

	public static function decodeSpaces($str) {
		$str = str_replace("-SPC-", " ",  $str);
		$str = str_replace("-TAB-", "\t", $str);
		$str = str_replace("-NL-",  "\n", $str);
		return $str;
	}
}