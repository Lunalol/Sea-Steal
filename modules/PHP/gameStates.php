<?php

/**
 *
 * @author Lunalol - PERRIN Jean-Luc
 *
 */
trait gameStates
{
	function stSetup()
	{
//* -------------------------------------------------------------------------------------------------------- */
		self::notifyAllPlayers('msg', '<span class="SSphase">${LOG}</span>', ['i18n' => ['LOG'], 'LOG' => clienttranslate('Game setup')]);
//* -------------------------------------------------------------------------------------------------------- */
// 1. Deploy the game board: Place the game board flat on a surface. Players should sit opposite each other, with the Indigenous player to the left of the board and the Spanish player to the right.
// 2. Preparing Military Unit Bags: Each player should prepare their respective military unit bags by following these steps:
		{
//		Indigenous Player
			{
//			Taíno Units: Place all Taíno into the green bag. Leave Leader units aside. 34 Taíno Units (Green)
				{
					Units::create(Factions::INDIGENOUS, 'Leader', 'aside'); // 1 Leader (Caonabó)
					for ($i = 0; $i < 13; $i++) Units::create(Factions::INDIGENOUS, 'Caciques', 'green'); // 13 Caciques
					for ($i = 0; $i < 20; $i++) Units::create(Factions::INDIGENOUS, 'Naborias', 'green'); // 20 Naborias
				}
//			Caribe Units: Place all Carib units into the blue bag. // 33 Caribe Units (Blue)
				{
					for ($i = 0; $i < 13; $i++) Units::create(Factions::INDIGENOUS, 'Calinagos', 'blue'); // 13 C alinagos
					for ($i = 0; $i < 20; $i++) Units::create(Factions::INDIGENOUS, 'Tamas', 'blue'); // 20 Tamas
				}
//			Rebel Units: Place all Rebel units into the white bag. // 16 Rebelds units (White)
				{
					for ($i = 0; $i < 6; $i++) Units::create(Factions::INDIGENOUS, 'Captains', 'white'); // 6 Captains
					for ($i = 0; $i < 10; $i++) Units::create(Factions::INDIGENOUS, 'Troops', 'white'); // 10 Troops
				}
			}
//		Spanish Player
			{
//			Settler Units: Place all Settler units into the red bag. // 22 Settler Units (Red)
				{
					for ($i = 0; $i < 12; $i++) Units::create(Factions::SPANISH, 'Pawns', 'red'); // 12 Pawns
					for ($i = 0; $i < 12; $i++) Units::create(Factions::SPANISH, 'Scribes', 'red'); // 12 Scribes
				}
//			Soldier Units: Place all Soldier units into the yellow bag. Leave Leader units aside. // 27 Soldier Units (Yellow)
				{
					Units::create(Factions::SPANISH, 'Leader', COLOMBUS); // 1 Leader (Cristopher Columbus)
					for ($i = 0; $i < 7; $i++) Units::create(Factions::SPANISH, 'Cavalry', 'yellow'); // 7 Cavalry
					for ($i = 0; $i < 7; $i++) Units::create(Factions::SPANISH, 'Arquebusiers', 'yellow'); // 7 Arquebusiers
					for ($i = 0; $i < 12; $i++) Units::create(Factions::SPANISH, 'Swordmen', 'yellow'); // 12 Swordmen
				}
			}
		}
// 3. Initial Placement of Indigenous Player Units:
		{
//		Randomly draw one Taíno or Caribe unit from the corresponding bag and place it by its full strength side in each of the 15 Zones on the board as follows:
			{
//			Zones marked with a blue square: Place 1 Carib unit.
				{
					foreach ([1, 2, 10] as $location) Units::draw('blue', $location);
				}
//			Zones marked with a green square: Place 1 Taíno unit.
				{
					foreach ([6, 7, 13, 14, 15] as $location) Units::draw('green', $location);
				}
//			Zones marked with a square divided into two sections (with one dot for blue and two dots for green):
//			Roll a 1 die for each zone. If the result is odd, place 1 Carib unit. If the result is even, place 1 Taíno unit.
				{
					foreach ([3, 4, 5, 8, 9, 11, 12] as $location) Units::draw(bga_rand(0, 1) ? 'blue' : 'green', $location);
				}
			}
		}
// 4. Initial Placement of Spanish Player Units:
		{
//		Place Columbus and 3 randomly drawn Spanish Soldier units by their full strength side in the appropriate zone on the board showing 3 yellow squares next to the 2 Caravels and 1 Nao of the First Voyage.
			Units::draw('yellow', COLOMBUS);
			Units::draw('yellow', COLOMBUS);
			Units::draw('yellow', COLOMBUS);
		}
// 5. Attestors Counters: Place one attestor marker on each of the boxes that show "2 VP" next to areas #1, 2, 7, 8, and 9 on the board.
		foreach (Counters::ATTESTORS as $location) Counters::create('attestor', $location);
// 6. Turn Marker: Place the Turn Marker on the "1 (1492)" box on the Game Turn Track.
		Counters::create('turn', 1);
// 7. Victory Points Marker: Place the Victory Points Marker on the "0" box on the Victory Point Track.
		Counters::create('VP', 0);
// 8. Royal Support Marker: Place the Royal Support Marker on the "3" space on your Royal Support
		Counters::create('royalSupport', 3);
// 9. Divine Grace/Nature Spirits Marker: Place the Divine Grace/Nature Spirits Marker near the Divine Grace/Nature Spirits section
//		Counters::create('divineGrace', 0);
//		Counters::create('natureSpirits', 0);
// 10. Impulse markers: Place both factions Impulse Counters on the “0” box of the Impulse
		Counters::create('impulseSpanish', 0);
		Counters::create('impulseIndigenous', 0);
// 11. Setup and Selection of Event Cards. Before the game begins, each player should follow these steps for their Event Cards:
		{
//		a) Shuffle your 8 Event Cards belonging to your faction face down without looking at them and leave the card deck aside.
			$evenIndigenous = [1, 2, 3, 4, 5, 6, 7, 8];
			shuffle($evenIndigenous);
//		b) Draw the top 5 cards from the shuffled deck. These will be your Event Cards for the game.
			Factions::setStatus(Factions::INDIGENOUS, 'events', array_slice($evenIndigenous, 0, 5));
			Factions::setStatus(Factions::INDIGENOUS, 'events', [1, 2, /* 3, */ 4, 5, 6, 7, /* 8 */]);
//		c) Set aside the remaining 3 Event Cards face down. These cards will not be used in the game.
//
//		a) Shuffle your 8 Event Cards belonging to your faction face down without looking at them and leave the card deck aside.
			$eventSpanish = [9, 10, 11, 12, 13, 14, 15, 16];
			shuffle($eventSpanish);
//		b) Draw the top 5 cards from the shuffled deck. These will be your Event Cards for the game.
			Factions::setStatus(Factions::SPANISH, 'events', array_slice($eventSpanish, 0, 5));
			Factions::setStatus(Factions::SPANISH, 'events', [9, /* 10, */ 11, /* 12, */ 13, 14, 15, 16]);
//		c) Set aside the remaining 3 Event Cards face down. These cards will not be used in the game.
		}
// 12. Prepare both Fate Card Decks.
		{
//		Create a deck with all 3 Fate Cards Game Turn 1 and shuffle.
			{
				$fateCardsTurn1 = [17, 18, 19];
				shuffle($fateCardsTurn1);
			}
//		Then create another deck with all 20 Fate Cards Game Turn 2-6 and shuffle.
			{
				$fateCardsTurn26 = [20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39];
				shuffle($fateCardsTurn26);
			}
		}
//
// 14. Additional counters: Place the following counters next to the board. Ship Wear, Palisades, Citadels, Caonabo , Anacaona, Guancaganarí , Sons of Caonabo
//
		for ($i = 0; $i < 11; $i++) Counters::create('palisades', 'aside');
		for ($i = 0; $i < 5; $i++) Counters::create('citadels', 'aside');
		Counters::create('shipsWear', 'aside');
//
		$fateCards = [array_pop($fateCardsTurn26), array_pop($fateCardsTurn26), array_pop($fateCardsTurn26), array_pop($fateCardsTurn26), array_pop($fateCardsTurn26), array_pop($fateCardsTurn1)];
		$this->globals->set('fateCards', $fateCards);
//
		self::setGameStateInitialValue('turn', 0);
		self::setGameStateInitialValue('fate', 0);
//
		$this->gamestate->changeActivePlayer(Factions::getPlayer(Factions::SPANISH));
//
		$this->gamestate->nextState('startOfGame');
	}
	function stStartOfRound()
	{
		$turn = intval(self::incGameStateValue('turn', 1));
//* -------------------------------------------------------------------------------------------------------- */
		self::notifyAllPlayers('msg', '<span class="SSphase">${LOG} ${turn}</span>', ['i18n' => ['LOG'], 'LOG' => clienttranslate('Start of turn'), 'turn' => $turn]);
//* -------------------------------------------------------------------------------------------------------- */
		$counter = Counters::getByType('turn')[0];
		$counter['location'] = $turn;
		Counters::setLocation($counter['id'], $counter['location']);
//* -------------------------------------------------------------------------------------------------------- */
		self::notifyAllPlayers('placeCounter', '', ['counter' => $counter]);
//* -------------------------------------------------------------------------------------------------------- */
		$this->gamestate->nextState('fatePhase');
	}
	function stEndOfRound()
	{
		$turn = intval(self::getGameStateValue('turn'));
		if ($turn === 1)
		{
			$bool = $this->CARDS[self::getGameStateValue('fate')][0];
			if ($bool & DIVINEGRACE) $counter = Counters::create('divineGrace', 0);
			if ($bool & NATURESPIRITS) $counter = Counters::create('natureSpirits', 0);
//* -------------------------------------------------------------------------------------------------------- */
			self::notifyAllPlayers('placeCounter', '', ['counter' => Counters::get($counter)]);
//* -------------------------------------------------------------------------------------------------------- */
		}
//* -------------------------------------------------------------------------------------------------------- */
		self::notifyAllPlayers('updateTurn', '<span class="SSphase">${LOG} ${turn}</span>', ['i18n' => ['LOG'], 'LOG' => clienttranslate('End of turn'), 'turn' => $turn]);
//* -------------------------------------------------------------------------------------------------------- */
		$this->gamestate->nextState('startOfRound');
	}
	function stFatePhase()
	{
//* -------------------------------------------------------------------------------------------------------- */
		self::notifyAllPlayers('msg', '<span class="SSsubphase">${LOG}</span>', ['i18n' => ['LOG'], 'LOG' => clienttranslate('Fate phase')]);
//* -------------------------------------------------------------------------------------------------------- */
		$fateCards = $this->globals->get('fateCards');
		self::setGameStateValue('fate', $fate = array_pop($fateCards));
		$this->globals->set('fateCards', $fateCards);
//* -------------------------------------------------------------------------------------------------------- */
		self::notifyAllPlayers('fate', clienttranslate('A new fate card is revealed'), ['fate' => $fate]);
//* -------------------------------------------------------------------------------------------------------- */
		$this->globals->set('navalDifficulties', $navalDifficulties = boolval($this->CARDS[$fate][0] & NAVALDIFFICULTIES));
//
		$counter = Counters::getByType('shipsWear', true)[0];
		$counter['location'] = $navalDifficulties ? 'shipsWear' : 'aside';
		Counters::setLocation($counter['id'], $counter['location']);
//* -------------------------------------------------------------------------------------------------------- */
		self::notifyAllPlayers('placeCounter', '', ['counter' => $counter]);
//* -------------------------------------------------------------------------------------------------------- */
		switch ($fate)
		{
			case 17:
			case 18:
			case 19:
				{
					$roll = bga_rand(1, 6);
//* -------------------------------------------------------------------------------------------------------- */
					self::notifyAllPlayers('fate', clienttranslate('${player_name} rolls ${DICE}'), ['player_name' => self::getActivePlayerName(), 'DICE' => $roll]);
//* -------------------------------------------------------------------------------------------------------- */
					$location = $this->CARDS[$fate]['landingArea'][$roll - 1];
					$this->globals->set('activeArea', $location);
//* -------------------------------------------------------------------------------------------------------- */
					self::notifyAllPlayers('msg', clienttranslate('Landing area is <B>${location}</B>'), ['location' => $this->LOCATIONS[$location], 'i18n' => ['location']]);
//* -------------------------------------------------------------------------------------------------------- */
					foreach (Units::getAtLocation(COLOMBUS, Factions::SPANISH) as $unit)
					{
						$unit['location'] = $location;
						Units::update($unit);
//* -------------------------------------------------------------------------------------------------------- */
						self::notifyAllPlayers('placeUnit', '', ['unit' => $unit]);
//* -------------------------------------------------------------------------------------------------------- */
					}
				}
				return $this->gamestate->nextState('eventPhase');
//
			default:
				{
					$impulse = $this->CARDS[$fate][1];
//
					Factions::setImpulse(Factions::SPANISH, $impulse);
					$impulseSpanish = Counters::getByType('impulseSpanish')[0];
					$impulseSpanish['location'] = $impulse;
					Counters::setLocation($impulseSpanish['id'], $impulseSpanish['location']);
//* -------------------------------------------------------------------------------------------------------- */
					self::notifyAllPlayers('placeCounter', '', ['counter' => $impulseSpanish]);
//* -------------------------------------------------------------------------------------------------------- */
					Factions::setImpulse(Factions::INDIGENOUS, $impulse);
					$impulseIndigenous = Counters::getByType('impulseIndigenous')[0];
					$impulseIndigenous['location'] = $impulse;
					Counters::setLocation($impulseIndigenous['id'], $impulseIndigenous['location']);
//* -------------------------------------------------------------------------------------------------------- */
					self::notifyAllPlayers('placeCounter', '', ['counter' => $impulseIndigenous]);
//* -------------------------------------------------------------------------------------------------------- */
				}
		}
//
		$this->gamestate->nextState('eventPhase');
	}
	function stEventPhase()
	{
		$turn = intval(self::getGameStateValue('turn'));
		if ($turn !== 1)
		{
//* -------------------------------------------------------------------------------------------------------- */
			self::notifyAllPlayers('msg', '<span class="SSsubphase">${LOG}</span>', ['i18n' => ['LOG'], 'LOG' => clienttranslate('Event phase')]);
//* -------------------------------------------------------------------------------------------------------- */
			$this->globals->set('state', 'eventCombatPhase');
//
			foreach ([Factions::INDIGENOUS, Factions::SPANISH] as $faction) self::giveExtraTime(Factions::getPlayer($faction));
//
			$this->gamestate->setAllPlayersMultiactive();
			$this->gamestate->nextState('secretChoice');
		}
		else $this->gamestate->nextState('recoveryPhase');
	}
	function stEventResolutionPhase()
	{
		foreach ([Factions::INDIGENOUS, Factions::SPANISH] as $faction)
		{
			$card = $this->globals->get("event/$faction");
			if ($card)
			{
				$this->gamestate->changeActivePlayer($player_id = Factions::getPlayer($faction));
				self::giveExtraTime($this->getActivePlayerId());
//* -------------------------------------------------------------------------------------------------------- */
//				self::notifyAllPlayers('msg', '<span class="SSsubphase">${LOG}</span>', ['i18n' => ['LOG'], 'LOG' => $this->CARDS[$card]['title']]);
//* -------------------------------------------------------------------------------------------------------- */
				$reinforcement = self::eventReinforcement($card);
//
				if ($faction === Factions::SPANISH && array_search('yellow', $reinforcement) !== false)
				{
					switch (Counters::getByType('royalSupport')[0]['location'])
					{
						case 0:
						case 1:
							unset($reinforcement[array_search('yellow', $reinforcement)]);
							unset($reinforcement[array_search('yellow', $reinforcement)]);
							break;
						case 2:
						case 3:
							unset($reinforcement[array_search('yellow', $reinforcement)]);
							break;
						case 6:
						case 7:
						case 8:
							array_unshift($reinforcement, 'yellow');
							break;
						case 9:
						case 10:
							array_unshift($reinforcement, 'yellow');
							array_unshift($reinforcement, 'yellow');
							break;
					}
				}
				foreach ($reinforcement as $bag) Units::draw($bag, 'event');
//
				return $this->gamestate->nextState('eventResolution');
			}
		}
		return $this->gamestate->nextState('recoveryPhase');
	}
	function stRecoveryPhase()
	{
		if (intval(self::getGameStateValue('turn')) !== 1)
		{
//* -------------------------------------------------------------------------------------------------------- */
			self::notifyAllPlayers('msg', '<span class="SSsubphase">${LOG}</span>', ['i18n' => ['LOG'], 'LOG' => clienttranslate('Recovery phase')]);
//* -------------------------------------------------------------------------------------------------------- */
			$this->globals->set('state', 'reinforcementCombatPhase');
//
			$indigenous = $this->globals->get("recoveryValue/" . Factions::INDIGENOUS);
			$spanish = $this->globals->get("recoveryValue/" . Factions::SPANISH);
//
			$this->globals->delete("recoveryValue/" . Factions::INDIGENOUS);
			$this->globals->delete("recoveryValue/" . Factions::SPANISH);
//
			if ($indigenous > $spanish + 1)
			{
				$this->globals->set("reinforcement/" . Factions::INDIGENOUS, 1);
				$this->globals->set("reinforcement/" . Factions::SPANISH, $indigenous - $spanish);
			}
			else if ($spanish > $indigenous + 1)
			{
				$this->globals->set("reinforcement/" . Factions::INDIGENOUS, $spanish - $indigenous);
				$this->globals->set("reinforcement/" . Factions::SPANISH, 1);
			}
			else
			{
				$this->globals->set("reinforcement/" . Factions::INDIGENOUS, 2);
				$this->globals->set("reinforcement/" . Factions::SPANISH, 2);
			}
//
			return $this->gamestate->nextState('reinforcementPhase');
		}
		else $this->gamestate->nextState('impulsePhase');
	}
	function stReinforcement()
	{
		foreach ([Factions::INDIGENOUS, Factions::SPANISH] as $faction)
		{
			$reinforcement = $this->globals->get("reinforcement/$faction");
			if ($reinforcement)
			{
				$this->gamestate->changeActivePlayer(Factions::getPlayer($faction));
				self::giveExtraTime($this->getActivePlayerId());
//
				return $this->gamestate->nextState('reinforcement');
			}
		}
		$this->gamestate->nextState('impulsePhase');
	}
	function stImpulsePhase()
	{
		if (intval(self::getGameStateValue('turn')) !== 1)
		{
//* -------------------------------------------------------------------------------------------------------- */
			self::notifyAllPlayers('msg', '<span class="SSsubphase">${LOG}</span>', ['i18n' => ['LOG'], 'LOG' => clienttranslate('Impulse phase')]);
//* -------------------------------------------------------------------------------------------------------- */
		}
//
		$this->globals->set('simultaneous', true);
		$this->globals->set('state', 'impulseCombatPhase');
//
		$this->gamestate->nextState('startOfImpulse');
	}
	function stStartOfImpulse()
	{
		if (intval(self::getGameStateValue('turn')) !== 1)
		{
			self::activeNextPlayer();
			$this->gamestate->nextState('action');
		}
		else
		{
			if (Factions::getImpulse(Factions::SPANISH) === 0)
			{
				Factions::setImpulse(Factions::SPANISH, 1);
				return $this->gamestate->nextState('impulseCombatPhase');
			}
			if (Factions::getImpulse(Factions::SPANISH) === 1)
			{
				Factions::setImpulse(Factions::SPANISH, 2);
				return $this->gamestate->nextState('movementPhase');
			}
			if (Factions::getImpulse(Factions::SPANISH) === 2) return $this->gamestate->nextState('victoryCheckPhase');
		}
	}
	function stAction()
	{
		$faction = Factions::getFaction($player_id = intval(self::getActivePlayerId()));
//* -------------------------------------------------------------------------------------------------------- */
		self::notifyAllPlayers('msg', '<span class="SSimpulse">${faction} <span>${LOG}</span></span>', ['i18n' => ['LOG'], 'LOG' => ['log' => clienttranslate('${player_name}\'s turn'), 'args' => ['player_name' => self::getCurrentPlayerName()]], 'faction' => $faction]);
//* -------------------------------------------------------------------------------------------------------- */
		self::giveExtraTime($this->getActivePlayerId());
	}
	function stEndOfImpulse()
	{
		if (intval(self::getGameStateValue('turn')) !== 1)
		{
			$faction = Factions::getFaction($player_id = intval($this->getActivePlayerId()));
//
			Factions::setImpulse($faction, $impulse = max(0, Factions::getImpulse($faction) - 1));
//
			$impulseCounter = Counters::getByType("impulse$faction")[0];
			$impulseCounter['location'] = $impulse;
			Counters::setLocation($impulseCounter['id'], $impulseCounter['location']);
//* -------------------------------------------------------------------------------------------------------- */
			self::notifyAllPlayers('placeCounter', '', ['counter' => $impulseCounter]);
//* -------------------------------------------------------------------------------------------------------- */
			if (Factions::getImpulse(Factions::INDIGENOUS) === 0 && Factions::getImpulse(Factions::SPANISH) === 0) return $this->gamestate->nextState('victoryCheckPhase');
		}
		$this->gamestate->nextState('startOfImpulse');
	}
	function stVictoryCheckPhase()
	{
		if (intval(self::getGameStateValue('turn')) !== 1)
		{
//* -------------------------------------------------------------------------------------------------------- */
			self::notifyAllPlayers('msg', '<span class="SSsubphase">${LOG}</span>', ['i18n' => ['LOG'], 'LOG' => clienttranslate('Victory check phase')]);
//* -------------------------------------------------------------------------------------------------------- */
			self::updateVP();
		}
		$this->gamestate->nextState('endOfRound');
	}
	function stCombatPhase()
	{
		self::updateVP();
//
		if (!Units::getCombatLocations()) $this->gamestate->nextState('continue');
		self::giveExtraTime($this->getActivePlayerId());
	}
	function stCombatSelectUnits()
	{
		$location = $this->globals->get('location');
//
		$attacker = $this->globals->get('attacker');
		$defender = $this->globals->get('defender');
//
		foreach (Factions::getAllDatas() as $faction => $player_id) if (sizeof(Units::getAtLocation($location, $faction)) === 0) return $this->gamestate->nextState('endOfCombat');
//* -------------------------------------------------------------------------------------------------------- */
		self::notifyAllPlayers('msg', clienttranslate('Combat occurs at <B>${location}</B>'), ['location' => $this->LOCATIONS[$location], 'i18n' => ['location']]);
//* -------------------------------------------------------------------------------------------------------- */

		$this->globals->set('hits', [$attacker => NULL, $defender => NULL]);
//
		$players = [];
		foreach (Factions::getAllDatas() as $faction => $player_id)
		{
			$units = Units::getAtLocation($location, $faction);
			if (sizeof($units) > 3)
			{
				$players[] = $player_id;
				self::giveExtraTime($player_id);
			}
			$this->globals->set("combatUnits/$faction", array_keys($units));
		}
//
		$this->gamestate->setPlayersMultiactive($players, 'combatRolls', true);
	}
	function stCombatRolls()
	{
		$location = $this->globals->get('location');
//
		$attacker = $this->globals->get('attacker');
		$defender = $this->globals->get('defender');
//
		$hits = $this->globals->get('hits');
		if ($hits[$attacker] === 'done' && $hits[$defender] === 'done') return $this->gamestate->nextState('combatRetreat');
//
		if ($this->globals->get('simultaneous')) $hits = [$attacker => 0, $defender => 0];
		else if (is_null($hits[$defender])) $hits[$defender] = 0;
		else if (is_null($hits[$attacker])) $hits[$attacker] = 0;
//
		foreach (['attacker' => $attacker, 'defender' => $defender] as $side => $faction)
		{
			if (!is_numeric($hits[Factions::other($faction)])) continue;
//
			foreach ($this->globals->get("combatUnits/$faction") as $id)
			{
				$unit = Units::get($id);
//
				$roll = bga_rand(1, 6);
//
				$modifier = 0;
//
// Combat Roll Modifiers:
//
// A) Dense Jungle areas: In those areas, Taíno, Caribe and Settler units (Green, Blue and Red) gain a +1 Combat Factor bonus when defending,
// while Spanish Soldiers, Columbus and Rebels (Yellow and White units) suffer a -1 Combat Factor penalty when attacking
//
				if (in_array($location, [3, 4, 5, 7]))
				{
					if ($side === 'defender' && in_array($unit['bag'], ['green', 'blue', 'red'])) $modifier += 1;
					if ($side === 'attacker' && in_array($unit['bag'], ['yellow', 'white'])) $modifier -= 1;
				}
//
// B) Palisade: It provides with a +1 Combat Factor when defending to the Spanish player
//
				if ($side === 'defender' && $faction === Factions::SPANISH && Counters::getAtLocation($location, 'palisades')) $modifier += 1;
//
// C) Citadel: Provide the Spanish units with a +2 Combat Factor when defending
//
				if ($side === 'defender' && $faction === Factions::SPANISH && Counters::getAtLocation($location, 'citadels')) $modifier += 2;
//
				if (!$unit['reduced'])
				{
					if ($side === 'attacker') $hit = $roll <= $this->UNITS[$faction][$unit['type']][ATTACK] + $modifier;
					if ($side === 'defender') $hit = $roll <= $this->UNITS[$faction][$unit['type']][DEFENSE] + $modifier;
				}
				else $hit = $roll <= $this->UNITS[$faction][$unit['type']][COMBINED] + $modifier;
//
				if ($roll === 1) $hit = true;
				if ($roll === 6) $hit = false;
//* -------------------------------------------------------------------------------------------------------- */
				if ($hit)
				{
//* -------------------------------------------------------------------------------------------------------- */
					if ($modifier > 0) self::notifyAllPlayers('combatRoll', clienttranslate('${UNIT}(+${modifier}) rolls ${DICE}, it is a hit'), ['UNIT' => $unit, 'DICE' => $roll, 'modifier' => +$modifier]);
					else if ($modifier < 0) self::notifyAllPlayers('combatRoll', clienttranslate('${UNIT}(-${modifier}) rolls ${DICE}, it is a hit'), ['UNIT' => $unit, 'DICE' => $roll, 'modifier' => -$modifier]);
					else self::notifyAllPlayers('combatRoll', clienttranslate('${UNIT} rolls ${DICE}, it is a hit'), ['UNIT' => $unit, 'DICE' => $roll]);
//* -------------------------------------------------------------------------------------------------------- */
					$hits[Factions::other($faction)]++;
				}
				else
				{
//* -------------------------------------------------------------------------------------------------------- */
					if ($modifier > 0) self::notifyAllPlayers('combatRoll', clienttranslate('${UNIT}(+${modifier}) rolls ${DICE}, it is a miss'), ['UNIT' => $unit, 'DICE' => $roll, 'modifier' => +$modifier]);
					else if ($modifier < 0) self::notifyAllPlayers('combatRoll', clienttranslate('${UNIT}(-${modifier}) rolls ${DICE}, it is a miss'), ['UNIT' => $unit, 'DICE' => $roll, 'modifier' => -$modifier]);
					else self::notifyAllPlayers('combatRoll', clienttranslate('${UNIT} rolls ${DICE}, it is a miss'), ['UNIT' => $unit, 'DICE' => $roll]);
//* -------------------------------------------------------------------------------------------------------- */
				}
			}
		}
		$this->globals->set('hits', $hits);
//
		$this->gamestate->nextState('combatHits');
//
	}
	function stCombatHits()
	{
		$hits = $this->globals->get('hits');
//
		$players = [];
		foreach (Factions::getAllDatas() as $faction => $player_id)
		{
			if ($hits[$faction] >= 0)
			{
				if ($hits[$faction] > 0)
				{
					$players[] = $player_id;
					self::giveExtraTime($player_id);
				}
				else $hits[$faction] = 'done';
			}
		}
		$this->globals->set('hits', $hits);
//
		$this->gamestate->setPlayersMultiactive($players, 'combatRolls', true);
	}
	function stCombatRetreat()
	{
		$location = $this->globals->get('location');
//
		$attacker = $this->globals->get('attacker');
		$defender = $this->globals->get('defender');
//
		$this->globals->delete('hits');
		$this->globals->set('simultaneous', true);
//
		if (Units::getAtLocation($location, $defender) && Units::getAtLocation($location, $attacker))
		{
			$player_id = Factions::getPlayer($defender);
			self::giveExtraTime($player_id);
			$this->gamestate->setPlayersMultiactive([$player_id], 'newRoundOfCombat', true);
		}
		else $this->gamestate->nextState('newRoundOfCombat');
	}
	function stEndOfCombat()
	{
		$location = $this->globals->get('location');
//
		$attacker = $this->globals->get('attacker');
		$defender = $this->globals->get('defender');
//
		$this->globals->delete('location');
		$this->globals->delete('defender');
		$this->globals->delete('attacker');
		$this->globals->delete("combatUnits/$attacker");
		$this->globals->delete("combatUnits/$defender");
//
		self::updateVP();
//
		if ($attacker === Factions::INDIGENOUS && sizeof(Units::getAtLocation($location, $defender)) === 0)
		{
			foreach (Counters::getAtLocation($location, 'palisades') as $counter)
			{
				Counters::setLocation($counter['id'], 'aside');
//* -------------------------------------------------------------------------------------------------------- */
				self::notifyAllPlayers('removeCounter', '', ['counter' => $counter]);
//* -------------------------------------------------------------------------------------------------------- */
				self::notifyAllPlayers('msg', clienttranslate('A palisade is destroyed at <B>${location}</B>'), ['location' => $this->LOCATIONS[$location], 'i18n' => ['location']]);
//* -------------------------------------------------------------------------------------------------------- */
			}
			if (sizeof(Units::getAtLocation($location, $attacker)) > 0)
			{
				foreach (Counters::getAtLocation($location, 'citadels') as $counter)
				{
					Counters::destroy($counter['id']);
//* -------------------------------------------------------------------------------------------------------- */
					self::notifyAllPlayers('removeCounter', '', ['counter' => $counter]);
//* -------------------------------------------------------------------------------------------------------- */
					self::notifyAllPlayers('msg', clienttranslate('A citadel is destroyed at <B>${location}</B>'), ['location' => $this->LOCATIONS[$location], 'i18n' => ['location']]);
//* -------------------------------------------------------------------------------------------------------- */
				}
			}
		}
		if ($attacker === Factions::SPANISH && sizeof(Units::getAtLocation($location, $defender)) === 0)
		{
			$counter = Counters::getByType('royalSupport')[0];
//* -------------------------------------------------------------------------------------------------------- */
			self::notifyAllPlayers('msg', clienttranslate('Spanish player is searching for gold'), []);
//* -------------------------------------------------------------------------------------------------------- */
			$roll = bga_rand(1, 6);
//* -------------------------------------------------------------------------------------------------------- */
			self::notifyAllPlayers('msg', clienttranslate('${player_name} rolls ${DICE}'), ['player_name' => self::getActivePlayerName(), 'DICE' => $roll]);
//* -------------------------------------------------------------------------------------------------------- */
			switch ($this->CARDS[self::getGameStateValue('fate')]['goldSearch'][$roll - 1])
			{
				case +1:
//* -------------------------------------------------------------------------------------------------------- */
					self::notifyAllPlayers('msg', clienttranslate('Gold Discovery: +1 Royal Support'), []);
//* -------------------------------------------------------------------------------------------------------- */
					$counter['location'] += 1;
					break;
				case 0:
//* -------------------------------------------------------------------------------------------------------- */
					self::notifyAllPlayers('msg', clienttranslate('No Discovery'), []);
//* -------------------------------------------------------------------------------------------------------- */
					break;
				case -1:
//* -------------------------------------------------------------------------------------------------------- */
					self::notifyAllPlayers('msg', clienttranslate('No Discovery: -1 Royal Support'), []);
//* -------------------------------------------------------------------------------------------------------- */
					$counter['location'] -= 1;
					break;
			}
			$counter['location'] = max(0, min($counter['location'], 10));
			Counters::setLocation($counter['id'], $counter['location']);
//* -------------------------------------------------------------------------------------------------------- */
			self::notifyAllPlayers('placeCounter', '', ['counter' => $counter]);
//* -------------------------------------------------------------------------------------------------------- */
		}
//
		$state = $this->globals->get('state');
		$this->gamestate->nextState($state);
	}
}
