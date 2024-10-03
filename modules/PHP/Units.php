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
		self::DbQuery("INSERT INTO units (faction,type,location) VALUES ('$faction','$type','$location')");
		return self::DbGetLastId();
	}
	static function getAllDatas(): array
	{
		return self::getCollectionFromDB("SELECT * FROM units WHERE location REGEXP '^[0-9]+$' ORDER BY faction,type");
	}
	static function get(int $id): array
	{
		return self::getObjectFromDB("SELECT * FROM units WHERE id = $id");
	}
	static function draw(string $bag, string $location): void
	{
		self::DbQuery("UPDATE units SET location = '$location' WHERE location = '$bag' ORDER BY RAND() LIMIT 1");
	}
	static function move(int $id, string $location): void
	{
		self::DbQuery("UPDATE units SET location = '$location' WHERE id = $id");
	}
}
