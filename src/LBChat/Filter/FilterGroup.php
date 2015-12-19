<?php
namespace LBChat\Filter;

use LBChat\ChatClient;
use LBChat\ChatServer;

/**
 * Simple class that combines an array of filters and applies them all at the same time.
 * @package LBChat\Filter
 */
class FilterGroup extends ChatFilter {

	/**
	 * @var array $filters
	 */
	protected $filters;

	public function __construct(ChatServer $server, ChatClient $client, array $filters) {
		parent::__construct($server, $client);
		$this->filters = $filters;
	}

	/**
	 * @param string $message The message to filter
	 * @return boolean If the message should be shown
	 */
	public function filterMessage(&$message) {
		//Run through all filters
		foreach ($this->filters as $filter) {
			/* @var ChatFilter $filter */
			if (!$filter->filterMessage($message))
				return false;
		}
		return true;
	}
}