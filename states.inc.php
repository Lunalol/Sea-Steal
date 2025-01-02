<?php
//
$machinestates = [
	1 => array(
		"name" => "gameSetup",
		"description" => "",
		"type" => "manager",
		"action" => "stGameSetup",
		"transitions" => ["" => 10]
	),
	10 => [
		"name" => "setup",
		"type" => "game",
		"action" => "stSetup",
		"transitions" => ["startOfGame" => 15]
	],
	15 => [
		"name" => "startOfGame",
		"description" => clienttranslate('Opponent must trigger the arrival of the Spanish to the Caribbean Islands in 1492'),
		"descriptionmyturn" => clienttranslate('${you} must trigger the arrival of the Spanish to the Caribbean Islands in 1492'),
		"type" => "activeplayer",
		"possibleactions" => ["actStartOfGame"],
		"transitions" => ["startOfRound" => 20]
	],
	20 => [
		"name" => "startOfRound",
		"type" => "game",
		"action" => "stStartOfRound",
		"transitions" => ["fatePhase" => 100]
	],
	25 => [
		"name" => "endOfRound",
		"type" => "game",
		"action" => "stEndOfRound",
		"transitions" => ["startOfRound" => 20, "gameEnd" => 99]
	],
	99 => [
		"name" => "gameEnd",
		"description" => clienttranslate("End of game"),
		"type" => "manager",
		"action" => "stGameEnd",
		"args" => "argGameEnd"
	],
//
// SEQUENCE OF PLAY
//
	100 => [
		"name" => "fatePhase",
		"type" => "game",
		"action" => "stFatePhase",
		"transitions" => ["eventPhase" => 200]
	],
	200 => [
		"name" => "eventPhase",
		"type" => "game",
		"action" => "stEventPhase",
		"transitions" => ["secretChoice" => 210, 'recoveryPhase' => 300]
	],
	210 => [
		'name' => 'secretChoice',
		'description' => clienttranslate('Opponent has to secretly choose one card from their hand'),
		'descriptionmyturn' => clienttranslate('${you} have to secretly choose one card from your hand'),
		'type' => 'multipleactiveplayer',
		'args' => 'argSecretChoice',
		'possibleactions' => ['actSecretChoice'],
		'transitions' => ["eventResolutionPhase" => 220]
	],
	220 => [
		"name" => "eventResolutionPhase",
		"type" => "game",
		"action" => "stEventResolutionPhase",
		"transitions" => ["eventResolution" => 230, "recoveryPhase" => 300]
	],
	230 => [
		"name" => "eventResolution",
		'description' => clienttranslate('Opponent must resolve their choosen Event Card'),
		'descriptionmyturn' => clienttranslate('${you} must revolve your choosen Event Card'),
		'type' => 'activeplayer',
		'args' => 'argEventResolution',
		'possibleactions' => ['actEventResolution'],
		"transitions" => ["eventCombatPhase" => 240]
	],
	240 => [
		"name" => "eventCombatPhase",
		'description' => clienttranslate('Opponent must engage combat'),
		'descriptionmyturn' => clienttranslate('${you} must engage combat'),
		'type' => 'activeplayer',
		'args' => 'argCombatPhase',
		"action" => "stCombatPhase",
		'possibleactions' => ['actCombat'],
		"transitions" => ["combat" => 1000, "continue" => 220]
	],
	300 => [
		"name" => "recoveryPhase",
		"type" => "game",
		"action" => "stRecoveryPhase",
		"transitions" => ["reinforcementPhase" => 310, "impulsePhase" => 400]
	],
	310 => [
		"name" => "reinforcementPhase",
		'type' => 'game',
		"action" => "stReinforcement",
		'possibleactions' => ['actReinforcement'],
		"transitions" => ["reinforcement" => 320, "impulsePhase" => 400]
	],
	320 => [
		"name" => "reinforcement",
		'description' => clienttranslate('Opponent gets ${reinforcement} military units as reinforcement'),
		'descriptionmyturn' => clienttranslate('${you} get ${reinforcement} military units as reinforcement'),
		'type' => 'activeplayer',
		'args' => 'argReinforcement',
		'possibleactions' => ['actReinforcement'],
		"transitions" => ["continue" => 330]
	],
	330 => [
		"name" => "reinforcementCombatPhase",
		'description' => clienttranslate('Opponent must engage combat'),
		'descriptionmyturn' => clienttranslate('${you} must engage combat'),
		'type' => 'activeplayer',
		'args' => 'argCombatPhase',
		"action" => "stCombatPhase",
		'possibleactions' => ['actCombat'],
		"transitions" => ["combat" => 1000, "continue" => 310]
	],
	400 => [
		"name" => "impulsePhase",
		"type" => "game",
		"action" => "stImpulsePhase",
		"transitions" => ["startOfImpulse" => 405]
	],
	405 => [
		"name" => "startOfImpulse",
		"type" => "game",
		"action" => "stStartOfImpulse",
		"transitions" => ["action" => 410, "movementPhase" => 420, "impulseCombatPhase" => 480, "victoryCheckPhase" => 500]
	],
	410 => [
		"name" => "action",
		'description' => clienttranslate('Opponent can do an action'),
		'descriptionmyturn' => clienttranslate('${you} can do an action'),
		'type' => 'activeplayer',
		'args' => 'argAction',
		"action" => "stAction",
		'possibleactions' => ['actActivation', 'actIncursion', 'actBuildPalisades', 'actBuildCitadels', 'actPass'],
		"transitions" => ["continue" => 410, "movementPhase" => 420, "incursion" => 430, "impulseCombatPhase" => 480]
	],
	420 => [
		"name" => "movementPhase",
		'description' => clienttranslate('Opponent can move units from activated area'),
		'descriptionmyturn' => clienttranslate('${you} can move units from activated area'),
		'type' => 'activeplayer',
		'args' => 'argMovementPhase',
		'possibleactions' => ['actScribe', 'actMovementPhase'],
		"transitions" => ["continue" => 420, "impulseCombatPhase" => 480]
	],
	430 => [
		"name" => "incursion",
		'description' => clienttranslate('Incursion'),
		"type" => "game",
		"action" => "stIncursion",
		"transitions" => ["divineGraceNatureSpirits" => 435, "incursionContinue" => 450, "incursionResolve" => 440, 'continue' => 490]
	],
	435 => [
		"name" => "divineGraceNatureSpirits",
		'description' => clienttranslate('Opponent can use Divine Grace / Nature Spirits'),
		'descriptionmyturn' => clienttranslate('${you} can use Divine Grace / Nature Spirits'),
		'type' => 'activeplayer',
		'args' => 'argDivineGraceNatureSpirits',
		'possibleactions' => ['actDivineGraceNatureSpirits'],
		"transitions" => ["continue" => 440]
	],
	440 => [
		"name" => "incursionResolve",
		'description' => clienttranslate('Incursion'),
		"type" => "game",
		"action" => "stIncursionResolve",
		"transitions" => ["incursionInjuries" => 445, "continue" => 490]
	],
	445 => [
		"name" => "incursionInjuries",
		'description' => clienttranslate('Opponent must apply first incursion injuries'),
		'descriptionmyturn' => clienttranslate('${you} must apply first incursion injuries'),
		'type' => 'activeplayer',
		'args' => 'argIncursionInjuries',
		'possibleactions' => ['actIncursionInjuries'],
		"transitions" => ["incursion" => 430, "continue" => 490]
	],
	450 => [
		"name" => "incursionContinue",
		'description' => clienttranslate('Opponent must chooce to attempt a second incursion or not'),
		'descriptionmyturn' => clienttranslate('${you} must chooce to attempt a second incursion or not'),
		'type' => 'activeplayer',
		'possibleactions' => ['actIncursionContinue'],
		"transitions" => ["incursion" => 430]
	],
	480 => [
		"name" => "impulseCombatPhase",
		'description' => clienttranslate('Opponent must engage combat'),
		'descriptionmyturn' => clienttranslate('${you} must engage combat'),
		'type' => 'activeplayer',
		'args' => 'argCombatPhase',
		"action" => "stCombatPhase",
		'possibleactions' => ['actCombat'],
		"transitions" => ["combat" => 1000, "continue" => 490]
	],
	490 => [
		"name" => "endOfimpulse",
		"type" => "game",
		"action" => "stEndOfimpulse",
		"transitions" => ["startOfImpulse" => 405, "victoryCheckPhase" => 500]
	],
	500 => [
		"name" => "victoryCheckPhase",
		"type" => "game",
		"action" => "stVictoryCheckPhase",
		"transitions" => ["endOfRound" => 25]
	],
//
// COMBAT
//
	1000 => [
		'name' => 'combatSelectUnits',
		'description' => clienttranslate('Opponent has to choose units engaged in the combat'),
		'descriptionmyturn' => clienttranslate('${you} have to choose units engaged in the combat'),
		'type' => 'multipleactiveplayer',
		'args' => 'argCombatSelectUnits',
		"action" => "stCombatSelectUnits",
		'possibleactions' => ['actCombatSelectUnits'],
		'transitions' => ['combatRolls' => 1010, 'endOfCombat' => 1050]
	],
	1010 => [
		"name" => "combatRolls",
		"type" => "game",
		"action" => "stCombatRolls",
		"transitions" => ["divineGraceNatureSpirits" => 1015, "combatResolve" => 1020, 'combatRetreat' => 1040]
	],
	1015 => [
		"name" => "divineGraceNatureSpirits",
		'description' => clienttranslate('Opponent can use Divine Grace / Nature Spirits'),
		'descriptionmyturn' => clienttranslate('${you} can use Divine Grace / Nature Spirits'),
		'type' => 'activeplayer',
		'args' => 'argDivineGraceNatureSpirits',
		'possibleactions' => ['actDivineGraceNatureSpirits'],
		"transitions" => ["continue" => 1020]
	],
	1020 => [
		"name" => "combatResolve",
		"type" => "game",
		"action" => "stCombatResolve",
		"transitions" => ["divineGraceNatureSpirits" => 1015, "combatHits" => 1030, 'combatRetreat' => 1040]
	],
	1030 => [
		'name' => 'combatHits',
		'description' => clienttranslate('Opponent has to inflict combat hits'),
		'descriptionmyturn' => clienttranslate('${you} have to inflict combat hits'),
		'type' => 'multipleactiveplayer',
		'args' => 'argCombatHits',
		"action" => "stCombatHits",
		'possibleactions' => ['actCombatHits'],
		'transitions' => ['combatRolls' => 1010]
	],
	1040 => [
		'name' => 'combatRetreat',
		'description' => clienttranslate('Opponent has the opportunity to retreat'),
		'descriptionmyturn' => clienttranslate('${you} have the opportunity to retreat'),
		'type' => 'multipleactiveplayer',
		'args' => 'argCombatDefenderRetreat',
		"action" => "stCombatDefenderRetreat",
		'possibleactions' => ['actRetreat'],
		'transitions' => ['continue' => 1045]
	],
	1045 => [
		'name' => 'combatRetreat',
		'description' => clienttranslate('Opponent has the opportunity to retreat'),
		'descriptionmyturn' => clienttranslate('${you} have the opportunity to retreat'),
		'type' => 'multipleactiveplayer',
		'args' => 'argCombatAttackerRetreat',
		"action" => "stCombatAttackerRetreat",
		'possibleactions' => ['actRetreat'],
		'transitions' => ['continue' => 1000]
	],
	1050 => [
		"name" => "endOfCombat",
		"type" => "game",
		"action" => "stEndOfCombat",
		"transitions" => ["divineGraceNatureSpirits" => 1055, "goldSearch" => 1060, "eventCombatPhase" => 240, "reinforcementCombatPhase" => 330, "impulseCombatPhase" => 480]
	],
	1055 => [
		"name" => "divineGraceNatureSpirits",
		'description' => clienttranslate('Opponent can use Divine Grace / Nature Spirits'),
		'descriptionmyturn' => clienttranslate('${you} can use Divine Grace / Nature Spirits'),
		'type' => 'activeplayer',
		'args' => 'argDivineGraceNatureSpirits',
		'possibleactions' => ['actDivineGraceNatureSpirits'],
		"transitions" => ["continue" => 1060]
	],
	1060 => [
		"name" => "goldSearch",
		"type" => "game",
		"action" => "stGoldSearch",
		"transitions" => ["eventCombatPhase" => 240, "reinforcementCombatPhase" => 330, "impulseCombatPhase" => 480]
	],
];
