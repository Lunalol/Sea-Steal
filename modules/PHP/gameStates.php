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
					for ($i = 0; $i < 5; $i++) Units::create(Factions::INDIGENOUS, 'Captains', 'white'); // 6 Captains
					for ($i = 0; $i < 9; $i++) Units::create(Factions::INDIGENOUS, 'Troops', 'white'); // 10 Troops
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
		$this->globals->set('turn', Counters::create('turn', 1));
// 7. Victory Points Marker: Place the Victory Points Marker on the "0" box on the Victory Point Track.
		$this->globals->set('VP', Counters::create('VP', 0));
// 8. Royal Support Marker: Place the Royal Support Marker on the "3" space on your Royal Support
		$this->globals->set('royalSupport', Counters::create('royalSupport', 3));
// 9. Divine Grace/Nature Spirits Marker: Place the Divine Grace/Nature Spirits Marker near the Divine Grace/Nature Spirits section
// 10. Impulse markers: Place both factions Impulse Counters on the “0” box of the Impulse
		$this->globals->set('impulseSpanish', Counters::create('impulseSpanish', 0));
		$this->globals->set('impulseIndigenous', Counters::create('impulseIndigenous', 0));
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
		for ($i = 0; $i < 10; $i++) Counters::create('palisades', 'aside');
		for ($i = 0; $i < 3; $i++) Counters::create('citadels', 'aside');
		$this->globals->set('shipsWear', Counters::create('shipsWear', 'aside'));
//
		$fateCards = [array_pop($fateCardsTurn26), array_pop($fateCardsTurn26), array_pop($fateCardsTurn26), array_pop($fateCardsTurn26), array_pop($fateCardsTurn26), array_pop($fateCardsTurn1)];
		$this->globals->set('fateCards', $fateCards);
//
		self::setGameStateInitialValue('turn', 0);
		self::setGameStateInitialValue('fate', 0);
//
		$this->globals->set('counter', 0);
		$this->globals->set('faction', Factions::SPANISH);
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
		$counter = Counters::get($this->globals->get('turn'));
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
			$flags = $this->CARDS[self::getGameStateValue('fate')]['flags'];
			if ($flags & DIVINEGRACE) $this->globals->set('counter', $counter = Counters::create('divineGrace', 0));
			if ($flags & NATURESPIRITS) $this->globals->set('counter', $counter = Counters::create('natureSpirits', 0));
//* -------------------------------------------------------------------------------------------------------- */
			self::notifyAllPlayers('placeCounter', '', ['counter' => Counters::get($counter)]);
//* -------------------------------------------------------------------------------------------------------- */
		}
		else
		{
			if (!$this->globals->get('used'))
			{
				$counter = Counters::get($this->globals->get('counter'));
				Counters::setType($counter['id'], $counter['type'] = ['divineGrace' => 'natureSpirits', 'natureSpirits' => 'divineGrace'][$counter['type']]);
//* -------------------------------------------------------------------------------------------------------- */
				self::notifyAllPlayers('placeCounter', '', ['counter' => $counter]);
//* -------------------------------------------------------------------------------------------------------- */
			}
		}
//* -------------------------------------------------------------------------------------------------------- */
		self::notifyAllPlayers('updateTurn', '<span class="SSphase">${LOG} ${turn}</span>', ['i18n' => ['LOG'], 'LOG' => clienttranslate('End of turn'), 'turn' => $turn]);
//* -------------------------------------------------------------------------------------------------------- */
		if ($turn === 6)
		{
//* -------------------------------------------------------------------------------------------------------- */
			self::notifyAllPlayers('msg', 'End of Game not implemented', []);
//* -------------------------------------------------------------------------------------------------------- */
			$this->gamestate->nextState('gameEnd');
		}
		else $this->gamestate->nextState('startOfRound');
	}
	function stFatePhase()
	{
//* -------------------------------------------------------------------------------------------------------- */
		self::notifyAllPlayers('msg', '<span class = "SSsubphase">${LOG}</span>', ['i18n' => ['LOG'], 'LOG' => clienttranslate('Fate phase')]);
//* -------------------------------------------------------------------------------------------------------- */
		$fateCards = $this->globals->get('fateCards');
		self::setGameStateValue('fate', $fate = array_pop($fateCards));
		$this->globals->set('fateCards', $fateCards);
//* -------------------------------------------------------------------------------------------------------- */
		self::notifyAllPlayers('fate', clienttranslate('A new fate card is revealed'), ['fate' => $fate]);
		self::notifyAllPlayers('msg', '${FATE}', ['FATE' => $fate]);
//* -------------------------------------------------------------------------------------------------------- */
		$this->globals->set('navalDifficulties', $navalDifficulties = boolval($this->CARDS[$fate]['flags'] & NAVALDIFFICULTIES));
//
		$counter = Counters::get($this->globals->get('shipsWear'));
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
					self::notifyAllPlayers('msg', clienttranslate('${player_name} rolls ${DICE}'), ['player_name' => self::getActivePlayerName(), 'DICE' => $roll]);
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
					$impulse = $this->CARDS[$fate]['impulse'];
//
					Factions::setImpulse(Factions::SPANISH, $impulse);
					$impulseSpanish = Counters::get($this->globals->get('impulseSpanish'));
					$impulseSpanish['location'] = $impulse;
					Counters::setLocation($impulseSpanish['id'], $impulseSpanish['location']);
//* -------------------------------------------------------------------------------------------------------- */
					self::notifyAllPlayers('placeCounter', '', ['counter' => $impulseSpanish]);
//* -------------------------------------------------------------------------------------------------------- */
					Factions::setImpulse(Factions::INDIGENOUS, $impulse);
					$impulseIndigenous = Counters::get($this->globals->get('impulseIndigenous'));
					$impulseIndigenous['location'] = $impulse;
					Counters::setLocation($impulseIndigenous['id'], $impulseIndigenous['location']);
//* -------------------------------------------------------------------------------------------------------- */
					self::notifyAllPlayers('placeCounter', '', ['counter' => $impulseIndigenous]);
//* -------------------------------------------------------------------------------------------------------- */
					$counter = Counters::get($this->globals->get('counter'));
					$counter['location'] = $impulse;
					Counters::setLocation($counter['id'], $counter['location']);
//* -------------------------------------------------------------------------------------------------------- */
					self::notifyAllPlayers('placeCounter', '', ['counter' => $counter]);
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
			self::notifyAllPlayers('msg', '<span class = "SSsubphase">${LOG}</span>', ['i18n' => ['LOG'], 'LOG' => clienttranslate('Event phase')]);
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
				$this->globals->set('faction', $faction);
				$this->gamestate->changeActivePlayer($player_id = Factions::getPlayer($faction));
				self::giveExtraTime($player_id);
//* -------------------------------------------------------------------------------------------------------- */
//				self::notifyAllPlayers('msg', '<span class = "SSsubphase">${LOG}</span>', ['i18n' => ['LOG'], 'LOG' => $this->CARDS[$card]['title']]);
//* -------------------------------------------------------------------------------------------------------- */
				$reinforcement = self::eventReinforcement($card);
//
				if ($faction === Factions::SPANISH && array_search('yellow', $reinforcement) !== false)
				{
					switch (Counters::get($this->globals->get('royalSupport')))
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
			self::notifyAllPlayers('msg', '<span class = "SSsubphase">${LOG}</span>', ['i18n' => ['LOG'], 'LOG' => clienttranslate('Recovery phase')]);
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
				$this->globals->set('faction', $faction);
				$this->gamestate->changeActivePlayer($player_id = Factions::getPlayer($faction));
				self::giveExtraTime($player_id);
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
			self::notifyAllPlayers('msg', '<span class = "SSsubphase">${LOG}</span>', ['i18n' => ['LOG'], 'LOG' => clienttranslate('Impulse phase')]);
//* -------------------------------------------------------------------------------------------------------- */
		}
//
		$this->globals->set('used', false);
		$this->globals->set('simultaneous', true);
		$this->globals->set('state', 'impulseCombatPhase');
//
		$this->gamestate->nextState('startOfImpulse');
	}
	function stStartOfImpulse()
	{
		if (intval(self::getGameStateValue('turn')) !== 1)
		{
			$this->globals->set('faction', $faction = Factions::other($this->globals->get('faction')));
			$this->gamestate->changeActivePlayer(Factions::getPlayer($faction));
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
		$player_id = Factions::getPlayer($faction = $this->globals->get('faction'));
//* -------------------------------------------------------------------------------------------------------- */
		self::notifyAllPlayers('msg', '<span class = "SSimpulse">${faction} <span>${LOG}</span></span>', ['i18n' => ['LOG'], 'LOG' => ['log' => clienttranslate('${player_name}\'s turn'), 'args' => ['player_name' => self::getPlayerNameById($player_id)]], 'faction' => $faction]);
//* -------------------------------------------------------------------------------------------------------- */
		self::giveExtraTime($player_id);
	}
	function stEndOfImpulse()
	{
		$faction = $this->globals->get('faction');
//
		if (intval(self::getGameStateValue('turn')) !== 1)
		{
			Factions::setImpulse($faction, $impulse = max(0, Factions::getImpulse($faction) - 1));
//
			$impulseCounter = Counters::getByType("impulse$faction")[0];
			$impulseCounter['location'] = $impulse;
			Counters::setLocation($impulseCounter['id'], $impulseCounter['location']);
//* -------------------------------------------------------------------------------------------------------- */
			self::notifyAllPlayers('placeCounter', '', ['counter' => $impulseCounter]);
//* -------------------------------------------------------------------------------------------------------- */
			$max = max(Factions::getImpulse(Factions::INDIGENOUS), Factions::getImpulse(Factions::SPANISH));
//
			$counter = Counters::get($this->globals->get('counter'));
			if ($counter['location'] > $max)
			{
				Counters::setLocation($counter['id'], $counter['location'] = $max);
//* -------------------------------------------------------------------------------------------------------- */
				self::notifyAllPlayers('placeCounter', '', ['counter' => $counter]);
//* -------------------------------------------------------------------------------------------------------- */
			}
			if ($max === 0) return $this->gamestate->nextState('victoryCheckPhase');
		}
//
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
	function stIncursion()
	{
		['attacker' => $faction, 'from' => $from, 'to' => $to, 'attempts' => $attempts] = $this->globals->get('incursion');
//
		$this->gamestate->changeActivePlayer($player_id = Factions::getPlayer($faction));
//
		if (++$attempts > 3 || sizeof(Units::getAtLocation($to)) === 0)
		{
			$this->globals->delete('incursion');
			return $this->gamestate->nextState('continue');
		}
//* -------------------------------------------------------------------------------------------------------- */
		if ($attempts === 1) self::notifyAllPlayers('msg', clienttranslate('First incursion occurs at <B>${location}</B>'), ['location' => $this->LOCATIONS[$to], 'i18n' => ['location']]);
//* -------------------------------------------------------------------------------------------------------- */
		if ($attempts === 2) return $this->gamestate->nextState('incursionContinue');
//* -------------------------------------------------------------------------------------------------------- */
		if ($attempts === 3) self::notifyAllPlayers('msg', clienttranslate('Second incursion occurs at <B>${location}</B>'), ['location' => $this->LOCATIONS[$to], 'i18n' => ['location']]);
//* -------------------------------------------------------------------------------------------------------- */
		$this->globals->set('dice', [0 => $roll = bga_rand(1, 6)]);
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
//* -------------------------------------------------------------------------------------------------------- */
		if ($modifier > 0) self::notifyAllPlayers('msg', clienttranslate('${player_name} rolls (+${modifier}) ${DICE}'), ['player_name' => self::getPlayerNameById($player_id), 'DICE' => $roll, 'modifier' => $modifier]);
		elseif ($modifier < 0) self::notifyAllPlayers('msg', clienttranslate('${player_name} rolls (-${modifier}) ${DICE}'), ['player_name' => self::getPlayerNameById($player_id), 'DICE' => $roll, 'modifier' => -$modifier]);
		else self::notifyAllPlayers('msg', clienttranslate('${player_name} rolls ${DICE}'), ['player_name' => self::getPlayerNameById($player_id), 'DICE' => $roll]);
//* -------------------------------------------------------------------------------------------------------- */
		$this->globals->set('incursion', ['attacker' => $faction, 'from' => $from, 'to' => $to, 'attempts' => $attempts]);
//
		$counter = Counters::get($this->globals->get('counter'));
		if ($faction === Factions::INDIGENOUS && $counter && $counter['type'] === 'natureSpirits' && $counter['location'] == Factions::getImpulse(Factions::INDIGENOUS)) return $this->gamestate->nextState('divineGraceNatureSpirits');
		if ($faction === Factions::SPANISH && $counter && $counter['type'] === 'divineGrace' && $counter['location'] == Factions::getImpulse(Factions::SPANISH)) return $this->gamestate->nextState('divineGraceNatureSpirits');
//
		$this->gamestate->nextState('incursionResolve');
	}
	function stIncursionResolve()
	{
		['attacker' => $faction, 'from' => $from, 'to' => $to, 'attempts' => $attempts] = $this->globals->get('incursion');
//
		$this->gamestate->changeActivePlayer($player_id = Factions::getPlayer($faction));
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
		if ($attempts === 1)
		{
			switch ($roll)
			{
				case 1:
				case 2:
//* -------------------------------------------------------------------------------------------------------- */
					self::notifyAllPlayers('msg', clienttranslate('<I>The defending faction loses one of their units with the highest Attack Factor.</I>'), []);
//* -------------------------------------------------------------------------------------------------------- */
					$this->gamestate->changeActivePlayer($player_id = Factions::getPlayer(Factions::other($faction)));
					self::giveExtraTime($player_id);
					break;
				case 3:
//* -------------------------------------------------------------------------------------------------------- */
					self::notifyAllPlayers('msg', clienttranslate('<I>The defending faction reduces one of their units with the highest Attack Factor by one level.</I>'), []);
//* -------------------------------------------------------------------------------------------------------- */
					$this->gamestate->changeActivePlayer($player_id = Factions::getPlayer(Factions::other($faction)));
					self::giveExtraTime($player_id);
					break;
				case 4:
				case 5:
//* -------------------------------------------------------------------------------------------------------- */
					self::notifyAllPlayers('msg', clienttranslate('<I>The Incursion attempt has no effect, and the Impulse ends.</I>'), []);
//* -------------------------------------------------------------------------------------------------------- */
					$this->globals->delete('incursion');
					return $this->gamestate->nextState('continue');
				case 6:
//* -------------------------------------------------------------------------------------------------------- */
					self::notifyAllPlayers('msg', clienttranslate('<I>The Incursion attempt has no effect on the defender, but the attacking player is affected. One unit with the highest Attack Factor is reduced by one level, and the Impulse ends. If all units are on their reduced sides, eliminate the unit with the highest combat factor.</I>'), []);
//* -------------------------------------------------------------------------------------------------------- */
					self::giveExtraTime($player_id);
					break;
			}
		}
		if ($attempts === 3)
		{
			switch ($roll)
			{
				case 1:
				case 2:
//* -------------------------------------------------------------------------------------------------------- */
					self::notifyAllPlayers('msg', clienttranslate('<I>The defending faction loses one of their units with the highest Attack Factor.</I>'), []);
//* -------------------------------------------------------------------------------------------------------- */
					$this->gamestate->changeActivePlayer($player_id = Factions::getPlayer(Factions::other($faction)));
					self::giveExtraTime($player_id);
					break;
				case 3:
//* -------------------------------------------------------------------------------------------------------- */
					self::notifyAllPlayers('msg', clienttranslate('<I>The defending faction reduces one of their units with the highest Attack Factor by one level.</I>'), []);
//* -------------------------------------------------------------------------------------------------------- */
					$this->gamestate->changeActivePlayer($player_id = Factions::getPlayer(Factions::other($faction)));
					self::giveExtraTime($player_id);
					break;
				case 4:
				case 5:
//* -------------------------------------------------------------------------------------------------------- */
					self::notifyAllPlayers('msg', clienttranslate('<I>The Incursion attempt has no effect on the defender, but the attacking unit (from the active faction) with the highest Attack Factor is reduced by one level, and the Impulse ends.</I>'), []);
//* -------------------------------------------------------------------------------------------------------- */
					self::giveExtraTime($player_id);
					break;
				case 6:
//* -------------------------------------------------------------------------------------------------------- */
					self::notifyAllPlayers('msg', clienttranslate('<I>The Incursion attempt has no effect on the defender, but the attacking player is affected. One unit with the highest Attack Factor is eliminated, and the Impulse ends. If all units are on their reduced sides, eliminate the unit with the highest combat factor.factor.</I>'), []);
//* -------------------------------------------------------------------------------------------------------- */
					self::giveExtraTime($player_id);
					break;
			}
		}
		return $this->gamestate->nextState('incursionInjuries');
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
		$this->globals->set('hits', $hits);
//
		$dices = [];
		foreach (['attacker' => $attacker, 'defender' => $defender] as $faction)
		{
			if (!is_numeric($hits[Factions::other($faction)])) continue;
			foreach ($this->globals->get("combatUnits/$faction") as $id) $dices[$id] = $roll = bga_rand(1, 6);
		}
		$this->globals->set('dice', $dices);
//
		$counter = Counters::get($this->globals->get('counter'));
		if ($attacker === Factions::INDIGENOUS && $counter && $counter['type'] === 'natureSpirits' && $counter['location'] == Factions::getImpulse(Factions::INDIGENOUS)) return $this->gamestate->nextState('divineGraceNatureSpirits');
		if ($attacker === Factions::SPANISH && $counter && $counter['type'] === 'divineGrace' && $counter['location'] == Factions::getImpulse(Factions::SPANISH)) return $this->gamestate->nextState('divineGraceNatureSpirits');
//
		$this->gamestate->nextState('combatResolve');
	}
	function stCombatResolve()
	{
		$location = $this->globals->get('location');
//
		$attacker = $this->globals->get('attacker');
		$defender = $this->globals->get('defender');
//
		$hits = $this->globals->get('hits');
		$dices = $this->globals->get('dice');
//
		foreach (['attacker' => $attacker, 'defender' => $defender] as $side => $faction)
		{
			if (!is_numeric($hits[Factions::other($faction)])) continue;
//
			foreach ($this->globals->get("combatUnits/$faction") as $id)
			{
				$unit = Units::get($id);
				$roll = $dices[$id];
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
	function stCombatDefenderRetreat()
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
			$this->gamestate->setPlayersMultiactive([$player_id], 'continue', true);
		}
		else $this->gamestate->nextState('continue');
	}
	function stCombatAttackerRetreat()
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
			$player_id = Factions::getPlayer($attacker);
			self::giveExtraTime($player_id);
			$this->gamestate->setPlayersMultiactive([$player_id], 'continue', true);
		}
		else $this->gamestate->nextState('continue');
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
//* -------------------------------------------------------------------------------------------------------- */
			self::notifyAllPlayers('msg', clienttranslate('Spanish player is searching for gold'), []);
//* -------------------------------------------------------------------------------------------------------- */
			$this->globals->set('dice', [0 => $roll = bga_rand(1, 6)]);
//* -------------------------------------------------------------------------------------------------------- */
			self::notifyAllPlayers('msg', clienttranslate('${player_name} rolls ${DICE}'), ['player_name' => self::getActivePlayerName(), 'DICE' => $roll]);
//* -------------------------------------------------------------------------------------------------------- */
			$counter = Counters::get($this->globals->get('counter'));
			if ($attacker === Factions::INDIGENOUS && $counter && $counter['type'] === 'natureSpirits' && $counter['location'] == Factions::getImpulse(Factions::INDIGENOUS)) return $this->gamestate->nextState('divineGraceNatureSpirits');
			if ($attacker === Factions::SPANISH && $counter && $counter['type'] === 'divineGrace' && $counter['location'] == Factions::getImpulse(Factions::SPANISH)) return $this->gamestate->nextState('divineGraceNatureSpirits');
//
			return self::stGoldSearch();
		}
//
		$state = $this->globals->get('state');
		$this->gamestate->nextState($state);
	}
	function stGoldSearch()
	{
		$dices = $this->globals->get('dice');
//
		$counter = Counters::get($this->globals->get('royalSupport'));
		switch ($this->CARDS[self::getGameStateValue('fate')]['goldSearch'][$dices[0] - 1])
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
		$state = $this->globals->get('state');
		$this->gamestate->nextState($state);
	}
}
