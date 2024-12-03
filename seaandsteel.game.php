<?php
/**
 *
 * @author Lunalol - PERRIN Jean-Luc
 *
 */
//declare(strict_types=1);

define("ATTACK", 0);
define("DEFENSE", 1);
define("COMBINED", 2);
//
define("DIVINEGRACE", 1 << 0);
define("NATURESPIRITS", 1 << 1);
define("NAVALDIFFICULTIES", 1 << 2);
define("INDIGENOUSINTERNALCONFLIT", 1 << 3);
//
define("COLOMBUS", 0);
//
define("TURN", 110);
define("FATE", 111);

require_once(APP_GAMEMODULE_PATH . "module/table/table.game.php");
require_once('modules/PHP/gameStates.php');
require_once('modules/PHP/gameStateArguments.php');
require_once('modules/PHP/gameStateActions.php');
require_once('modules/PHP/gameUtils.php');
require_once('modules/PHP/events.php');
require_once('modules/PHP/Players.php');
require_once('modules/PHP/Factions.php');
require_once('modules/PHP/Counters.php');
require_once('modules/PHP/Units.php');

class seaandsteel extends Table
{
	use events;
	use gameStates;
	use gameStateArguments;
	use gameStateActions;
	use gameUtils;

	public function __construct()
	{
		parent::__construct();

		$this->initGameStateLabels([
			'turn' => TURN, 'fate' => FATE
		]);
	}
	public function getGameProgression()
	{
		return 0;
	}
	public function upgradeTableDb($from_version)
	{

	}
	protected function getAllDatas()
	{
		$player_id = intval(self::getCurrentPlayerId());
//
		$result = [];
//
		$result['LOCATIONS'] = $this->LOCATIONS;
		$result['CARDS'] = $this->CARDS;
//
		$result['players'] = $this->getCollectionFromDb("SELECT player_id, player_score score FROM player");
		$result['factions'] = array_flip(Factions::getAllDatas());
//
		$result['turn'] = intval(self::getGameStateValue('turn'));
		$result['fate'] = intval(self::getGameStateValue('fate'));
//
		$result['units'] = Units::getAllDatas();
		$result['counters'] = Counters::getAllDatas();
//
		if (!self::isSpectator())
		{
			$faction = Factions::getFaction($player_id);
			$result['hand'] = Factions::getStatus($faction, 'events');
			$result['event'] = $this->globals->get("event/$faction");
		}
//
		return $result;
	}
	protected function getGameName()
	{
		return "seaandsteel";
	}
	protected function setupNewGame($players, $options = [])
	{
		$gameinfos = self::getGameinfos();
		$admin = Players::getAdminPlayerID();
//
		switch (sizeof($players))
		{
//
			case 1:
//
// ONE player game
//
				Factions::create(Factions::INDIGENOUS, $admin);
				Factions::create(Factions::SPANISH, $admin);
//
				$players[$admin]['player_color'] = '000000';
//
				break;
//
			case 2:
//
// TWO players game
//
				$IDs = array_keys($players);
//
				switch ($options[101])
				{
//
					case 0:
//
// Factions are randomly chosen
//
						shuffle($IDs);
//
						break;
//
					case 1:
//
// Table administrator will play Indigenous
//
						$IDs = array_diff($IDs, [$admin]);
						array_unshift($IDs, $admin);
//
						break;
//
					case 2:
//
// Table administrator will play Spanish
//
						$IDs = array_diff($IDs, [$admin]);
						array_push($IDs, $admin);
//
						break;
//
					default: throw new BgaVisibleSystemException('Invalid factionsChoice: ' . $options[101]);
				}
//
				foreach ([Factions::INDIGENOUS, Factions::SPANISH] as $faction)
				{
					$ID = array_shift($IDs);
					Factions::create($faction, $ID);
					$players[$ID]['player_color'] = $gameinfos['player_colors'][$faction];
				}
//
				break;
//
			default: throw new BgaVisibleSystemException('Invalid number of players: ' . sizeof($players));
		}
//
		Players::create($players);
//
// Color Preferences
//
		if (sizeof($players) === 1)
		{
			$gameinfos = self::getGameinfos();
			self::reattributeColorsBasedOnPreferences($players, $gameinfos['player_colors']);
			self::reloadPlayersBasicInfos();
		}
//
	}
	protected function zombieTurn(array $state, int $active_player): void
	{
		$state_name = $state["name"];
//
		if ($state["type"] === "activeplayer")
		{
			switch ($state_name)
			{
				default:
					{
						$this->gamestate->nextState("zombiePass");
						break;
					}
			}
			return;
		}
		if ($state["type"] === "multipleactiveplayer")
		{
			$this->gamestate->setPlayerNonMultiactive($active_player, '');
			return;
		}
		throw new feException("Zombie mode not supported at this game state: \"{$state_name}\".");
	}
	function updateVP()
	{
		$counter = Counters::getByType('VP')[0];
		$counter['location'] = max(0, min(Factions::VP(), 20));
		Counters::setLocation($counter['id'], $counter['location']);
//* -------------------------------------------------------------------------------------------------------- */
		self::notifyAllPlayers('placeCounter', '', ['counter' => $counter]);
//* -------------------------------------------------------------------------------------------------------- */
	}
	function debug_board()
	{
		for ($i = 1; $i <= 6; $i++) Counters::create('turn', $i);
		for ($i = 0; $i <= 10; $i++) Counters::create('royalSupport', $i);
		for ($i = 0; $i <= 20; $i++) Counters::create('VP', $i);
		for ($i = 0; $i <= 5; $i++) Counters::create('impulseIndigenous', $i);
		for ($i = 0; $i <= 5; $i++) Counters::create('impulseSpanish', $i);
		Counters::create('shipsWear', 'shipsWear');
		Units::create(Factions::SPANISH, 'Leader', 'prisonInSpain');
	}
	function debug_scribe()
	{
		$units = array_filter(Units::getAllDatas(), fn($unit) => $unit['faction'] === Factions::SPANISH && $unit ['type'] === 'Leader');
		if ($units)
		{
			$unit = array_pop($units);
//* -------------------------------------------------------------------------------------------------------- */
			self::notifyAllPlayers('placeUnit', '', ['unit' => Units::get(Units::create(Factions::SPANISH, 'Scribes', $unit['location']))]);
			self::notifyAllPlayers('placeUnit', '', ['unit' => Units::get(Units::create(Factions::SPANISH, 'Scribes', $unit['location']))]);
//* -------------------------------------------------------------------------------------------------------- */
		}
	}
	function debug_shipsWear()
	{
//* -------------------------------------------------------------------------------------------------------- */
		self::notifyAllPlayers('placeCounter', '', ['counter' => Counters::get(Counters::create('shipsWear', 'shipsWear'))]);
//* -------------------------------------------------------------------------------------------------------- */
	}
	function debug_Columbus()
	{
		$columbus = Units::getColumbus();
		if ($columbus)
		{
			$columbus['location'] = 'prisonInSpain';
			Units::update($columbus);
//* -------------------------------------------------------------------------------------------------------- */
			self::notifyAllPlayers('placeUnit', '', ['unit' => $columbus]);
//* -------------------------------------------------------------------------------------------------------- */
		}
	}
}
