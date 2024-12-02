<?php

use \Bga\GameFramework\Actions\Types\IntParam;
use \Bga\GameFramework\Actions\Types\JsonParam;

/**
 *
 * @author Lunalol
 */
trait gameStateActions
{
	function actStartOfGame()
	{
		$this->gamestate->nextState('startOfRound');
	}
	function actSecretChoice(#[IntParam] int $card)
	{
		$faction = Factions::getFaction($player_id = intval(self::getCurrentPlayerId()));
//
		if (!in_array($card, Factions::getStatus($faction, 'events'))) throw new BgaVisibleSystemException("Invalid card: $card");
		$this->globals->set("event/$faction", $card);
//* -------------------------------------------------------------------------------------------------------- */
		self::notifyPlayer($player_id, 'event', '', ['event' => $card]);
//* -------------------------------------------------------------------------------------------------------- */
		if ($this->gamestate->setPlayerNonMultiactive($player_id, 'eventResolutionPhase'))
		{
//* -------------------------------------------------------------------------------------------------------- */
			self::notifyAllPlayers('msg', '${EVENTS}', ['EVENTS' => [$this->globals->get("event/" . Factions::INDIGENOUS), $this->globals->get("event/" . Factions::SPANISH)]]);
//* -------------------------------------------------------------------------------------------------------- */
		}
	}
	function actEventResolution(#[JsonParam] array $units)
	{
		$faction = Factions::getFaction($player_id = intval(self::getCurrentPlayerId()));
//
		if ($faction !== $this->possible['faction']) throw new BgaVisibleSystemException("Invalid faction: $faction");
		foreach (array_keys($units) as $unit) if (!array_key_exists($unit, $this->possible['units'])) throw new BgaVisibleSystemException("Invalid units: $unit");
//
		switch ($this->possible['event']['type'])
		{
			case 'any':
				break;
			case 'one':
				if (sizeof(array_unique($units)) !== 1) throw new BgaUserException(self::_("You need to place units	 in an unique area"));
				break;
			case 'all':
				if (array_diff($this->possible['event']['locations'], $units)) throw new BgaUserException(self::_("You need to place at least 1 unit in each area"));
				break;
			default: throw new BgaVisibleSystemException("Invalid event: " . $this->possible['event']['type']);
		}
//
		foreach ($units as $id => $location)
		{
			if (!in_array($location, $this->possible['event']['locations'])) throw new BgaVisibleSystemException("Invalid location: $location");
//
			$unit = Units::get($id);
			$unit['location'] = $location;
			Units::update($unit);
//* -------------------------------------------------------------------------------------------------------- */
			self::notifyAllPlayers('placeUnit', '', ['unit' => $unit]);
//* -------------------------------------------------------------------------------------------------------- */
			if (!$this->globals->get('overStacking') && Units::overstacking($location, $faction) > 3) throw new BgaUserException(self::_("Overstacking"));
		}
		foreach (Units::getAtLocation('event') as $unit)
		{
			if ($unit['type'] === 'Leader') throw new BgaUserException(self::_("Leader must be placed in area"));
			$unit['location'] = $unit['bag'];
			Units::update($unit);
		}
//
		$card = $this->globals->get("event/$faction");
//
		$hand = Factions::getStatus($faction, 'events');
		unset($hand[array_search($card, $hand)]);
		Factions::setStatus($faction, 'events', array_values($hand));
//* -------------------------------------------------------------------------------------------------------- */
		self::notifyPlayer($player_id, 'event', '', ['card' => $card]);
//* -------------------------------------------------------------------------------------------------------- */
		self::eventResolve($this->globals->get("event/$faction"));
//
		$this->globals->set("recoveryValue/$faction", $this->CARDS[$this->possible['card']][0]);
		$this->globals->delete("event/$faction");
//
		$this->gamestate->nextState('eventCombatPhase');
	}
	function actReinforcement(#[JsonParam] array|null $reinforcement, #[JsonParam] array|null $units)
	{
		$faction = Factions::getFaction($player_id = intval(self::getCurrentPlayerId()));
//
		if ($faction !== $this->possible['faction']) throw new BgaVisibleSystemException("Invalid faction: $faction");
		if (!array_key_exists('bags', $this->possible)) throw new BgaVisibleSystemException("Invalid possible: " . json_encode($this->possible));
		if (!array_key_exists('reinforcement', $this->possible)) throw new BgaVisibleSystemException("Invalid possible: " . json_encode($this->possible));
		if (!array_key_exists('locations', $this->possible)) throw new BgaVisibleSystemException("Invalid possible: " . json_encode($this->possible));
//
		if (!is_null($reinforcement))
		{
			if (max($reinforcement) - min($reinforcement) > 1) throw new BgaVisibleSystemException("Not uniform select: " . json_encode($reinforcement));
			if (array_sum($reinforcement) !== $this->possible['reinforcement']) throw new BgaVisibleSystemException("Invalid count: " . array_sum($reinforcement) . " <> " . $this->possible['reinforcement']);
//
			foreach ($reinforcement as $bag => $count) for ($i = 0; $i < $count; $i++) Units::draw($bag, 'event');
		}
//
		if (!is_null($units))
		{
			$priority = 0;
			foreach ($units as $id => $location)
			{
				if (!array_key_exists($location, $this->possible['locations'])) throw new BgaVisibleSystemException("Invalid location: $location");
				$priority = max($priority, $this->possible['locations'][$location]);
//
				$unit = Units::get($id);
				$unit['location'] = $location;
				Units::update($unit);
//* -------------------------------------------------------------------------------------------------------- */
				self::notifyAllPlayers('placeUnit', '', ['unit' => $unit]);
//* -------------------------------------------------------------------------------------------------------- */
				if (Units::overstacking($location, $faction) > 3) throw new BgaUserException(self::_("Overstacking"));
			}
//
			if ($priority > min(Units::reinforcement($faction))) throw new BgaUserException(self::_("Priorities not respected"));
//
			foreach (Units::getAtLocation('event') as $unit)
			{
				if ($unit['type'] === 'Leader') throw new BgaUserException(self::_("Leader must be placed in area"));
				$unit['location'] = $unit['bag'];
				Units::update($unit);
			}
//
			$this->globals->delete("reinforcement/$faction");
		}
//
		$this->gamestate->nextState('continue');
	}
	function actPass()
	{
		$faction = Factions::getFaction($player_id = intval(self::getCurrentPlayerId()));
//
		if ($faction !== $this->possible['faction']) throw new BgaVisibleSystemException("Invalid faction: $faction");
//
		$this->gamestate->nextState('impulseCombatPhase');
	}
	function actActivation(#[IntParam(min: 1, max: 15)] int $location)
	{
		$faction = Factions::getFaction($player_id = intval(self::getCurrentPlayerId()));
//
		if ($faction !== $this->possible['faction']) throw new BgaVisibleSystemException("Invalid faction: $faction");
		if (!in_array($location, $this->possible['locations'])) throw new BgaVisibleSystemException("Invalid location: $location");
//
		if (Units::overstacking($location, $faction) <= 3)
		{
			foreach ($this->possible['locations'] as $otherLocation) if (Units::overstacking($otherLocation, $faction) > 3) throw new BgaUserException(self::_("If a player has any over stacked areas, they must compulsory activate one of those areas during their Impulse Phase"));
		}
//
		$this->globals->set('activeArea', $location);
//
		$this->gamestate->nextState('movementPhase');
	}
	function actBuildPalisades(#[JsonParam] array $locations)
	{
		$faction = Factions::getFaction($player_id = intval(self::getCurrentPlayerId()));
//
		if ($faction !== Factions::SPANISH) throw new BgaVisibleSystemException("Only Spanish faction can build palisades");
		if (sizeof($locations) > 3) throw new BgaVisibleSystemException("You can only build up to 3 palisades");
		if (!array_key_exists('palisades', $this->possible)) throw new BgaVisibleSystemException("Invalid possible: " . json_encode($this->possible));
//
		foreach ($this->possible['locations'] as $otherLocation) if (Units::overstacking($otherLocation, $faction) > 3) throw new BgaUserException(self::_("If a player has any over stacked areas, they must compulsory activate one of those areas during their Impulse Phase"));
//
		$palisades = Counters::getAtLocation('aside', 'palisades');
//
		foreach ($locations as $location)
		{
			if (!in_array($location, $this->possible['palisades'])) throw new BgaVisibleSystemException("Invalid location: $location");
			if (Counters::getAtLocation($location, 'palisades')) throw new BgaVisibleSystemException("Palisade already at $location");
//
			if (!$palisades) throw new BgaUserException(self::_("No more Palisades in reserve"));
			$palisade = array_pop($palisades);
//
			$palisade['location'] = $location;
			Counters::setLocation($palisade['id'], $palisade['location']);
//* -------------------------------------------------------------------------------------------------------- */
			self::notifyAllPlayers('placeCounter', '', ['counter' => $palisade]);
//* -------------------------------------------------------------------------------------------------------- */
			self::notifyAllPlayers('msg', clienttranslate('${PALISADE} is build at <B>${location}</B>'), [
				'PALISADE' => $palisade, 'location' => $this->LOCATIONS[$location], 'i18n' => ['location']]);
//* -------------------------------------------------------------------------------------------------------- */
		}
		$this->gamestate->nextState('impulseCombatPhase');
	}
	function actBuildCitadels(#[JsonParam] array $locations)
	{
		$faction = Factions::getFaction($player_id = intval(self::getCurrentPlayerId()));
//
		if ($faction !== Factions::SPANISH) throw new BgaVisibleSystemException("Only Spanish faction can build citadels");
		if (sizeof($locations) > 2) throw new BgaVisibleSystemException("You can only build up to 2 citadels");
		if (!array_key_exists('citadels', $this->possible)) throw new BgaVisibleSystemException("Invalid possible: " . json_encode($this->possible));
//
		foreach ($this->possible['locations'] as $otherLocation) if (Units::overstacking($otherLocation, $faction) > 3) throw new BgaUserException(self::_("If a player has any over stacked areas, they must compulsory activate one of those areas during their Impulse Phase"));
//
		$citadels = Counters::getAtLocation('aside', 'citadels');
		if (sizeof(Counters::getByType('citadels')) >= 3) throw new BgaUserException(self::_("A maximum of 3 Citadels can be in play at any time"));
//
		foreach ($locations as $location)
		{
			if (!in_array($location, $this->possible['citadels'])) throw new BgaVisibleSystemException("Invalid location: $location");
			if (Counters::getAtLocation($location, 'citadels')) throw new BgaVisibleSystemException("Citadel already at $location");
//
			if (!$citadels) throw new BgaUserException(self::_("No more Citadels in reserve"));
			$citadel = array_pop($citadels);
//
			$units = Units::getAtLocation($location, $faction, 'Pawns');
			if (!$units) throw new BgaVisibleSystemException("Citadel building need a Pawn unit");
			$unit = array_pop($units);
//
			$unit['location'] = $unit['bag'];
			Units::update($unit);
//* -------------------------------------------------------------------------------------------------------- */
			self::notifyAllPlayers('placeUnit', clienttranslate('${UNIT} is removed to <B>red</B> bag'), ['unit' => $unit, 'UNIT' => $unit]);
//* -------------------------------------------------------------------------------------------------------- */
//
			$palisades = Counters::getAtLocation($location, 'palisades');
			if (!$palisades) throw new BgaVisibleSystemException("Citadel building need a Palisade");
			$palisade = array_pop($palisades);
//
			Counters::destroy($palisade['id']);
//* -------------------------------------------------------------------------------------------------------- */
			self::notifyAllPlayers('removeCounter', '', ['counter' => $palisade]);
//* -------------------------------------------------------------------------------------------------------- */
			$citadel['location'] = $location;
			Counters::setLocation($citadel['id'], $citadel['location']);
//* -------------------------------------------------------------------------------------------------------- */
			self::notifyAllPlayers('placeCounter', '', ['counter' => $citadel]);
//* -------------------------------------------------------------------------------------------------------- */
			self::notifyAllPlayers('msg', clienttranslate('${CITADEL} replaces ${PALISADE} at <B>${location}</B>'), [
				'CITADEL' => $citadel, 'PALISADE' => $palisade,
				'location' => $this->LOCATIONS[$location], 'i18n' => ['location']]);
//* -------------------------------------------------------------------------------------------------------- */
		}
		$this->gamestate->nextState('impulseCombatPhase');
	}
	function actScribe(#[IntParam] int $scribe, #[IntParam] int $attestor)
	{
		$faction = Factions::getFaction($player_id = intval(self::getCurrentPlayerId()));
//
		if ($faction !== $this->possible['faction']) throw new BgaVisibleSystemException("Invalid faction: $faction");
		if (!($unit = Units::get($scribe))) throw new BgaVisibleSystemException("Invalid unit: $scribe");
		if ($unit['type'] !== 'Scribes') throw new BgaVisibleSystemException("Invalid unit type: $unit[type]");
		if (!($counter = Counters::get($attestor))) throw new BgaVisibleSystemException("Invalid counter: $attestor");
		if ($counter['type'] !== 'attestor') throw new BgaVisibleSystemException("Invalid unit type: $counter[type]");
		if ($unit['location'] !== $counter['location']) throw new BgaVisibleSystemException("Invalid location: $unit[location] <>$counter[location]");
//* -------------------------------------------------------------------------------------------------------- */
		self::notifyAllPlayers('msg', clienttranslate('Attestor is removed from <B>${location}</B>'), ['location' => $this->LOCATIONS[$counter['location']], 'i18n' => ['location']]);
//* -------------------------------------------------------------------------------------------------------- */
		$unit['location'] = $unit['bag'];
		Units::update($unit);
//* -------------------------------------------------------------------------------------------------------- */
		self::notifyAllPlayers('placeUnit', clienttranslate('${UNIT} is removed to <B>red</B> bag'), ['unit' => $unit, 'UNIT' => $unit]);
//* -------------------------------------------------------------------------------------------------------- */
		Counters::destroy($attestor);
//* -------------------------------------------------------------------------------------------------------- */
		self::notifyAllPlayers('removeCounter', '', ['counter' => $counter]);
//* -------------------------------------------------------------------------------------------------------- */
		self::updateVP();
//
		$this->gamestate->nextState('continue');
	}
	function actMovementPhase(#[JsonParam] array $units, #[JsonParam] array $shipsWear)
	{
		$faction = Factions::getFaction($player_id = intval(self::getCurrentPlayerId()));
//
		if ($faction !== $this->possible['faction']) throw new BgaVisibleSystemException("Invalid faction: $faction");
//
		$from = $this->globals->get('activeArea');
//
		if ($navalDifficulties = $this->globals->get('navalDifficulties'))
		{
			if (array_key_exists($from, $shipsWear) && is_int($shipsWear[$from]))
			{
				$unit = Units::get($id = $shipsWear[$from]);
//
				if (!$unit) throw new BgaVisibleSystemException("Invalid unit: $id");
				if ($unit['faction'] !== $faction) throw new BgaVisibleSystemException("Invalid unit: " . json_encode($unit));
				if (intval($unit['location']) !== $from) throw new BgaVisibleSystemException("Invalid unit: " . json_encode($unit));
//
				self::reduceUnit($unit);
//
				$navalDifficulties = false;
			}
		}
//
		foreach ($units as $id => $to)
		{
			$unit = Units::get($id);
//
			if (!$unit) throw new BgaVisibleSystemException("Invalid unit: $id");
			if ($unit['faction'] !== $faction) throw new BgaVisibleSystemException("Invalid unit: " . json_encode($unit));
			if (intval($unit['location']) !== $from) throw new BgaVisibleSystemException("Invalid unit: " . json_encode($unit));
//
			if ($navalDifficulties && !in_array($to, [(($unit['location'] + 1 - 1) % 15) + 1, (($unit['location'] - 1 - 1 + 15) % 15) + 1])) throw new BgaVisibleSystemException("Naval difficulties");
//
			$unit['location'] = $to;
			Units::update($unit);
//* -------------------------------------------------------------------------------------------------------- */
			self::notifyAllPlayers('placeUnit', '', ['unit' => $unit]);
//* -------------------------------------------------------------------------------------------------------- */
		}
//
		$this->gamestate->nextState('impulseCombatPhase');
	}
	function actCombat(#[IntParam(min: 1, max: 15)] int $location)
	{
		$faction = Factions::getFaction($player_id = intval(self::getCurrentPlayerId()));
//
		if ($faction !== $this->possible['faction']) throw new BgaVisibleSystemException("Invalid faction: $faction");
		if (!in_array($location, $this->possible['locations'])) throw new BgaVisibleSystemException("Invalid location: $location");
//
		$this->globals->set('location', $location);
		$this->globals->set('attacker', $faction);
		$this->globals->set('defender', Factions::other($faction));
//
		$this->gamestate->nextState('combat');
	}
	function actCombatSelectUnits(#[JsonParam] array $units)
	{
		$faction = Factions::getFaction($player_id = intval(self::getCurrentPlayerId()));
//
		if (!array_key_exists($player_id, $this->possible)) throw new BgaVisibleSystemException("Invalid player: $player_id");
		if ($faction !== $this->possible[$player_id]['faction']) throw new BgaVisibleSystemException("Invalid faction: $faction");
		if (sizeof($units) !== 3) throw new BgaVisibleSystemException("Invalid number of units: " . sizeof($units));
		if (array_diff($units, $this->globals->get("combatUnits/$faction"))) throw new BgaVisibleSystemException("Invalid units: " . json_encode($units));
//
		$this->globals->set("combatUnits/$faction", $units);
//
		$this->gamestate->setPlayerNonMultiactive($player_id, 'combatRolls');
	}
	function actCombatHits(#[JsonParam] array $units)
	{
		$faction = Factions::getFaction($player_id = intval(self::getCurrentPlayerId()));
//
		if (!array_key_exists($player_id, $this->possible)) throw new BgaVisibleSystemException("Invalid player: $player_id");
		if ($faction !== $this->possible[$player_id]['faction']) throw new BgaVisibleSystemException("Invalid faction: $faction");
//
		$hits = $this->globals->get('hits');
		$hits[$faction] = 'done';
		$this->globals->set('hits', $hits);
//
		foreach ($units as $id => $hits)
		{
			if ($hits > 0)
			{
				$unit = Units::get($id);
//
				if ($unit['faction'] !== $faction) throw new BgaVisibleSystemException("Invalid unit: " . json_encode($unit));
				if (!in_array($id, $this->globals->get("combatUnits/$unit[faction]"))) throw new BgaVisibleSystemException("Invalid unit: " . json_encode($unit));
//
				if (!self::reduceUnit($unit, $hits)) $this->globals->set("combatUnits/$faction", array_values(array_diff($this->globals->get("combatUnits/$faction"), [$id])));
			}
		}
//
		$this->gamestate->setPlayerNonMultiactive($player_id, 'combatRolls');
	}
	function actRetreat(#[JsonParam] array $units, #[JsonParam] array $shipsWear)
	{
		$faction = Factions::getFaction($player_id = intval(self::getCurrentPlayerId()));
//
		if (!array_key_exists($player_id, $this->possible)) throw new BgaVisibleSystemException("Invalid player: $player_id");
		if ($faction !== $this->possible[$player_id]['faction']) throw new BgaVisibleSystemException("Invalid faction: $faction");
//
		$from = $this->globals->get('location');
//
		if ($navalDifficulties = $this->globals->get('navalDifficulties'))
		{
			if (array_key_exists($from, $shipsWear) && is_int($shipsWear[$from]))
			{
				$unit = Units::get($id = $shipsWear[$from]);
//
				if (!$unit) throw new BgaVisibleSystemException("Invalid unit: $id");
				if ($unit['faction'] !== $faction) throw new BgaVisibleSystemException("Invalid unit: " . json_encode($unit));
				if (intval($unit['location']) !== $from) throw new BgaVisibleSystemException("Invalid unit: " . json_encode($unit));
//
				self::reduceUnit($unit);
//
				$navalDifficulties = false;
			}
		}
//
		foreach ($units as $id => $to)
		{
			$unit = Units::get($id);
//
			if (!$unit) throw new BgaVisibleSystemException("Invalid unit: $id");
			if ($unit['faction'] !== $faction) throw new BgaVisibleSystemException("Invalid unit: " . json_encode($unit));
			if (intval($unit['location']) !== $from) throw new BgaVisibleSystemException("Invalid unit: " . json_encode($unit));
//
			if ($navalDifficulties && !in_array($to, [(($unit['location'] + 1 - 1) % 15) + 1, (($unit['location'] - 1 - 1 + 15) % 15) + 1])) throw new BgaVisibleSystemException("Naval difficulties");
//
			$unit['location'] = $to;
			Units::update($unit);
//* -------------------------------------------------------------------------------------------------------- */
			self::notifyAllPlayers('placeUnit', '', ['unit' => $unit]);
//* -------------------------------------------------------------------------------------------------------- */
		}
//
		$this->globals->set("combatUnits/$faction", array_values(array_diff($this->globals->get("combatUnits/$faction"), array_keys($units))));
//
		if ($player_id === Factions::getPlayer($this->globals->get('defender')))
		{
			$player_id = Factions::getPlayer($this->globals->get('attacker'));
			self::giveExtraTime($player_id);
			$this->gamestate->setPlayersMultiactive([$player_id], 'newRoundOfCombat', true);
		}
		else $this->gamestate->setPlayerNonMultiactive($player_id, 'newRoundOfCombat');
	}
}
