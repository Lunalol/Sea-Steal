/* global g_gamethemeurl, g_archive_mode */

define(["dojo", "dojo/_base/declare"], function (dojo, declare)
{
	return declare("Movement", null,
	{
		constructor: function (bgagame, args)
		{
			console.log('Movement constructor');
//
// Reference to BGA game
//
			this.bgagame = bgagame;
			this.board = bgagame.board;
			this.faction = args.faction;
			this.location = args.location;
//
			this.navalDifficulties = {[this.location]: args.navalDifficulties};
//
			this.rebels = dojo.query(`.SSunit[data-faction='${this.faction}'][data-location='${this.location}'][data-type='Captains']`, 'SSboard').length;
			this.rebels += dojo.query(`.SSunit[data-faction='${this.faction}'][data-location='${this.location}'][data-type='Troops']`, 'SSboard').length;
//
			this.units = {};
//
		},
		show: function (navalDifficulties)
		{
			this.board.clearCanvas();
			dojo.query('.SSaction', 'SSboard').remove();
//
			const node = dojo.place(`<div id='SSaction-${this.location}' class='SSaction' style='position:absolute;width:10%;height:10%;border-radius:50%;background:${COLORS[this.faction]};filter:blur(25px);z-index:-1;'></div>`, 'SSboard');
			dojo.style(node, {left: `${BOARD[this.location][0] - 5}%`, top: `${BOARD[this.location][1] - 5}%`});
//
			const locations = new Set();
//
			dojo.query('.SSunit.SSselected', 'SSunitContainer').forEach((node) => {
				const from = +node.dataset.location;
				if (dojo.query(`.SSunit[data-location='${from}']:not([data-faction='${this.faction}'])`, 'SSboard').length === 0)
				{
					let possible = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15];
					if (navalDifficulties /*|| (this.faction === 'Indigenous' && this.rebels === 0)*/) possible = [((from + 1 - 1) % 15) + 1, ((from - 1 - 1 + 15) % 15) + 1];
//
					for (let to of possible)
					{
						if (from !== to)
						{
							if (!locations.has(to))
							{
								locations.add(to);
//
								const node = dojo.place(`<div id='SSaction-${to}' class='SSaction' style='position:absolute;width:10%;height:10%;border-radius:50%;background:${COLORS[this.faction]};filter:blur(25px);z-index:-1;'></div>`, 'SSboard');
								dojo.style(node, {left: `${BOARD[to][0] - 5}%`, top: `${BOARD[to][1] - 5}%`});
								dojo.connect(node, 'click', (event) => {
									dojo.query('.SSunit.SSselected', 'SSunitContainer').forEach((node) => {
										if (node.dataset.faction === 'Indigenous' && ![((from + 1 - 1) % 15) + 1, ((from - 1 - 1 + 15) % 15) + 1].includes(to))
										{
											if (!['Captains', 'Troops'].includes(node.dataset.type))
											{
												if (this.rebels === 0) return this.bgagame.showMessage(_('Not enough rebel units for Rebel-Assisted Taíno/Caribe Naval Movement'), 'info');
												this.rebels--;
											}
										}
										this.units[node.dataset.id] = Object.assign({}, node.dataset);
										node.dataset.location = to;
										this.bgagame.placeUnit(node.dataset);
										dojo.destroy(node);
//										if (dojo.query(`.SSunit[data-location='${to}']:not([data-faction='${this.faction}'])`, 'SSboard').length > 0) dojo.removeClass(node, 'SSselected');
										this.show();
									});
								});
//								this.board.arrow(from, to, '#45a1bf20');
							}
						}
					}
				}
			});
		},
		result: function ()
		{
			const result = {};
//
			for (let unit of Object.values(this.units))
			{
				const location = $(`SSunit-${unit.id}`).dataset.location;
				if (unit.location !== location) result[unit.id] = +location;
			}
//
			return result;
		},
		restore: function ()
		{
			this.board.clearCanvas();
			dojo.query('.SSaction', 'SSboard').remove();
			dojo.query('.SSunit.SSselected', 'SSunitContainer').removeClass('SSselected');
//
			for (let unit of Object.values(this.units)) this.bgagame.placeUnit(unit);
			this.bgagame.restoreServerGameState();
		}
	}
	);
});