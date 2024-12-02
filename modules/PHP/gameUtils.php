<?php

/**
 *
 * @author Lunalol
 */
trait gameUtils
{
	function reduceUnit(array $unit, int $hits = 1): bool
	{
		if ($hits === 1 && intval($unit['reduced']) === 0)
		{
			$unit['reduced'] = 1;
			Units::update($unit);
//* -------------------------------------------------------------------------------------------------------- */
			self::notifyAllPlayers('placeUnit', clienttranslate('A unit is reduced to ${UNIT}'), ['unit' => $unit, 'UNIT' => $unit]);
//* -------------------------------------------------------------------------------------------------------- */
			return true;
		}
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
		return false;
	}
}
