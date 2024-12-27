<?php
//
$this->CARDS = [
//
	1 => ['recoveryValue' => 7, 'title' => clienttranslate('ALONSO PINZÓN’S DESERTION'), clienttranslate('+4 Rebels'), clienttranslate('Place in any area not controlled by Spanish units. Otherwise, place in areas with the least number of Spanish units.')],
	2 => ['recoveryValue' => 5, 'title' => clienttranslate('THE ATTACK OF CAONABÓ'), clienttranslate('+4 Caribes +1 Taino + Caonabó'), clienttranslate('Place in any area controlled by Spanish units. If game turn 2, do not place where Columbus is located. Proceed with a non-simultaneous combat.')],
	3 => ['recoveryValue' => 1, 'title' => clienttranslate('COLUMBUS’S PEARLS'), clienttranslate('Roll 1 die'), clienttranslate('(1) No effect; (2-4) -2 Royal Support; (5-6) -3 Royal Support and -2 Soldiers (Choose between the closest soldier units to Columbus, otherwise, at your choice).')],
	4 => ['recoveryValue' => 7, 'title' => clienttranslate('ROLDAN’S REVOLT'), clienttranslate('Roll 1 die for Rebels'), clienttranslate('(1) 2 Rebels; (2-3) 3 Rebels; (4-5) 4 Rebels; (6) 5 Rebels. Place in any area controlled by Spanish units. If game turn 2, do not place where Columbus is located. Proceed with a non-simultaneous combat.')],
	5 => ['recoveryValue' => 5, 'title' => clienttranslate('TENSION WITH CARIBS'), clienttranslate('+4 Caribes'), clienttranslate('Place in any empty area. Otherwise, place in any area under your control. If that is not possible, place in any area with the least number of Spanish units.')],
	6 => ['recoveryValue' => 3, 'title' => clienttranslate('TENSION WITH TAINOS'), clienttranslate('+5 Tainos'), clienttranslate('Place in any empty area or any area under your control. If that is not possible, place in any area with the least number of Spanish units.')],
	7 => ['recoveryValue' => 5, 'title' => clienttranslate('OVANDO TURNS A DEAF EAR'), clienttranslate('+5 Caribes & +3 Tainos'), clienttranslate('Place at your choice between areas 1, 2 & 10. You need to place at least 1 unit in each area.')],
	8 => ['recoveryValue' => 3, 'title' => clienttranslate('STORMS AT SEA'), clienttranslate('Roll 1 die for each Spanish Soldier'), clienttranslate('(1-3) Replace the soldier for a random settler; (4-6) No effect.')],
	9 => ['recoveryValue' => 7, 'title' => clienttranslate('COLUMBUS’ SECOND VOYAGE'), clienttranslate('+7 Soldiers* & +6 Settlers'), clienttranslate('Place in the same area where Columbus is located. If Columbus is not in any area, place freely between areas 5, 6 & 15.')],
	10 => ['recoveryValue' => 1, 'title' => clienttranslate('GUACANAGARÍ’S SUPPORT'), clienttranslate('-3 Tainos, +3 Settlers + Guacanagarí'), clienttranslate('Remove up to 3 Tainos in one area and place Guacaganarí on its Spanish side +3 Settlers. Roll a die at the end of each turn: (6) Flip Guacaganarí and move him to the most crowded Indigenous controlled area without restrictions.')],
	11 => ['recoveryValue' => 4, 'title' => clienttranslate('BARTHOLOMEW COLUMBUS'), clienttranslate('+3 Soldiers* & +2 Settlers'), clienttranslate('Place in any Spanish controlled area.')],
	12 => ['recoveryValue' => 6, 'title' => clienttranslate('SMALLPOC EPIDEMIC'), clienttranslate('Roll 1 die per each Taino & Carib unit'), clienttranslate('(1-3) Eliminate unit; (4-6) No effect. If target units are 2 or more areas from any Spanish controlled area or Rebel units, apply a die roll modifier of +1. No effect on Rebel units nor Leaders.')],
	13 => ['recoveryValue' => 4, 'title' => clienttranslate('DIEGO MÉNDEZ'), clienttranslate('+3 Soldiers* & +3 Settlers'), clienttranslate('Arrival to Santiago Island. Place in area 10.')],
	14 => ['recoveryValue' => 6, 'title' => clienttranslate('COLUMBUS’ THIRD VOYAGE'), clienttranslate('+6 Soldiers* & +3 Settlers'), clienttranslate('Place in the same area where Columbus is located. If Columbus is not on any area, place freely between areas 5 & 6. If Columbus is in the “Prison of Spain” box, move it to the chosen area.')],
	15 => ['recoveryValue' => 4, 'title' => clienttranslate('FRANCISCO DE BOBADILLA'), clienttranslate('+4 Soldiers* & +3 Settlers'), clienttranslate('Place in any Spanish controlled area. In addition, place Columbus in the “Prison in Spain” box if he hasn’t been eliminated yet.')],
	16 => ['recoveryValue' => 2, 'title' => clienttranslate('COLUMBUS’ FOURTH VOYAGE'), clienttranslate('+2 Soldiers* & +4 Settlers'), clienttranslate('Place in an empty area or with the least number of enemy units. If Columbus is in the “Prison in Spain” box, move him to the chosen area.')],
//
	17 => ['title' => clienttranslate('LANDFALL IN THE CARIBBEAN'), clienttranslate('Roll 1 die for the landing area: (1-4) Area 15; (5) Area 11; (6) Area 8. Resolve landing combat. In addition, activate the landing area once (optional).'), clienttranslate('Turn 1 (1942)'), 'flags' => DIVINEGRACE, 'goldSearch' => [1, 1, 1, 1, -1, -1], 'landingArea' => [15, 15, 15, 15, 11, 8]],
	18 => ['title' => clienttranslate('LANDFALL IN THE CARIBBEAN'), clienttranslate('Roll 1 die for the landing area: (1-3) Area 15; (4-5) Area 11; (6) Area 8. Resolve landing combat. In addition, activate the landing area once (optional).'), clienttranslate('Turn 1 (1942)'), 'flags' => NATURESPIRITS | NAVALDIFFICULTIES | INDIGENOUSINTERNALCONFLIT, 'goldSearch' => [1, 1, 1, 0, -1, -1], 'landingArea' => [15, 15, 15, 11, 11, 8], 'title' => clienttranslate('')],
	19 => ['title' => clienttranslate('LANDFALL IN THE CARIBBEAN'), clienttranslate('Roll 1 die for the landing area: (1-3) Area 15; (4) Area 11; (5-6) Area 8. Resolve landing combat. In addition, activate the landing area once (optional).'), clienttranslate('Turn 1 (1942)'), 'flags' => DIVINEGRACE, 'goldSearch' => [1, 1, 1, 1, 0, -1], 'landingArea' => [15, 15, 15, 11, 8, 8], 'title' => clienttranslate('')],
//
	20 => ['title' => clienttranslate('MAJOR EARTHQUAKE'), clienttranslate(''), clienttranslate(''), 'flags' => NAVALDIFFICULTIES | INDIGENOUSINTERNALCONFLIT, 'impulse' => 2, 'goldSearch' => [1, 1, 0, 0, -1, -1], 'title' => clienttranslate('MAJOR EARTHQUAKE')],
	21 => ['title' => clienttranslate('EARTHQUAKE'), clienttranslate(''), clienttranslate(''), 'flags' => NAVALDIFFICULTIES | INDIGENOUSINTERNALCONFLIT, 'impulse' => 3, 'goldSearch' => [1, 0, 0, 0, -1, -1], 'title' => clienttranslate('EARTHQUAKE')],
	22 => ['title' => clienttranslate('THE END OF THE WORLD'), clienttranslate(''), clienttranslate(''), 'flags' => 0, 'impulse' => 3, 'goldSearch' => [1, 1, 1, 0, -1, -1], 'title' => clienttranslate('THE END OF THE WORLD')],
	23 => ['title' => clienttranslate('NOBILITY UNWILLING TO WORK'), clienttranslate(''), clienttranslate(''), 'flags' => 0, 'impulse' => 3, 'goldSearch' => [1, 1, 0, 0, 0, -1], 'title' => clienttranslate('NOBILITY UNWILLING TO WORK')],
	24 => ['title' => clienttranslate(''), clienttranslate(''), clienttranslate(''), 'flags' => NAVALDIFFICULTIES | INDIGENOUSINTERNALCONFLIT, 'impulse' => 3, 'goldSearch' => [1, 1, 1, 0, 0, -1], 'title' => clienttranslate('SHELLS ON THE HULL')],
	25 => ['title' => clienttranslate(''), clienttranslate(''), clienttranslate(''), 'flags' => 0, 'impulse' => 5, 'goldSearch' => [1, 1, 1, 0, -1, -1], 'title' => clienttranslate('ANACAONA TAINO PRINCESS')],
	26 => ['title' => clienttranslate(''), clienttranslate(''), clienttranslate(''), 'flags' => NAVALDIFFICULTIES | INDIGENOUSINTERNALCONFLIT, 'impulse' => 4, 'goldSearch' => [1, 1, 1, 1, 0, -1], 'title' => clienttranslate('THE SONS OF CAONABÓ')],
	27 => ['title' => clienttranslate('RELIGIOUS CONVERSIONS'), clienttranslate(''), clienttranslate(''), 'flags' => NAVALDIFFICULTIES | INDIGENOUSINTERNALCONFLIT, 'impulse' => 2, 'goldSearch' => [1, 1, 1, 1, 0, -1], 'title' => clienttranslate('RELIGIOUS CONVERSIONS')],
	28 => ['title' => clienttranslate(''), clienttranslate(''), clienttranslate(''), 'flags' => 0, 'impulse' => 3, 'goldSearch' => [1, 1, 1, 1, -1, -1], 'title' => clienttranslate('COLUMBUS’ NEGOTIATIONS')],
	29 => ['title' => clienttranslate(''), clienttranslate(''), clienttranslate(''), 'flags' => NAVALDIFFICULTIES | INDIGENOUSINTERNALCONFLIT, 'impulse' => 3, 'goldSearch' => [1, 1, 1, 1, 0, -1], 'title' => clienttranslate('EARTHLY PARADISE')],
	30 => ['title' => clienttranslate(''), clienttranslate(''), clienttranslate(''), 'flags' => NAVALDIFFICULTIES | INDIGENOUSINTERNALCONFLIT, 'impulse' => 4, 'goldSearch' => [1, 1, 1, 1, -1, -1], 'title' => clienttranslate('ARE WE IN CATHAY?')],
	31 => ['title' => clienttranslate(''), clienttranslate(''), clienttranslate(''), 'flags' => 0, 'impulse' => 3, 'goldSearch' => [1, 1, 1, 1, -1, -1], 'title' => clienttranslate('THE GOLD OF CIBAO')],
	32 => ['title' => clienttranslate(''), clienttranslate(''), clienttranslate(''), 'flags' => 0, 'impulse' => 2, 'goldSearch' => [1, 1, 1, 0, 0, -1], 'title' => clienttranslate('THE LEGEND OF JARAGUA')],
	33 => ['title' => clienttranslate(''), clienttranslate(''), clienttranslate(''), 'flags' => 0, 'impulse' => 4, 'goldSearch' => [1, 1, 0, 0, 0, -1], 'title' => clienttranslate('CRUELTY')],
	34 => ['title' => clienttranslate(''), clienttranslate(''), clienttranslate(''), 'flags' => NAVALDIFFICULTIES | INDIGENOUSINTERNALCONFLIT, 'impulse' => 2, 'goldSearch' => [1, 0, 0, 0, -1, -1], 'title' => clienttranslate('MAINLAND')],
	35 => ['title' => clienttranslate(''), clienttranslate(''), clienttranslate(''), 'flags' => NAVALDIFFICULTIES | INDIGENOUSINTERNALCONFLIT, 'impulse' => 4, 'goldSearch' => [1, 1, 1, 0, -1, -1], 'title' => clienttranslate('ISLAND OR CONTINENT?')],
	36 => ['title' => clienttranslate(''), clienttranslate(''), clienttranslate(''), 'flags' => NAVALDIFFICULTIES | INDIGENOUSINTERNALCONFLIT, 'impulse' => 5, 'goldSearch' => [1, 1, 0, 0, -1, -1], 'title' => clienttranslate('TROPICAL CLIMATOLOGY')],
	37 => ['title' => clienttranslate(''), clienttranslate(''), clienttranslate(''), 'flags' => 0, 'impulse' => 3, 'goldSearch' => [1, 1, 1, 0, -1, -1], 'title' => clienttranslate('COLUMBUS’S HEALTH')],
	38 => ['title' => clienttranslate(''), clienttranslate(''), clienttranslate(''), 'flags' => 0, 'impulse' => 3, 'goldSearch' => [1, 1, 1, 0, -1, -1], 'title' => clienttranslate('INTRIGUES AGAINST COLUMBUS')],
	39 => ['title' => clienttranslate(''), clienttranslate(''), clienttranslate(''), 'flags' => NAVALDIFFICULTIES | INDIGENOUSINTERNALCONFLIT, 'impulse' => 3, 'goldSearch' => [1, 1, 1, 0, -1, -1], 'title' => clienttranslate('LUNAR ECLIPSE')],
//
];
//
$this->LOCATIONS = [
	1 => clienttranslate('MANTAZAS'),
	2 => clienttranslate('LA HABANA'),
	3 => clienttranslate('PUERTO PRINCIPE'),
	4 => clienttranslate('HOLGUIN'),
	5 => clienttranslate('MOE'),
	6 => clienttranslate('SAN FELIPE'),
	7 => clienttranslate('LA VEGA'),
	8 => clienttranslate('SANTO DOMINGO'),
	9 => clienttranslate('SAN JUAN'),
	10 => clienttranslate('ISLA DE SANTIAGO'),
	11 => clienttranslate('BARBUDA'),
	12 => clienttranslate('MARTINICA'),
	13 => clienttranslate('LUCAYAS OCCIDENTALES'),
	14 => clienttranslate('LUCAYAS ORIENTALES'),
	15 => clienttranslate('SAN SALVADOR'),
];
//
$this->UNITS = [
	Factions::INDIGENOUS => [
		'Leader' => [4, 4, 4],
		'Caciques' => [2, 2, 1],
		'Naborias' => [1, 2, 1],
		'Calinagos' => [3, 3, 2],
		'Tamas' => [3, 2, 2],
		'Captains' => [4, 4, 3],
		'Troops' => [4, 3, 3],
	],
	Factions::SPANISH => [
		'Leader' => [5, 5, 5],
		'Cavalry' => [5, 4, 4],
		'Arquebusiers' => [4, 5, 4],
		'Swordmen' => [4, 4, 4],
		'Pawns' => [1, 2, 1],
		'Scribes' => [2, 1, 1],
	]
];
