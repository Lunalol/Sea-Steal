<?php

/**
 *
 * @author Lunalol - PERRIN Jean-Luc
 *
 */
trait events
{
	function eventReinforcement($card)
	{
		switch ($card)
		{
//
			case 1:
//
				$this->globals->set('overStacking', false);
				$this->globals->set('simultaneous', true);
				return ['white', 'white', 'white', 'white'];
//
			case 2:
//
				$this->globals->set('overStacking', true);
				$this->globals->set('simultaneous', false);
				return ['blue', 'blue', 'blue', 'blue', 'green', 'aside'];
//
			case 4:
//
				$this->globals->set('overStacking', true);
				$this->globals->set('simultaneous', false);
				$roll = bga_rand(1, 6);
//* -------------------------------------------------------------------------------------------------------- */
				self::notifyAllPlayers('msg', clienttranslate('${player_name} rolls ${DICE}'), ['player_name' => self::getActivePlayerName(), 'DICE' => $roll]);
//* -------------------------------------------------------------------------------------------------------- */
				return array_fill(0, [2, 3, 3, 4, 4, 5][$roll - 1], 'white');
//
			case 5:
//
				$this->globals->set('overStacking', false);
				$this->globals->set('simultaneous', true);
				return ['blue', 'blue', 'blue', 'blue'];
//
			case 6:
//
				$this->globals->set('overStacking', true);
				$this->globals->set('simultaneous', true);
				return ['green', 'green', 'green', 'green', 'green'];
//
			case 7:
//
				$this->globals->set('overStacking', false);
				$this->globals->set('simultaneous', true);
				return ['blue', 'blue', 'blue', 'blue', 'blue', 'green', 'green', 'green'];
//
			case 9:
//
				$this->globals->set('overStacking', true);
				$this->globals->set('simultaneous', true);
				return['yellow', 'yellow', 'yellow', 'yellow', 'yellow', 'yellow', 'yellow', 'red', 'red', 'red', 'red', 'red', 'red'];
//
			case 11:
//
				$this->globals->set('overStacking', true);
				$this->globals->set('simultaneous', true);
				return['yellow', 'yellow', 'yellow', 'red', 'red'];
//
			case 13:
//
				$this->globals->set('overStacking', true);
				$this->globals->set('simultaneous', true);
				return['yellow', 'yellow', 'yellow', 'red', 'red', 'red'];
//
			case 14:
//
				$columbus = Units::getColumbus();
				if ($columbus && $columbus['location'] === 'prisonInSpain')
				{
					$columbus['location'] = 'event';
					Units::update($columbus);
//* -------------------------------------------------------------------------------------------------------- */
					self::notifyAllPlayers('placeUnit', '', ['unit' => $columbus]);
//* -------------------------------------------------------------------------------------------------------- */
				}
//
				$this->globals->set('overStacking', true);
				$this->globals->set('simultaneous', true);
				return['yellow', 'yellow', 'yellow', 'yellow', 'yellow', 'yellow', 'red', 'red', 'red'];
//
			case 15:
//
				$this->globals->set('overStacking', true);
				$this->globals->set('simultaneous', true);
				return['yellow', 'yellow', 'yellow', 'yellow', 'red', 'red', 'red'];
//
			case 16:
//
				$this->globals->set('overStacking', true);
				$this->globals->set('simultaneous', true);
				return['yellow', 'yellow', 'red', 'red', 'red', 'red'];
//
			case 3:
			case 8:
			case 10:
			case 12:
//
			default: throw new BgaVisibleSystemException("Not implemented card: $card");
		}
	}
	function eventLocations($card)
	{
		$turn = intval(self::getGameStateValue('turn'));
		$columbus = Units::getColumbus();
//
		$locations = [];
		foreach ([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15] as $location)
		{
			$locations[Factions::SPANISH][$location] = sizeof(Units::getAtLocation($location, Factions::SPANISH));
			$locations[Factions::INDIGENOUS][$location] = sizeof(Units::getAtLocation($location, Factions::INDIGENOUS));
		}
//
		switch ($card)
		{
			case 1: // Place in any area not controlled by Spanish units. Otherwise, place in areas with the least number of Spanish units
				return ['type' => 'any', 'locations' => array_keys($locations[Factions::SPANISH], min($locations[Factions::SPANISH]))];
			case 5: // Place in any area not controlled by Spanish units. Otherwise, place in areas with the least number of Spanish units
				return ['type' => 'any', 'locations' => array_keys($locations[Factions::SPANISH], min($locations[Factions::SPANISH]))];
			case 6: // Place in any area not controlled by Spanish units. Otherwise, place in areas with the least number of Spanish units
				return ['type' => 'one', 'locations' => array_keys($locations[Factions::SPANISH], min($locations[Factions::SPANISH]))];
//
			case 2: // Place in any area controlled by Spanish units
			case 4: // If game turn 2, do not place where Columbus is located
//
				if ($turn === 2 && $columbus && is_numeric($columbus['location']))
				{
					return ['type' => 'one', 'locations' => array_values(array_diff(array_keys(array_filter($locations[Factions::SPANISH], fn($count) => $count > 0)), [$columbus['location']]))];
				}
				return ['type' => 'one', 'locations' => array_keys(array_filter($locations[Factions::SPANISH], fn($count) => $count > 0))];
//
			case 7: return ['type' => 'all', 'locations' => [1, 2, 10]]; // Place at your choice between areas 1, 2 & 10
			case 9: return ['type' => 'one', 'locations' => $columbus && is_numeric($columbus['location']) ? [$columbus['location']] : [5, 6, 15]];
			case 11: return ['type' => 'one', 'locations' => Units::getAreas(Factions::SPANISH)];
			case 13: return ['type' => 'one', 'locations' => [10]];
			case 14: return ['type' => 'any', 'locations' => $columbus && is_numeric($columbus['location']) ? [$columbus['location']] : [5, 6]];
			case 15: return ['type' => 'one', 'locations' => Units::getAreas(Factions::SPANISH)];
			case 16: return ['type' => 'one', 'locations' => array_keys($locations[Factions::INDIGENOUS], min($locations[Factions::INDIGENOUS]))];
//
			case 3:
			case 8:
			case 10:
			case 12:
//
			default: throw new BgaVisibleSystemException("Not implemented card: $card");
		}
	}
	function eventResolve($card)
	{
		switch ($card)
		{
			case 15:
//
				$columbus = Units::getColumbus();

				if ($columbus)
				{
					$columbus['location'] = 'prisonInSpain';
					Units::update($columbus);
//* -------------------------------------------------------------------------------------------------------- */
					self::notifyAllPlayers('placeUnit', '', ['unit' => $columbus]);
//* -------------------------------------------------------------------------------------------------------- */
				}
				break;
		}
	}
}
