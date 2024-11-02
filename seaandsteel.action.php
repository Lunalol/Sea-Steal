<?php
declare(strict_types=1);

class action_seaandsteel extends APP_GameAction
{
	public function __default()
	{
		if ($this->isArg("notifwindow"))
		{
			$this->view = "common_notifwindow";
			$this->viewArgs["table"] = $this->getArg("table", AT_posint, true);
		}
		else
		{
			$this->view = "seaandsteel_seaandsteel";
			$this->trace("Complete re-initialization of board game.");
		}
	}
}
