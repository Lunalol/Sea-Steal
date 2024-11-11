<?php

/**
 *
 * @author Lunalol - PERRIN Jean-Luc
 *
 */
class Units extends APP_GameClass
{
	static function create(string $faction, string $type, string $location): int
	{
		self::DbQuery("INSERT INTO units (faction,type,location,bag) VALUES ('$faction','$type','$location','$location')");
		return self::DbGetLastId();
	}
	static function getAllDatas(): array
	{
		return self::getCollectionFromDB("SELECT * FROM units WHERE location REGEXP '^[0-9]+$' OR location = 'prisonInSpain' ORDER BY faction,type,reduced");
	}
	static function get(int $id): array
	{
		return self::getObjectListFromDB("SELECT * FROM units WHERE id = $id")[0];
	}
	static function getColumbus(): array
	{
		return self::getObjectListFromDB("SELECT * FROM units WHERE faction = 'Spanish' and type = 'Leader'")[0];
	}
	static function getAtLocation(string $location, string|null $faction = null, string|null $type = null): array
	{
		$query = "SELECT * FROM units WHERE location = '$location'";
		if ($faction) $query .= " AND faction = '$faction'";
		if ($type) $query .= " AND type = '$type'";
		return self::getCollectionFromDB($query . " ORDER BY faction,type,reduced");
	}
	static function getEnemyAtLocation(string $location, string $faction): array
	{
		return self::getCollectionFromDB("SELECT * FROM units WHERE location = '$location' AND faction <> '$faction' ORDER BY faction,type,reduced");
	}
	static function draw(string $bag, string $location): void
	{
		self::DbQuery("UPDATE units SET location = '$location' WHERE location = '$bag' ORDER BY RAND() LIMIT 1");
	}
	static function update(array $unit): void
	{
		self::DbQuery("UPDATE units SET location = '$unit[location]',reduced = '$unit[reduced]' WHERE id = $unit[id]");
	}
	static function getCombatLocations(): array
	{
		return self::getObjectListFromDB("SELECT DISTINCT attacker.location FROM units AS attacker JOIN units AS defender WHERE attacker.location > 0 AND attacker.location = defender.location AND attacker.faction <> defender.faction", true);
	}
	static function getAreas(string $faction): array
	{
		return self::getObjectListFromDB("SELECT DISTINCT location FROM units WHERE faction = '$faction' AND location REGEXP '^[0-9]+$'", true);
	}
	static function overstacking(string $location, string $faction): int
	{
		return intval(self::getUniqueValueFromDB("SELECT COUNT(*) FROM units WHERE location = '$location' AND faction = '$faction' AND type NOT IN ('Leader')"));
	}
	static function retreat(array $unit): array
	{
		$locations = [];
		if (is_numeric($unit['location']))
		{
			foreach ([(($unit['location'] + 1 - 1) % 15) + 1, (($unit['location'] - 1 - 1 + 15) % 15) + 1] as $adjacent)
			{
				if (sizeof(Units::getEnemyAtLocation($adjacent, $unit['faction'])) === 0) $locations[] = $adjacent;
			}
		}
//
		return $locations;
	}
}
