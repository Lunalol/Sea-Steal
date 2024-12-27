<?php

/**
 *
 * @author Lunalol - PERRIN Jean-Luc
 *
 */
class Factions extends APP_GameClass
{
	const INDIGENOUS = 'Indigenous';
	const SPANISH = 'Spanish';
//
	static function create(string $faction, int $player_id): int
	{
		self::DbQuery("INSERT INTO factions (faction,player_id,status) VALUES ('$faction','$player_id','{}')");
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
	static function getImpulse(string $faction): int
	{
		return intval(self::getUniqueValueFromDB("SELECT impulse FROM factions WHERE faction = '$faction'"));
	}
	static function setImpulse(string $faction, int $impulse = 0): void
	{
		self::dbQuery("UPDATE factions SET impulse = $impulse WHERE faction = '$faction'");
	}
	static function getStatus(string $faction, string $status)
	{
		return json_decode(self::getUniqueValueFromDB("SELECT JSON_UNQUOTE(status->'$.$status') FROM factions WHERE faction = '$faction'"), JSON_OBJECT_AS_ARRAY);
	}
	static function setStatus(string $faction, string $status, mixed $value = null): void
	{
		if (is_null($value)) self::dbQuery("UPDATE factions SET status = JSON_REMOVE(status, '$.$status') WHERE faction = '$faction'");
		else
		{
			$json = self::escapeStringForDB(json_encode($value));
			self::dbQuery("UPDATE factions SET status = JSON_SET(status, '$.$status', '$json') WHERE faction = '$faction'");
		}
	}
	static function other(string $faction): string
	{
		return [self::INDIGENOUS => self::SPANISH, self::SPANISH => self::INDIGENOUS][$faction];
	}
	static function VP()
	{
		$combatLocations = Units::getCombatLocations();
//
		$VP = 0;
//
		$attestor = array_column(Counters::getByType('attestor'), 'location');
		foreach (array_unique(array_merge(Units::getAreas(self::SPANISH), array_column(Counters::getByType('citadels'), 'location'))) as $location)
		{
			if (!in_array($location, $combatLocations))
			{
				if (in_array($location, Counters::ATTESTORS) && !in_array($location, $attestor)) $VP += 2;
				else $VP += 1;
			}
		}
//
		return $VP;
	}
}
