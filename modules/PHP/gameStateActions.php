<?php

use \Bga\GameFramework\Actions\Types\BoolParam;
use \Bga\GameFramework\Actions\Types\IntParam;
use \Bga\GameFramework\Actions\Types\StringParam;
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
	function actSecretChoice(#[JsonParam] array $choices)
	{
		$player_id = self::getCurrentPlayerId();
		if (!array_key_exists($player_id, $this->possible)) throw new BgaVisibleSystemException("Invalid player: $player_id");
//
		foreach ($choices as $faction => $card)
		{
			if (!in_array($faction, $this->possible[$player_id])) throw new BgaVisibleSystemException("Invalid faction: $faction");
			if (!in_array($card, Factions::getStatus($faction, 'events'))) throw new BgaVisibleSystemException("Invalid card: $card");
			$this->globals->set("event/$faction", $card);
//* -------------------------------------------------------------------------------------------------------- */
			self::notifyPlayer($player_id, 'event', '', ['event' => $card]);
//* -------------------------------------------------------------------------------------------------------- */
		}
		if ($this->gamestate->setPlayerNonMultiactive($player_id, 'eventResolutionPhase'))
		{
//* -------------------------------------------------------------------------------------------------------- */
			self::notifyAllPlayers('msg', '${EVENTS}', ['EVENTS' => [$this->globals->get("event/" . Factions::INDIGENOUS), $this->globals->get("event/" . Factions::SPANISH)]]);
//* -------------------------------------------------------------------------------------------------------- */
		}
	}
	function actEventResolution(#[JsonParam] array $units)
	{
		$player_id = Factions::getPlayer($faction = $this->globals->get('faction'));
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
		$this->globals->set("recoveryValue/$faction", $this->CARDS[$this->possible['card']]['recoveryValue']);
		$this->globals->delete("event/$faction");
//
		$this->gamestate->nextState('eventCombatPhase');
	}
	function actReinforcement(#[JsonParam] array|null $reinforcement, #[JsonParam] array|null $units)
	{
		$player_id = Factions::getPlayer($faction = $this->globals->get('faction'));
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
			if (sizeof($units) > $this->possible['reinforcement']) throw new BgaVisibleSystemException("Invalid count: " . sizeof($units) . " <> " . $this->possible['reinforcement']);

			$priority = 0;
			foreach ($units as $id => $location)
			{
				$unit = Units::get($id);
				if (!$unit) throw new BgaVisibleSystemException("Invalid unit: $id");
//
				if ($location === 'heal')
				{
					if (!$unit['reduced']) throw new BgaVisibleSystemException("Unit already at full health");
//
					$unit['reduced'] = 0;
					Units::update($unit);
//* -------------------------------------------------------------------------------------------------------- */
					self::notifyAllPlayers('placeUnit', clienttranslate('${UNIT} is restored at full health at <B>${location}</B>'), [
						'unit' => $unit, 'UNIT' => $unit,
						'location' => $this->LOCATIONS[$unit['location']], 'i18n' => ['location']]);
//* -------------------------------------------------------------------------------------------------------- */
				}
				else
				{
					if (!array_key_exists($location, $this->possible['locations'])) throw new BgaVisibleSystemException("Invalid location: $location");
					$priority = max($priority, $this->possible['locations'][$location]);
//
					$unit['location'] = $location;
					Units::update($unit);
//* -------------------------------------------------------------------------------------------------------- */
					self::notifyAllPlayers('placeUnit', clienttranslate('${UNIT} is deployed at <B>${location}</B>'), [
						'unit' => $unit, 'UNIT' => $unit,
						'location' => $this->LOCATIONS[$unit['location']], 'i18n' => ['location']]);
//* -------------------------------------------------------------------------------------------------------- */
					if (Units::overstacking($location, $faction) > 3) throw new BgaUserException(self::_("Overstacking"));
				}
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
		$faction = $this->globals->get('faction');
		if ($faction !== $this->possible['faction']) throw new BgaVisibleSystemException("Invalid faction: $faction");
//
		$this->globals->delete('activeArea');
//
		$this->gamestate->nextState('impulseCombatPhase');
	}
	function actActivation(#[IntParam(min: 1, max: 15)] int $location)
	{
		$faction = $this->globals->get('faction');
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
	function actIncursion(#[IntParam] int $from, #[IntParam] int $to, #[JsonParam] array $shipsWear = [])
	{
		$faction = $this->globals->get('faction');
		if ($faction !== $this->possible['faction']) throw new BgaVisibleSystemException("Invalid faction: $faction");
		if (!in_array($from, $this->possible['locations'])) throw new BgaVisibleSystemException("Invalid location: $from");
//
		foreach ($this->possible['locations'] as $otherLocation) if (Units::overstacking($otherLocation, $faction) > 3) throw new BgaUserException(self::_("If a player has any over stacked areas, they must compulsory activate one of those areas during their Impulse Phase"));
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
		if ($navalDifficulties && !in_array($to, [(($from + 1 - 1) % 15) + 1, (($from - 1 - 1 + 15) % 15) + 1])) throw new BgaVisibleSystemException("Naval difficulties");
//
		$this->globals->set('incursion', ['attacker' => $faction, 'from' => $from, 'to' => $to, 'attempts' => 0]);
//
		$this->gamestate->nextState('incursion');
	}
	function actIncursionInjuries(#[IntParam] int $id)
	{
		if (!array_key_exists($id, $this->possible['units'])) throw new BgaVisibleSystemException("Invalid unit: $id");
		if (!($unit = Units::get($id))) throw new BgaVisibleSystemException("Invalid unit: $id");
		if ($unit['faction'] !== $this->possible['faction']) throw new BgaVisibleSystemException("Invalid faction: $unit[faction]");
//
		self::reduceUnit($unit, $this->possible['hits']);
//
		if ($this->possible['attacker']) $this->gamestate->nextState('continue');
		else $this->gamestate->nextState('incursion');
	}
	function actIncursionContinue(#[BoolParam] $continue)
	{
		$faction = $this->globals->get('faction');
		['attacker' => $faction, 'from' => $from, 'to' => $to, 'attempts' => $attempts] = $this->globals->get('incursion');
//
		$attempts++;
		if (!$continue) $attempts++;
//
		$this->globals->set('incursion', ['attacker' => $faction, 'from' => $from, 'to' => $to, 'attempts' => $attempts]);
//
		$this->gamestate->nextState('incursion');
	}
	function actBuildPalisades(#[JsonParam] array $locations)
	{
		$faction = $this->globals->get('faction');
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
		$faction = $this->globals->get('faction');
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
			self::notifyAllPlayers('removeUnit', clienttranslate('${UNIT} is removed to ${BAG}'), ['unit' => $unit, 'UNIT' => $unit, 'BAG' => $unit['bag']]);
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
		$faction = $this->globals->get('faction');
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
		self::notifyAllPlayers('removeUnit', clienttranslate('${UNIT} is removed to ${BAG}'), ['unit' => $unit, 'UNIT' => $unit, 'BAG' => $unit['bag']]);
//* -------------------------------------------------------------------------------------------------------- */
		Counters::destroy($attestor);
//* -------------------------------------------------------------------------------------------------------- */
		self::notifyAllPlayers('removeCounter', '', ['counter' => $counter]);
//* -------------------------------------------------------------------------------------------------------- */
		self::updateVP();
//
		$this->gamestate->nextState('continue');
	}
	function actMovementPhase(#[JsonParam] array $units, #[JsonParam] array $shipsWear = [])
	{
		$faction = $this->globals->get('faction');
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
		$faction = $this->globals->get('faction');
		if ($faction !== $this->possible['faction']) throw new BgaVisibleSystemException("Invalid faction: $faction");
		if (!in_array($location, $this->possible['locations'])) throw new BgaVisibleSystemException("Invalid location: $location");
//
		$this->globals->set('location', $location);
		$this->globals->set('attacker', $faction);
		$this->globals->set('defender', Factions::other($faction));
//
		$this->gamestate->nextState('combat');
	}
	function actCombatSelectUnits(#[JsonParam] array $selectedUnits)
	{
		$player_id = self::getCurrentPlayerId();
		if (!array_key_exists($player_id, $this->possible)) throw new BgaVisibleSystemException("Invalid player: $player_id");
//
		foreach ($selectedUnits as $faction => $units)
		{
			if (!array_key_exists($faction, $this->possible[$player_id])) throw new BgaVisibleSystemException("Invalid faction: $faction");
//
			if ($this->possible[$player_id][$faction]['units'])
			{
				if (sizeof($units) !== 3) throw new BgaVisibleSystemException("Invalid number of units: " . sizeof($units));
				if (array_diff($units, $this->globals->get("combatUnits/$faction"))) throw new BgaVisibleSystemException("Invalid units: " . json_encode($units));
//
				$this->globals->set("combatUnits/$faction", $units);
			}
			else if (sizeof($units) !== 0) throw new BgaVisibleSystemException("Invalid number of units: " . sizeof($units));
		}
//
		$this->gamestate->setPlayerNonMultiactive($player_id, 'combatRolls');
	}
	function actCombatHits(#[JsonParam] array $combatHits)
	{
		$player_id = self::getCurrentPlayerId();
		if (!array_key_exists($player_id, $this->possible)) throw new BgaVisibleSystemException("Invalid player: $player_id");

		$hits = $this->globals->get('hits');
		foreach ($combatHits as $faction => $units)
		{
			if (!array_key_exists($faction, $this->possible[$player_id])) throw new BgaVisibleSystemException("Invalid faction: $faction");
//
			foreach ($units as $id => $hit)
			{
				if ($hit > 0)
				{
					$unit = Units::get($id);
//
					if ($unit['faction'] !== $faction) throw new BgaVisibleSystemException("Invalid unit: " . json_encode($unit));
					if (!in_array($id, $this->globals->get("combatUnits/$unit[faction]"))) throw new BgaVisibleSystemException("Invalid unit: " . json_encode($unit));
//
					if (!self::reduceUnit($unit, $hit)) $this->globals->set("combatUnits/$faction", array_values(array_diff($this->globals->get("combatUnits/$faction"), [$id])));
				}
			}
			$hits[$faction] = 'done';
		}
		$this->globals->set('hits', $hits);
//
		$this->gamestate->setPlayerNonMultiactive($player_id, 'combatRolls');
	}
	function actRetreat(#[StringParam] string $faction, #[JsonParam] array $units, #[JsonParam] array $shipsWear)
	{
		$player_id = intval(self::getCurrentPlayerId());
		if (Factions::getPlayer($faction) !== $player_id) throw new BgaVisibleSystemException("Invalid player: $player_id");
//
		$from = $this->globals->get('location');
//
		$navalDifficulties = $this->globals->get('navalDifficulties');
		if ($navalDifficulties)
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
		$this->gamestate->setPlayerNonMultiactive($player_id, 'continue');
	}
	function actDivineGraceNatureSpirits(#[StringParam] string $type, #[JsonParam] array|null $dice)
	{
		$player_id = Factions::getPlayer($faction = $this->globals->get('faction'));
//
		if ($type !== 'pass')
		{
			$rolls = $this->globals->get('dice');
//* -------------------------------------------------------------------------------------------------------- */
			if ($faction === Factions::INDIGENOUS) self::notifyAllPlayers('msg', clienttranslate('${player_name} uses <B>Nature Spirits</B>'), ['player_name' => self::getCurrentPlayerName()]);
			if ($faction === Factions::SPANISH) self::notifyAllPlayers('msg', clienttranslate('${player_name} uses <B>Divine Grace</B>'), ['player_name' => self::getCurrentPlayerName()]);
//* -------------------------------------------------------------------------------------------------------- */
			$this->globals->set('used', true);
//
			$counter = Counters::get($this->globals->get('counter'));
			Counters::setLocation($counter['id'], $counter['location'] = Factions::getImpulse($faction) - 1);
			Counters::setType($counter['id'], $counter['type'] = ['divineGrace' => 'natureSpirits', 'natureSpirits' => 'divineGrace'][$counter['type']]);
//* -------------------------------------------------------------------------------------------------------- */
			self::notifyAllPlayers('placeCounter', '', ['counter' => $counter]);
//* -------------------------------------------------------------------------------------------------------- */
			switch ($type)
			{
//
				case 're-roll':
//* -------------------------------------------------------------------------------------------------------- */
					foreach ($dice as $die) self::notifyAllPlayers('msg', clienttranslate('${player_name} re-rolls ${DICE}'), ['player_name' => self::getCurrentPlayerName(), 'DICE' => $rolls[$die] = bga_rand(1, 6)]);
//* -------------------------------------------------------------------------------------------------------- */
					break;
//
				case '-1':
//* -------------------------------------------------------------------------------------------------------- */
					foreach ($dice as $die) self::notifyAllPlayers('msg', clienttranslate('${player_name} modifies die ${DICE}'), ['player_name' => self::getCurrentPlayerName(), 'DICE' => $rolls[$die] = max(0, $rolls[$die] - 1)]);
//* -------------------------------------------------------------------------------------------------------- */
					break;
//
				default: throw new BgaVisibleSystemException("Invalid type: $type");
			}
//
			$this->globals->set('dice', $rolls);
		}
		$this->gamestate->nextState('continue');
	}
}
