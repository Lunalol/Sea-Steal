<?php
//
$this->CARDS = [
//
	1 => [7, 'title' => clienttranslate('ALONSO PINZÓN’S DESERTION'), clienttranslate('+4 Rebels'), clienttranslate('Place in any area not controlled by Spanish units. Otherwise, place in areas with the least number of Spanish units.')],
	2 => [5, 'title' => clienttranslate('THE ATTACK OF CAONABÓ'), clienttranslate('+4 Caribes +1 Taino + Caonabó'), clienttranslate('Place in any area controlled by Spanish units. If game turn 2, do not place where Columbus is located. Proceed with a non-simultaneous combat.')],
	3 => [1, 'title' => clienttranslate('COLUMBUS’S PEARLS'), clienttranslate('Roll 1 die'), clienttranslate('(1) No effect; (2-4) -2 Royal Support; (5-6) -3 Royal Support and -2 Soldiers (Choose between the closest soldier units to Columbus, otherwise, at your choice).')],
	4 => [7, 'title' => clienttranslate('ROLDAN’S REVOLT'), clienttranslate('Roll 1 die for Rebels'), clienttranslate('(1) 2 Rebels; (2-3) 3 Rebels; (4-5) 4 Rebels; (6) 5 Rebels. Place in any area controlled by Spanish units. If game turn 2, do not place where Columbus is located. Proceed with a non-simultaneous combat.')],
	5 => [5, 'title' => clienttranslate('TENSION WITH CARIBS'), clienttranslate('+4 Caribes'), clienttranslate('Place in any empty area. Otherwise, place in any area under your control. If that is not possible, place in any area with the least number of Spanish units.')],
	6 => [3, 'title' => clienttranslate('TENSION WITH TAINOS'), clienttranslate('+5 Tainos'), clienttranslate('Place in any empty area or any area under your control. If that is not possible, place in any area with the least number of Spanish units.')],
	7 => [5, 'title' => clienttranslate('OVANDO TURNS A DEAF EAR'), clienttranslate('+5 Caribes & +3 Tainos'), clienttranslate('Place at your choice between areas 1, 2 & 10. You need to place at least 1 unit in each area.')],
	8 => [3, 'title' => clienttranslate('STORMS AT SEA'), clienttranslate('Roll 1 die for each Spanish Soldier'), clienttranslate('(1-3) Replace the soldier for a random settler; (4-6) No effect.')],
	9 => [7, 'title' => clienttranslate('COLUMBUS’ SECOND VOYAGE'), clienttranslate(''), clienttranslate('')],
	10 => [1, 'title' => clienttranslate('GUACANAGARÍ’S SUPPORT'), clienttranslate(''), clienttranslate('')],
	11 => [4, 'title' => clienttranslate('BARTHOLOMEW COLUMBUS'), clienttranslate(''), clienttranslate('')],
	12 => [6, 'title' => clienttranslate('SMALLPOC EPIDEMIC'), clienttranslate(''), clienttranslate('')],
	13 => [4, 'title' => clienttranslate('DIEGO MÉNDEZ'), clienttranslate(''), clienttranslate('')],
	14 => [6, 'title' => clienttranslate('COLUMBUS’ THIRD VOYAGE'), clienttranslate(''), clienttranslate('')],
	15 => [4, 'title' => clienttranslate('FRANCISCO DE BOBADILLA'), clienttranslate(''), clienttranslate('')],
	16 => [2, 'title' => clienttranslate('COLUMBUS’ FOURTH VOYAGE'), clienttranslate(''), clienttranslate('')],
//
	17 => [DIVINEGRACE, 'goldSearch' => [1, 1, 1, 1, -1, -1], 'landingArea' => [15, 15, 15, 15, 11, 8]],
	18 => [NATURESPIRITS | NAVALDIFFICULTIES | INDIGENOUSINTERNALCONFLIT, 'goldSearch' => [1, 1, 1, 0, -1, -1], 'landingArea' => [15, 15, 15, 11, 11, 8], 'title' => clienttranslate('')],
	19 => [DIVINEGRACE, 'goldSearch' => [1, 1, 1, 1, 0, -1], 'landingArea' => [15, 15, 15, 11, 8, 8], 'title' => clienttranslate('')],
//
	20 => [NAVALDIFFICULTIES | INDIGENOUSINTERNALCONFLIT, 2, 'goldSearch' => [1, 1, 0, 0, -1, -1], 'title' => clienttranslate('MAJOR EARTHQUAKE')],
	21 => [NAVALDIFFICULTIES | INDIGENOUSINTERNALCONFLIT, 3, 'goldSearch' => [1, 0, 0, 0, -1, -1], 'title' => clienttranslate('EARTHQUAKE')],
	22 => [0, 3, 'goldSearch' => [1, 1, 1, 0, -1, -1], 'title' => clienttranslate('THE END OF THE WORLD')],
	23 => [0, 3, 'goldSearch' => [1, 1, 0, 0, 0, -1], 'title' => clienttranslate('NOBILITY UNWILLING TO WORK')],
	24 => [NAVALDIFFICULTIES | INDIGENOUSINTERNALCONFLIT, 3, 'goldSearch' => [1, 1, 1, 0, 0, -1], 'title' => clienttranslate('SHELLS ON THE HULL')],
	25 => [0, 5, 'goldSearch' => [1, 1, 1, 0, -1, -1], 'title' => clienttranslate('ANACAONA TAINO PRINCESS')],
	26 => [NAVALDIFFICULTIES | INDIGENOUSINTERNALCONFLIT, 4, 'goldSearch' => [1, 1, 1, 1, 0, -1], 'title' => clienttranslate('THE SONS OF CAONABÓ')],
	27 => [NAVALDIFFICULTIES | INDIGENOUSINTERNALCONFLIT, 2, 'goldSearch' => [1, 1, 1, 1, 0, -1], 'title' => clienttranslate('RELIGIOUS CONVERSIONS')],
	28 => [0, 3, 'goldSearch' => [1, 1, 1, 1, -1, -1], 'title' => clienttranslate('COLUMBUS’ NEGOTIATIONS')],
	29 => [NAVALDIFFICULTIES | INDIGENOUSINTERNALCONFLIT, 3, 'goldSearch' => [1, 1, 1, 1, 0, -1], 'title' => clienttranslate('EARTHLY PARADISE')],
	30 => [NAVALDIFFICULTIES | INDIGENOUSINTERNALCONFLIT, 4, 'goldSearch' => [1, 1, 1, 1, -1, -1], 'title' => clienttranslate('ARE WE IN CATHAY?')],
	31 => [0, 3, 'goldSearch' => [1, 1, 1, 1, -1, -1], 'title' => clienttranslate('THE GOLD OF CIBAO')],
	32 => [0, 2, 'goldSearch' => [1, 1, 1, 0, 0, -1], 'title' => clienttranslate('THE LEGEND OF JARAGUA')],
	33 => [0, 4, 'goldSearch' => [1, 1, 0, 0, 0, -1], 'title' => clienttranslate('CRUELTY')],
	34 => [NAVALDIFFICULTIES | INDIGENOUSINTERNALCONFLIT, 2, 'goldSearch' => [1, 0, 0, 0, -1, -1], 'title' => clienttranslate('MAINLAND')],
	35 => [NAVALDIFFICULTIES | INDIGENOUSINTERNALCONFLIT, 4, 'goldSearch' => [1, 1, 1, 0, -1, -1], 'title' => clienttranslate('ISLAND OR CONTINENT?')],
	36 => [NAVALDIFFICULTIES | INDIGENOUSINTERNALCONFLIT, 5, 'goldSearch' => [1, 1, 0, 0, -1, -1], 'title' => clienttranslate('TROPICAL CLIMATOLOGY')],
	37 => [0, 3, 'goldSearch' => [1, 1, 1, 0, -1, -1], 'title' => clienttranslate('COLUMBUS’S HEALTH')],
	38 => [0, 3, 'goldSearch' => [1, 1, 1, 0, -1, -1], 'title' => clienttranslate('INTRIGUES AGAINST COLUMBUS')],
	39 => [NAVALDIFFICULTIES | INDIGENOUSINTERNALCONFLIT, 3, 'goldSearch' => [1, 1, 1, 0, -1, -1], 'title' => clienttranslate('LUNAR ECLIPSE')],
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
