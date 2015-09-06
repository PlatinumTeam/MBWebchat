<?php
namespace LBChat\Integration;

use LBChat\Database\Database;

interface IUserSupport {
	public function __construct(Database $database);
	public function getId($username);
	public function getUsername($username);
	public function getAccess($username);
	public function getDisplayName($username);
	public function getColor($username);
	public function getTitles($username);
}