<?php

/**
 *
 * @author Lunalol
 */
trait gameStateArguments
{
	function argSecretChoice()
	{
		$this->possible = [];
		foreach (Factions::getAlldatas() as $faction => $player_id) $this->possible[$player_id][] = $faction;
//
		return ['_private' => $this->possible];
	}
	function argEventResolution()
	{
		$faction = $this->globals->get('faction');
		$card = $this->globals->get("event/$faction");
//
		return $this->possible = [
			'faction' => $faction, 'card' => $card, 'overStacking' => $this->globals->get('overStacking'),
			'units' => Units::getAtLocation('event'), 'event' => self::eventLocations($card)
		];
	}
	function argReinforcement()
	{
		$faction = $this->globals->get('faction');
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
		$faction = $this->globals->get('faction');
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
		$faction = $this->globals->get('faction');
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
		$location = $from;
		if (!(($attempts === 1 && $roll == 6) || ($attempts === 3 && $roll >= 4)))
		{
			$location = $to;
			$faction = Factions::other($faction);
		}
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
		return $this->possible = ['faction' => $this->globals->get('faction'), 'navalDifficulties' => $this->globals->get('navalDifficulties'), 'location' => $this->globals->get('activeArea')];
	}
	function argCombatPhase()
	{
		return $this->possible = ['faction' => $this->globals->get('faction'), 'locations' => Units::getCombatLocations()];
	}
	function argCombatSelectUnits()
	{
		$location = $this->globals->get('location');
//
		$this->possible = [];
		foreach (Factions::getAlldatas() as $faction => $player_id)
		{
			$units = Units::getAtLocation($location, $faction);
			$this->possible[$player_id][$faction] = ['units' => sizeof($units) > 3 ? array_values($units) : []];
		}

//
		return ['location' => $location, '_private' => $this->possible];
	}
	function argCombatHits()
	{
		$location = $this->globals->get('location');
//
		$hits = $this->globals->get('hits');
//
		$this->possible = [];
		foreach (Factions::getAlldatas() as $faction => $player_id) if ($hits[$faction] !== 'done' && $hits[$faction] > 0) $this->possible[$player_id][$faction] = ['units' => array_map('Units::get', $this->globals->get("combatUnits/$faction")), 'hits' => $hits[$faction]];
//
		return ['location' => $location, '_private' => $this->possible];
	}
	function argCombatDefenderRetreat()
	{
		$location = $this->globals->get('location');
//
		$faction = $this->globals->get('defender');
		return ['faction' => $faction, 'location' => $location, 'navalDifficulties' => $this->globals->get('navalDifficulties'), 'units' => array_map('Units::get', $this->globals->get("combatUnits/$faction"))];
	}
	function argCombatAttackerRetreat()
	{
		$location = $this->globals->get('location');
//
		$faction = $this->globals->get('attacker');
		return ['faction' => $faction, 'location' => $location, 'navalDifficulties' => $this->globals->get('navalDifficulties'), 'units' => array_map('Units::get', $this->globals->get("combatUnits/$faction"))];
	}
	function argDivineGraceNatureSpirits()
	{
		return ['faction' => $this->globals->get('faction'), 'dice' => $this->globals->get('dice')];
	}
}
