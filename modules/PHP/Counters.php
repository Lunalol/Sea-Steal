<?php

/**
 *
 * @author Lunalol - PERRIN Jean-Luc
 *
 */
class Counters extends APP_GameClass
{
	const ATTESTORS = [1, 2, 7, 8, 9];
//
	static function create(string $type, string $location): int
	{
		self::DbQuery("INSERT INTO counters (type,location) VALUES ('$type','$location')");
		return self::DbGetLastId();
	}
	static function destroy(int $id): void
	{
		self::dBQuery("DELETE FROM counters WHERE id = $id");
	}
	static function getAllDatas(): array
	{
		return self::getCollectionFromDB("SELECT * FROM counters ORDER BY type");
	}
	static function get(int|null $id): array|null
	{
		if ($id) return self::getObjectListFromDB("SELECT * FROM counters WHERE id = $id")[0];
		return null;
	}
	static function getAtLocation(string $location, string $type = null): array
	{
		if (is_null($type)) return self::getCollectionFromDB("SELECT * FROM counters WHERE location = '$location'");
		return self::getCollectionFromDB("SELECT * FROM counters WHERE location LIKE '$location' AND type = '$type'");
	}
	static function getByType(string $type, bool $aside = false): array
	{
		if ($aside) return self::getObjectListFromDB("SELECT * FROM counters WHERE type = '$type'");
		return self::getObjectListFromDB("SELECT * FROM counters WHERE type = '$type' AND location <> 'aside'");
	}
	static function setLocation(int $id, string $location)
	{
		self::dBquery("UPDATE counters SET location = '$location' WHERE id = $id");
	}
	static function setType(int $id, string $type)
	{
		self::dBquery("UPDATE counters SET type = '$type' WHERE id = $id");
	}
}
