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
		"transitions" => ["startOfGame" => 20]
	],
	20 => [
		"name" => "startOfGame",
		"description" => clienttranslate('${actplayer} must trigger the arrival of the Spanish to the Caribbean Islands in 1492'),
		"descriptionmyturn" => clienttranslate('${you} must trigger the arrival of the Spanish to the Caribbean Islands in 1492'),
		"type" => "activeplayer",
		"possibleactions" => ["continue"],
		"transitions" => ["continue" => 30]
	],
	30 => [
		"name" => "startOfGame",
		"action" => "stStartOfGame",
		"transitions" => ["continue" => 40]
	],
	40 => [
		"name" => "startOfGame",
		"description" => clienttranslate('${actplayer} must play a card or pass'),
		"descriptionmyturn" => clienttranslate('${you} must play a card or pass'),
		"type" => "activeplayer",
		"possibleactions" => ["actPlayCard", "actPass"],
		"transitions" => ["playCard" => 3, "pass" => 3]
	],
	99 => [
		"name" => "gameEnd",
		"description" => clienttranslate("End of game"),
		"type" => "manager",
		"action" => "stGameEnd",
		"args" => "argGameEnd"
	],
];

