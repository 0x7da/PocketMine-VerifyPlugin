<?php
/*
 * Copyright (c) 2021 Jan Sohn.
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */

declare(strict_types=1);
namespace xxAROX\Utils;
use Medoo\Medoo;


/**
 * Class SQLite3Database
 * @package xxAROX\Utils
 * @author Jan Sohn / xxAROX - <jansohn@hurensohn.me>
 * @date 29. April, 2021 - 22:37
 * @ide PhpStorm
 * @project Core
 */
class SQLite3Database{
	protected Medoo $medoo;

	public function __construct(string $file){
		$this->medoo = new Medoo([
			"database_type" => "sqlite",
			"database_file" => $file
		]);
	}

	/**
	 * Function getMedoo
	 * @return Medoo
	 */
	public function getMedoo(): Medoo{
		return $this->medoo;
	}

	/**
	 * Function isTable
	 * @param string $tableName
	 * @return bool
	 */
	public function isTable(string $tableName): bool{
		return is_object($this->medoo->query("SELECT 1 FROM `{$tableName}` LIMIT 1;")) ? true : $this->medoo->query("SELECT 1 FROM `{$tableName}` LIMIT 1;");
	}

	/**
	 * Function createTable
	 * @param string $tableName
	 * @param string $body
	 * @return void
	 */
	public function createTable(string $tableName, string $body): void{
		$this->medoo->query("CREATE TABLE `{$tableName}`({$body});");
	}
}
