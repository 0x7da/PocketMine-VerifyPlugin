<?php
/*
 * Copyright (c) 2021 Jan Sohn.
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */

declare(strict_types=1);
namespace xxAROX\Verify\utils\database;
use Frago9876543210\EasyForms\elements\Input;
use Frago9876543210\EasyForms\forms\CustomForm;
use Frago9876543210\EasyForms\forms\CustomFormResponse;
use pocketmine\Server;
use pocketmine\Player;
use xxAROX\Utils\SQLite3Database;


/**
 * Class Verify
 * @package xxAROX\Core\utils\database
 * @author Jan Sohn / xxAROX - <jansohn@hurensohn.me>
 * @date 04. Mai, 2021 - 00:24
 * @ide PhpStorm
 * @project Core
 */
class Verify{
	const CODE_LENGTH = 6;
	const CODE_EXPIRE_MINUTES = 5;
	protected static SQLite3Database $database;

	public static function init(){
		self::$database = new SQLite3Database("/home/.data/VERIFY.db");
		if (!self::$database->isTable("codes")) {
			self::$database->createTable("codes", "name VARCHAR(32),code VARCHAR(32) PRIMARY KEY,expire VARCHAR(128)");
		}
		if (!self::$database->isTable("verified")) {
			self::$database->createTable("verified", "name VARCHAR(32),discordId VARCHAR(18)");
		}
	}

	/**
	 * Function isVerifiedByName
	 * @param string $playerName
	 * @return bool
	 */
	static function isVerifiedByName(string $playerName): bool{
		return self::$database->getMedoo()->has("verified", ["name" => $playerName]);
	}

	/**
	 * Function activateVerifyCode
	 * @param MMOPlayer $player
	 * @return void
	 */
	static function activateVerifyCode(Player $player): void{
		if (self::isVerifiedByName($player->getName())) {
			self::$database->getMedoo()->delete("verified", ["name" => $player->getName()]);
		}

		if (self::$database->getMedoo()->has("codes", ["name" => $player->getName()])) {
			//$data = self::$database->getMedoo()->get("codes", ["code", "expire"], ["name" => $player->getName()]);
			//if ($data["expire"] <= time()) {
			//	self::$database->getMedoo()->delete("codes", ["name" => $player->getName()]);
			//	self::activateVerifyCode($player);
			//	return;
			//}
			//$player->sendMessage("message.verifyCodeExpiresIn", [$data["code"], self::formatOnlineTime($player, $data["expire"] -time(), true)]);
			//self::sendCopyCodeAgain($player, $data["code"], self::formatOnlineTime($player, ($data["expire"] -time()), true));
		} else {
			$code = self::generateRandomString(self::CODE_LENGTH);
			$expire = time() +(60 * self::CODE_EXPIRE_MINUTES);
			self::$database->getMedoo()->insert("codes", ["code" => $code, "expire" => $expire, "name" => $player->getName()]);
			$player->sendMessage("message.verifyCodeExpiresIn", [$code, self::formatOnlineTime($player, $expire -time(), true)]);
			self::sendCopyCodeAgain($player, $code, self::formatOnlineTime($player, ($expire -time()), true));
		}
	}

	/**
	 * Function sendCopyCodeAgain
	 * @param MMOPlayer $player
	 * @param string $code
	 * @param string $time
	 * @return void
	 */
	private static function sendCopyCodeAgain(Player $player, string $code, string $time): void{
		$player->sendForm(new CustomForm(
			"§7Code expires in {$time}",
			[new Input("Copy the code", "§l§4NOT DELETE, COPY!!", $code)],
			function (MMOPlayer $player, CustomFormResponse $response): void{},
			function (MMOPlayer $player) use ($code, $time): void{
				self::sendCopyCodeAgain($player, $code, $time);
			}
		));
	}
	/**
	 * Function generateRandomString
	 * @param int $length
	 * @param null|bool $numbers
	 * @param null|bool $lowerCase
	 * @param null|bool $upperCase
	 * @return null|string
	 */
	static function generateRandomString($length = 8, ?bool $numbers = true, ?bool $lowerCase = true, ?bool $upperCase = true) {
		if (!($numbers && $lowerCase && $upperCase)) {
			$numbers = $lowerCase = $upperCase = true;
		}
		$dictionary = (($numbers ? "1234567890" : "") . ($lowerCase ? "abcdefghijklmnopqrstuvwxyz" : "") . ($upperCase ? "ABCDEFGHIJKLMNOPQRSTUVWXYZ" : ""));
		try {
			$dictionaryLen = strlen($dictionary);
			$randomString = "";
			for ($i = 0; $i < $length; $i++) {
				$randomString .= $dictionary[random_int(0, $dictionaryLen -1)];
			}
			return $randomString;
		} catch (\Exception $exception) {
			Server::getInstance()->getLogger()->error($exception->getMessage());
		}
		return null;
	}

	/**
	 * Function formatOnlineTime
	 * @param MMOPlayer $player
	 * @param int $time
	 * @param null|bool $seconds
	 * @return string
	 */
	static function formatOnlineTime(Player $player, int $time, ?bool $seconds=false) {
		$dt1 = new \DateTime("@0");
		$dt2 = new \DateTime("@$time");
		$diff = $dt1->diff($dt2);

		$diffSeconds = (int)$diff->format("%s");
		$diffMinutes = (int)$diff->format("%i");
		$diffHours = (int)$diff->format("%h");
		$diffDays = (int)$diff->format("%a");
		$str = "";

		if ($diffDays > 0) {
			if ($diffDays != 1) {
				$str .= "{$diffDays} raw.days";
			} else {
				$str .= "{$diffDays} day";
			}
		}
		if ($diffHours > 0) {
			if ($diffDays > 0) {
				$str .= ", ";
			}
			if ($diffHours != 1) {
				$str .= "{$diffHours} hours";
			} else {
				$str .= "{$diffHours} hour";
			}
		}
		if ($diffMinutes > 0) {
			if ($diffDays > 0 || $diffHours > 0) {
				$str .= ", ";
			}
			if ($diffMinutes != 1) {
				$str .= "{$diffMinutes} minutes";
			} else {
				$str .= "{$diffMinutes} minute";
			}
		}
		if ($diffMinutes == 0) {
			$seconds = true;
		}
		if ($seconds) {
			if ($diffSeconds > 0) {
				if ($diffDays > 0 || $diffHours > 0 || $diffMinutes > 0) {
					$str .= ", ";
				}
				if ($diffSeconds != 1) {
					$str .= "{$diffSeconds} seconds";
				} else {
					$str .= "{$diffSeconds} second";
				}
			}
		}
		return $str;
	}
}
