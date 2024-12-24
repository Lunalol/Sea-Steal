/* global g_gamethemeurl, g_archive_mode */

define(["dojo", "dojo/_base/declare"], function (dojo, declare)
{
	return declare("Movement", null,
	{
		constructor: function (bgagame, type, faction, location, navalDifficulties)
		{
			console.log('Movement constructor');
//
// Reference to BGA game
//
			this.bgagame = bgagame;
			this.board = bgagame.board;
//
			this.type = type;
			this.faction = faction;
			this.location = location;
//
			this.navalDifficulties = {[this.location]: navalDifficulties};
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
				if (this.type === RETREAT || dojo.query(`.SSunit[data-location='${from}']:not([data-faction='${this.faction}'])`, 'SSboard').length === 0)
				{
					let possible = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15];
					if (navalDifficulties || (this.faction === 'Indigenous' && this.rebels === 0)) possible = [((from + 1 - 1) % 15) + 1, ((from - 1 - 1 + 15) % 15) + 1];
//
					for (let to of possible)
					{
						if (from !== to)
						{
							if (this.type === RETREAT && dojo.query(`.SSunit[data-location='${to}']:not([data-faction='${this.faction}'])`, 'SSboard').length > 0) continue;
							if (this.type === INCURSION && dojo.query(`.SSunit[data-location='${to}']:not([data-faction='${this.faction}'])`, 'SSboard').length === 0) continue;
							if (!locations.has(to))
							{
								locations.add(to);
//
								const node = dojo.place(`<div id='SSaction-${to}' class='SSaction' style='position:absolute;width:10%;height:10%;border-radius:50%;background:${COLORS[this.faction]};filter:blur(25px);z-index:-1;'></div>`, 'SSboard');
								dojo.style(node, {left: `${BOARD[to][0] - 5}%`, top: `${BOARD[to][1] - 5}%`});
								dojo.connect(node, 'click', (event) => {
									dojo.stopEvent(event);
									if (this.type === INCURSION) return this.bgagame.bgaPerformAction('actIncursion', {from: from, to: to, shipsWear: JSON.stringify(this.navalDifficulties)});
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
		shipsWear: function (faction, location)
		{
			this.myDlg = new ebg.popindialog();
			this.myDlg.create('SSshipwWear');
			this.myDlg.setTitle(_("Reduce one unit to face naval difficulties"));
//
			let html = `<div id='SSunitContainer' class='SSunitContainer'>`;
			dojo.query(`.SSunit[data-faction='${faction}'][data-location='${location}']`, 'SSboard').forEach(node =>
			{
				const unit = node.dataset;
				html += `<div class='SSunit-dialog SSunit ${ +unit.reduced === 1 ? 'SSreduced ' : ''}SSselected' data-id='${unit.id}' data-faction='${unit.faction}' data-type='${unit.type}' data-location='${unit.location}'></div>`;
			});
			html += `</div>`;
//
			this.myDlg.setContent(html);
			this.myDlg.show();
//
			this.bgagame.connectClass('SSunit-dialog', 'click', (event) =>
			{
				dojo.stopEvent(event);
				this.myDlg.destroy();
				dojo.addClass('SSshipsWear', 'SSdisabled');
				this.navalDifficulties[location] = +event.currentTarget.dataset.id;
				this.show(!dojo.hasClass('SSshipsWear', 'SSdisabled'));
				dojo.query(`.SSunit[data-id='${event.currentTarget.dataset.id}']`, 'SSunitContainer').forEach((node) =>
				{
					if (dojo.hasClass(node, 'SSreduced'))
					{
						dojo.addClass(node, 'SSremoved');
						dojo.removeClass(node, 'SSselected');
					}
					else dojo.addClass(node, 'SSreduced');
				});
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
