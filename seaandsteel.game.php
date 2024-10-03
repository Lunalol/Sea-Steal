<?php
/**
 *
 * @author Lunalol - PERRIN Jean-Luc
 *
 */
declare(strict_types=1);

define("COLOMBUS", 0);

require_once(APP_GAMEMODULE_PATH . "module/table/table.game.php");
require_once('modules/PHP/gameStates.php');
require_once('modules/PHP/gameStateArguments.php');
require_once('modules/PHP/gameStateActions.php');
require_once('modules/PHP/Players.php');
require_once('modules/PHP/Factions.php');
require_once('modules/PHP/Counters.php');
require_once('modules/PHP/Units.php');

class seaandsteel extends Table
{
	use gameStates;
	use gameStateArguments;
	use gameStateActions;

	public function __construct()
	{
		parent::__construct();

		$this->initGameStateLabels([]);
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
		$result = [];
//
		$result["players"] = $this->getCollectionFromDb("SELECT player_id, player_score score FROM player");
		$result["units"] = Units::getAllDatas();
		$result["counters"] = Counters::getAllDatas();
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
				Factions::create('Indigenous', $admin);
				Factions::create('Spanish', $admin);
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
				foreach (['Indigenous', 'Spanish'] as $faction)
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
}
