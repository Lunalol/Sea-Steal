<?php

/**
 *
 * @author Lunalol - PERRIN Jean-Luc
 *
 */
class Factions extends APP_GameClass
{
	static function create(string $faction, int $player_id): int
	{
		self::DbQuery("INSERT INTO factions (faction,player_id) VALUES ('$faction','$player_id')");
		return self::DbGetLastId();
	}
	static function getAllDatas(): array
	{
		return self::getCollectionFromDB("SELECT faction,player_id FROM factions", true);
	}
	static function getPlayer(string $faction): int
	{
		return self::getUniqueValueFromDB("SELECT player_id FROM factions WHERE faction = '$faction'");
	}
	static function getStatus(string $faction, string $status)
	{
		return json_decode(self::getUniqueValueFromDB("SELECT JSON_UNQUOTE(status->'$.$status') FROM factions WHERE faction = '$faction'"), JSON_OBJECT_AS_ARRAY);
	}
	static function setStatus(string $faction, string $status, $value = null): void
	{
		if (is_null($value)) self::dbQuery("UPDATE factions SET status = JSON_REMOVE(status, '$.$status') WHERE faction = '$faction'");
		else
		{
			$json = self::escapeStringForDB(json_encode($value));
			self::dbQuery("UPDATE factions SET status = JSON_SET(status, '$.$status', '$json') WHERE faction = '$faction'");
		}
	}
}
