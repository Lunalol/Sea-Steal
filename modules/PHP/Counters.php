<?php

/**
 *
 * @author Lunalol - PERRIN Jean-Luc
 *
 */
class Counters extends APP_GameClass
{
	static function create(string $type, string $location): int
	{
		self::DbQuery("INSERT INTO counters (type,location) VALUES ('$type','$location')");
		return self::DbGetLastId();
	}
	static function getAllDatas(): array
	{
		return self::getCollectionFromDB("SELECT * FROM counters ORDER BY type");
	}
}
