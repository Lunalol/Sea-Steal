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
			self::notifyAllPlayers('msg', '${EVENTS}', ['EVENTS' => [$this->globals->get(Factions::INDIGENOUS), $this->globals->get(Factions::SPANISH)]]);
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
		foreach ($units as $id => $location)
		{
			if (!in_array($location, $this->possible['locations'])) throw new BgaVisibleSystemException("Invalid location: $location");
//
			$unit = Units::get($id);
			$unit['location'] = $location;
			Units::update($unit);
//* -------------------------------------------------------------------------------------------------------- */
			self::notifyAllPlayers('placeUnit', '', ['unit' => $unit]);
//* -------------------------------------------------------------------------------------------------------- */
		}
		foreach (Units::getAtLocation('event') as $unit)
		{
			$unit['location'] = $unit['bag'];
			Units::update($unit);
		}
//
		$this->globals->delete("event/$faction");
//
		$this->gamestate->nextState('eventCombatPhase');
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
		if (!Units::overstacking($location, $faction))
		{
			foreach ($this->possible['locations'] as $otherLocation) if (!Units::overstacking($otherLocation, $faction)) throw new BgaUserException(self::_("If a player has any over stacked areas, they must compulsory activate one of those areas during their Impulse Phase"));
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
		$citadels = Counters::getAtLocation('aside', 'citadels');
		if (sizeof(Counters::getByType('citadels') >= 3)) throw new BgaUserException(self::_("A maximum of 3 Citadels can be in play at any time"));
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
	function actMovementPhase(#[JsonParam] array $units)
	{
		$faction = Factions::getFaction($player_id = intval(self::getCurrentPlayerId()));
//
		if ($faction !== $this->possible['faction']) throw new BgaVisibleSystemException("Invalid faction: $faction");
//
		foreach ($units as $id => $location)
		{
			if (in_array($id, $this->possible['units'])) throw new BgaVisibleSystemException("Invalid unit: $id");
//
			$unit = Units::get($id);
			$unit['location'] = $location;
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
				if ($hits === 1 && intval($unit['reduced']) === 0)
				{
					$unit['reduced'] = 1;
					Units::update($unit);
//* -------------------------------------------------------------------------------------------------------- */
					self::notifyAllPlayers('placeUnit', clienttranslate('A unit is reduced to ${UNIT}'), ['unit' => $unit, 'UNIT' => $unit]);
//* -------------------------------------------------------------------------------------------------------- */
				}
				else
				{
					$this->globals->set("combatUnits/$faction", array_values(array_diff($this->globals->get("combatUnits/$faction"), [$id])));
//
					switch ($unit['type'])
					{
						case 'Cavalry':
						case 'Arquebusiers':
						case 'Swordmen':
							{
								$unit['location'] = $unit['bag'];
								Units::update($unit);
//* -------------------------------------------------------------------------------------------------------- */
								self::notifyAllPlayers('placeUnit', clienttranslate('${UNIT} is removed to <B>yellow</B> bag'), ['unit' => $unit, 'UNIT' => $unit]);
//* -------------------------------------------------------------------------------------------------------- */
							}
							break;
//
						case 'Pawns':
						case 'Scribes':
							{
								$unit['location'] = $unit['bag'];
								Units::update($unit);
//* -------------------------------------------------------------------------------------------------------- */
								self::notifyAllPlayers('placeUnit', clienttranslate('${UNIT} is removed to <B>red</B> bag'), ['unit' => $unit, 'UNIT' => $unit]);
//* -------------------------------------------------------------------------------------------------------- */
							}
							break;
//
						case 'Caciques':
						case 'Naborias':
							{
								$unit['location'] = $unit['bag'];
								Units::update($unit);
//* -------------------------------------------------------------------------------------------------------- */
								self::notifyAllPlayers('placeUnit', clienttranslate('${UNIT} is removed to <B>green</B> bag'), ['unit' => $unit, 'UNIT' => $unit]);
//* -------------------------------------------------------------------------------------------------------- */
							}
							break;
//
						case 'Calinagos':
						case 'Tamas':
							{
								$unit['location'] = $unit['bag'];
								Units::update($unit);
//* -------------------------------------------------------------------------------------------------------- */
								self::notifyAllPlayers('placeUnit', clienttranslate('${UNIT} is removed to <B>blue</B> bag'), ['unit' => $unit, 'UNIT' => $unit]);
//* -------------------------------------------------------------------------------------------------------- */
							}
							break;
//
						default:
							{
								$unit['location'] = 'aside';
								Units::update($unit);
//* -------------------------------------------------------------------------------------------------------- */
								self::notifyAllPlayers('placeUnit', clienttranslate('${UNIT} is removed from the game'), ['unit' => $unit, 'UNIT' => $unit]);
//* -------------------------------------------------------------------------------------------------------- */
							}
					}
				}
			}
		}
//
		$this->gamestate->setPlayerNonMultiactive($player_id, 'combatRolls');
	}
	function actRetreat(#[IntParam(min: 1, max: 15)] int $location, #[JsonParam] array $units)
	{
		$faction = Factions::getFaction($player_id = intval(self::getCurrentPlayerId()));
//
		if (!array_key_exists($player_id, $this->possible)) throw new BgaVisibleSystemException("Invalid player: $player_id");
		if ($faction !== $this->possible[$player_id]['faction']) throw new BgaVisibleSystemException("Invalid faction: $faction");
//
		foreach ($units as $id)
		{
			$unit = Units::get($id);
			if (array_diff($units, $this->globals->get("combatUnits/$faction"))) throw new BgaVisibleSystemException("Invalid units: " . json_encode($units));
//
			$locations = Units::retreat($unit);
			if (!in_array($location, $locations)) throw new BgaVisibleSystemException("Invalid location: $location");
//
			$unit['location'] = $location;
			Units::update($unit);
//* -------------------------------------------------------------------------------------------------------- */
			self::notifyAllPlayers('placeUnit', '', ['unit' => $unit]);
//* -------------------------------------------------------------------------------------------------------- */
		}
		$this->globals->set("combatUnits/$faction", array_values(array_diff($this->globals->get("combatUnits/$faction"), $units)));
//
		$this->gamestate->nextState('continue');
	}
	function actNoRetreat()
	{
		$faction = Factions::getFaction($player_id = intval(self::getCurrentPlayerId()));
//
		if (!array_key_exists($player_id, $this->possible)) throw new BgaVisibleSystemException("Invalid player: $player_id");
		if ($faction !== $this->possible[$player_id]['faction']) throw new BgaVisibleSystemException("Invalid faction: $faction");
//
		$attacker = $this->globals->get('attacker');
		$defender = $this->globals->get('defender');
//
		if ($player_id === Factions::getPlayer($defender))
		{
			$player_id = Factions::getPlayer($attacker);
			self::giveExtraTime($player_id);
			$this->gamestate->setPlayersMultiactive([$player_id], 'newRoundOfCombat', true);
		}
		else $this->gamestate->setPlayerNonMultiactive($player_id, 'newRoundOfCombat');
	}
}
