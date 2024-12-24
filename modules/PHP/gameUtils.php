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
		if ($unit['bag'])
		{
//* -------------------------------------------------------------------------------------------------------- */
			self::notifyAllPlayers('removeUnit', clienttranslate('${UNIT} is removed to ${BAG}'), ['unit' => $unit, 'UNIT' => $unit, 'BAG' => $unit['bag']]);
//* -------------------------------------------------------------------------------------------------------- */
			$unit['location'] = $unit['bag'];
			Units::update($unit);
		}
		else
		{
//* -------------------------------------------------------------------------------------------------------- */
			self::notifyAllPlayers('removeUnit', clienttranslate('${UNIT} is removed from the game'), ['unit' => $unit, 'UNIT' => $unit]);
//* -------------------------------------------------------------------------------------------------------- */
			$unit['location'] = 'aside';
			Units::update($unit);
		}
		return false;
	}
}
