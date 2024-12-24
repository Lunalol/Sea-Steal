<?php

/**
 *
 * @author Lunalol
 */
trait gameStateArguments
{
	function argEventResolution()
	{
		$faction = Factions::getFaction($player_id = intval($this->getActivePlayerId()));
//
		$card = $this->globals->get("event/$faction");
//
		return $this->possible = [
			'faction' => $faction, 'card' => $card, 'overStacking' => $this->globals->get('overStacking'),
			'units' => Units::getAtLocation('event'), 'event' => self::eventLocations($card)
		];
	}
	function argReinforcement()
	{
		$faction = Factions::getFaction($player_id = intval($this->getActivePlayerId()));
//
		$bags = [Factions::INDIGENOUS => ['green', 'blue', 'white'], Factions::SPANISH => ['yellow', 'red']][$faction];
		$units = Units::getAtLocation('event');
//
		return $this->possible = [
			'faction' => $faction,
			'bags' => $units ? [] : $bags, 'reinforcement' => $this->globals->get("reinforcement/$faction"),
			'units' => $units, 'locations' => Units::reinforcement($faction),
		];
	}
	function argAction()
	{
		$faction = Factions::getFaction($player_id = intval($this->getActivePlayerId()));
//
		$palisades = $faction === Factions::SPANISH ? self::getObjectListFromDB("SELECT DISTINCT location FROM units WHERE"
				. " faction = '$faction' AND"
				. " location REGEXP '^[0-9]+$' AND"
				. " location NOT IN (3,4,5,7) AND"
				. " location NOT IN (SELECT DISTINCT location FROM counters WHERE type IN ('palisades', 'citadels'))", true) : null;
//
		$citadels = $faction === Factions::SPANISH ? self::getObjectListFromDB("SELECT DISTINCT location FROM units WHERE"
				. " faction = '$faction' AND"
				. " location IN (SELECT DISTINCT location FROM counters WHERE type = 'palisades') AND"
				. " location IN (SELECT DISTINCT location FROM units WHERE type = 'Pawns') AND"
				. " location NOT IN (SELECT DISTINCT location FROM counters WHERE type = 'citadels')", true) : null;
//
		return $this->possible = ['faction' => $faction, 'locations' => Units::getAreas($faction),
			'navalDifficulties' => $this->globals->get('navalDifficulties'),
			'palisades' => $palisades, 'citadels' => $citadels
		];
	}
	function argIncursionInjuries()
	{
		$faction = Factions::getFaction($player_id = intval($this->getActivePlayerId()));
//
		['from' => $from, 'to' => $to, 'attempts' => $attempts] = $this->globals->get('incursion');
//
		$modifier = 0;
		if (Counters::getAtLocation($to, 'citadels')) $modifier += 1;
		if (in_array($to, [3, 4, 5, 7]))
		{
			foreach (Units::getAtLocation($from, $faction) as $unit)
			{
				if (in_array($unit['bag'], ['green', 'blue']))
				{
					$modifier -= 1;
					break;
				}
			}
		}
		$roll = min(max(1, $this->globals->get('dice')[0] + $modifier), 6);
//
		$location = $to;
		if ($attempts === 1 && $roll == 6) $location = $from;
		if ($attempts === 3 && $roll >= 4) $location = $from;
//
		$units = Units::getAtLocation($location);
//
		$strength = 0;
		foreach (array_filter($units, fn($unit) => $unit['type'] !== 'Leader') as $unit) $strength = max($this->UNITS[$faction][$unit['type']][$unit['reduced'] ? COMBINED : ATTACK], $strength);
		if ($strength > 0) $units = array_filter($units, fn($unit) => $unit['type'] !== 'Leader' && $this->UNITS[$faction][$unit['type']][$unit['reduced'] ? COMBINED : ATTACK] === $strength);
//
		return $this->possible = ['faction' => $faction, 'location' => $location, 'units' => $units, 'attacker' => $location === $from,
			'hits' => [1 => [1 => 2, 2 => 2, 3 => 1, 4 => 0, 5 => 0, 6 => 1], 3 => [1 => 2, 2 => 2, 3 => 1, 4 => 1, 5 => 1, 6 => 2]][$attempts][$roll]
		];
	}
	function argMovementPhase()
	{
		$faction = Factions::getFaction($player_id = intval($this->getActivePlayerId()));
//
		$location = $this->globals->get('activeArea');
//
		return $this->possible = ['faction' => $faction, 'navalDifficulties' => $this->globals->get('navalDifficulties'), 'location' => $location];
	}
	function argCombatPhase()
	{
		$faction = Factions::getFaction($player_id = intval($this->getActivePlayerId()));
//
		return $this->possible = ['faction' => $faction, 'locations' => Units::getCombatLocations()];
	}
	function argCombatSelectUnits()
	{
		$location = $this->globals->get('location');
//
		$attacker = $this->globals->get('attacker');
		$defender = $this->globals->get('defender');
//
		return ['location' => $location, '_private' => $this->possible = [
			Factions::getPlayer($attacker) => ['faction' => $attacker, 'units' => Units::getAtLocation($location, $attacker)],
			Factions::getPlayer($defender) => ['faction' => $defender, 'units' => Units::getAtLocation($location, $defender)],
		]];
	}
	function argCombatHits()
	{
		$location = $this->globals->get('location');
//
		$attacker = $this->globals->get('attacker');
		$defender = $this->globals->get('defender');
//
		$hits = $this->globals->get('hits');
//
		return ['location' => $location, '_private' => $this->possible = [
			Factions::getPlayer($attacker) => ['faction' => $attacker, 'units' => array_map('Units::get', $this->globals->get("combatUnits/$attacker")), 'hits' => $hits[$attacker]],
			Factions::getPlayer($defender) => ['faction' => $defender, 'units' => array_map('Units::get', $this->globals->get("combatUnits/$defender")), 'hits' => $hits[$defender]],
		]];
	}
	function argCombatRetreat()
	{
		$location = $this->globals->get('location');
//
		$attacker = $this->globals->get('attacker');
		$defender = $this->globals->get('defender');
//
		return ['location' => $location, 'navalDifficulties' => $this->globals->get('navalDifficulties'), '_private' => $this->possible = [
			Factions::getPlayer($attacker) => ['faction' => $attacker, 'units' => array_map('Units::get', $this->globals->get("combatUnits/$attacker"))],
			Factions::getPlayer($defender) => ['faction' => $defender, 'units' => array_map('Units::get', $this->globals->get("combatUnits/$defender"))],
		]];
	}
	function argDivineGraceNatureSpirits()
	{
		return $this->globals->get('dice');
	}
}
