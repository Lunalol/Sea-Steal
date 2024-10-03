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
// 1. Deploy the game board: Place the game board flat on a surface. Players should sit opposite each other, with the Indigenous player to the left of the board and the Spanish player to the right.
// 2. Preparing Military Unit Bags: Each player should prepare their respective military unit bags by following these steps:
		{
//		Indigenous Player
			{
//			Taíno Units: Place all Taíno into the green bag. Leave Leader units aside. 34 Taíno Units (Green)
				{
					Units::create('Indigenous', 'Leader', 'aside'); // 1 Leader (Caonabó)
					for ($i = 0; $i < 13; $i++) Units::create('Indigenous', 'Caciques', 'green'); // 13 Caciques
					for ($i = 0; $i < 20; $i++) Units::create('Indigenous', 'Naborias', 'green'); // 20 Naborias
				}
//			Caribe Units: Place all Carib units into the blue bag. // 33 Caribe Units (Blue)
				{
					for ($i = 0; $i < 13; $i++) Units::create('Indigenous', 'Calinagos', 'blue'); // 13 C alinagos
					for ($i = 0; $i < 20; $i++) Units::create('Indigenous', 'Tamas', 'blue'); // 20 Tamas
				}
//			Rebel Units: Place all Rebel units into the white bag. // 16 Rebelds units (White)
				{
					for ($i = 0; $i < 6; $i++) Units::create('Indigenous', 'Captains', 'white'); // 6 Captains
					for ($i = 0; $i < 10; $i++) Units::create('Indigenous', 'Troops', 'white'); // 10 Troops
				}
			}
//		Spanish Player
			{
//			Settler Units: Place all Settler units into the red bag. // 22 Settler Units (Red)
				{
					for ($i = 0; $i < 12; $i++) Units::create('Spanish', 'Pawns', 'red'); // 12 Pawns
					for ($i = 0; $i < 12; $i++) Units::create('Spanish', 'Scribes', 'red'); // 12 Scribes
				}
//			Soldier Units: Place all Soldier units into the yellow bag. Leave Leader units aside. // 27 Soldier Units (Yellow)
				{
					$colombus = Units::create('Spanish', 'Leader', 'aside'); // 1 Leader (Cristopher Columbus)
					for ($i = 0; $i < 7; $i++) Units::create('Spanish', 'Cavalry', 'yellow'); // 7 Cavalry
					for ($i = 0; $i < 7; $i++) Units::create('Spanish', 'Arquebusiers', 'yellow'); // 7 Arquebusiers
					for ($i = 0; $i < 12; $i++) Units::create('Spanish', 'Swordmen', 'yellow'); // 12 Swordmen
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
			Units::move($colombus, COLOMBUS);
			Units::draw('yellow', COLOMBUS);
			Units::draw('yellow', COLOMBUS);
			Units::draw('yellow', COLOMBUS);
		}
// 5. Attestors Counters: Place one attestor marker on each of the boxes that show "2 VP" next to areas #1, 2, 7, 8, and 9 on the board.
		foreach ([1, 2, 7, 8, 9] as $location) Counters::create('attestor', $location);
// 6. Turn Marker: Place the Turn Marker on the "1 (1492)" box on the Game Turn Track.
		Counters::create('turn', 1);
// 7. Victory Points Marker: Place the Victory Points Marker on the "0" box on the Victory Point Track.
		Counters::create('VP', 0);
// 8. Royal Support Marker: Place the Royal Support Marker on the "3" space on your Royal Support
		Counters::create('royalSupport', 3);
// 9. Divine Grace/Nature Spirits Marker: Place the Divine Grace/Nature Spirits Marker near the Divine Grace/Nature Spirits section
		Counters::create('divineGrace', 0);
		Counters::create('natureSpirit', 0);
// 10. Impulse markers: Place both factions Impulse Counters on the “0” box of the Impulse
		Counters::create('impulseSpanish', 0);
		Counters::create('impulseNatives', 0);
// 11. Setup and Selection of Event Cards. Before the game begins, each player should follow these steps for their Event Cards:
		foreach (['Indigenous', 'Spanish'] as $faction)
		{
//		a) Shuffle your 8 Event Cards belonging to your faction face down without looking at them and leave the card deck aside.
			$event = [1, 2, 3, 4, 5, 6, 7, 8];
			shuffle($event);
//		b) Draw the top 5 cards from the shuffled deck. These will be your Event Cards for the game.
			Factions::setStatus($faction, 'events', array_slice($event, 0, 5));
//		c) Set aside the remaining 3 Event Cards face down. These cards will not be used in the game.
		}
// 12. Prepare both Fate Card Decks.
		{
//		Create a deck with all 3 Fate Cards Game Turn 1 and shuffle.
			{
				$fateCardsTurn1 = [1, 2, 3];
				shuffle($fateCardsTurn1);
				$this->globals->set('fateCardsTurn1', $fateCardsTurn1);
			}
//		Then create another deck with all 20 Fate Cards Game Turn 2-6 and shuffle.
			{
				$fateCardsTurn26 = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20];
				shuffle($fateCardsTurn26);
				$this->globals->set('fateCardsTurn26', $fateCardsTurn26);
			}
		}
//
		$this->gamestate->changeActivePlayer(Factions::getPlayer('Spanish'));
		$this->gamestate->nextState('startOfGame');
	}
	function stStartOfGame()
	{

	}
}
