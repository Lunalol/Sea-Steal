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
		$locations = [];
		foreach (Units::getAreas($faction) as $location)
		{
			if (Units::overstacking($location, $faction) < 3) $locations[$location] = 0;
			foreach ([(($location + 1 - 1) % 15) + 1, (($location - 1 - 1 + 15) % 15) + 1] as $adjacent)
			{
				if (!array_key_exists($adjacent, $locations) && Units::overstacking($adjacent, $faction) < 3)
				{
					if (sizeof(Units::getEnemyAtLocation($adjacent, $faction)) === 0) $locations[$adjacent] = 1;
					else $locations[$adjacent] = 2;
				}
			}
		}
//
		return $this->possible = [
			'faction' => $faction,
			'bags' => $units ? [] : $bags, 'reinforcement' => $this->globals->get("reinforcement/$faction"),
			'units' => $units, 'locations' => $locations,
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
			'palisades' => $palisades, 'citadels' => $citadels
		];
	}
	function argMovementPhase()
	{
		$faction = Factions::getFaction($player_id = intval($this->getActivePlayerId()));
//
		$location = $this->globals->get('activeArea');
//
		return $this->possible = ['faction' => $faction, 'navalDifficulties' => $this->globals->get('navalDifficulties'),
			'location' => $location, 'units' => Units::getAtLocation($location)];
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
		$locations = [$attacker => [], $defender => []];
		foreach ($this->globals->get("combatUnits/$attacker") as $id) $locations[$attacker][$id] = Units::retreat(Units::get($id));
		foreach ($this->globals->get("combatUnits/$defender") as $id) $locations[$defender][$id] = Units::retreat(Units::get($id));
//
		return ['location' => $location, 'navalDifficulties' => $this->globals->get('navalDifficulties'), '_private' => $this->possible = [
			Factions::getPlayer($attacker) => ['faction' => $attacker, 'units' => array_map('Units::get', $this->globals->get("combatUnits/$attacker")), 'locations' => $locations[$attacker]],
			Factions::getPlayer($defender) => ['faction' => $defender, 'units' => array_map('Units::get', $this->globals->get("combatUnits/$defender")), 'locations' => $locations[$defender]],
		]];
	}
}
