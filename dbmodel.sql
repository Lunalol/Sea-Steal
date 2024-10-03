CREATE TABLE IF NOT EXISTS `factions` (
	`faction` ENUM ('Indigenous', 'Spanish'), `player_id` INT, `status` JSON
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `units` (
	`id` INT(2) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`faction` ENUM ('Indigenous', 'Spanish'),
	`type` ENUM('Leader', 'Cavalry', 'Arquebusiers', 'Swordmen', 'Pawns', 'Scribes', 'Caciques', 'Naborias', 'Calinagos', 'Tamas', 'Captains', 'Troops'),
	`location` VARCHAR(20), `status` JSON
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `counters` (
	`id` INT(2) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`type` ENUM('turn', 'VP', 'impulseSpanish', 'impulseNatives', 'royalSupport', 'divineGrace', 'natureSpirit', 'palisades', 'citadels', 'shipsWear', 'attestor', 'area'),
	`location` VARCHAR(20), `status` JSON
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
