CREATE TABLE IF NOT EXISTS `factions` (
	`faction` ENUM ('Indigenous', 'Spanish'), `player_id` INT,`impulse` INT DEFAULT 0 , `status` JSON
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `units` (
	`id` INT(2) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`faction` ENUM ('Indigenous', 'Spanish'), `reduced` BOOL DEFAULT false,
	`type` ENUM('Leader', 'Cavalry', 'Arquebusiers', 'Swordmen', 'Pawns', 'Scribes', 'Caciques', 'Naborias', 'Calinagos', 'Tamas', 'Captains', 'Troops'),
	`location` VARCHAR(20), `bag` VARCHAR(20)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `counters` (
	`id` INT(2) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`type` ENUM('turn', 'VP', 'impulseSpanish', 'impulseIndigenous', 'royalSupport', 'divineGrace', 'natureSpirits', 'palisades', 'citadels', 'shipsWear', 'attestor', 'area'),
	`location` VARCHAR(20), `status` JSON
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
