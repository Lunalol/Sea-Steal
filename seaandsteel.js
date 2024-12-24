/* global g_gamethemeurl, ebg, _, dijit */

const DELAY = 500;
//
define(["dojo", "dojo/_base/declare", "ebg/core/gamegui", "ebg/counter",
	g_gamethemeurl + "modules/JavaScript/constants.js",
	g_gamethemeurl + "modules/JavaScript/board.js",
	g_gamethemeurl + "modules/JavaScript/movement.js"
], function (dojo, declare)
{
	return declare("bgagame.seaandsteel", ebg.core.gamegui, {
		constructor: function ()
		{
			console.log('seaandsteel constructor');
//
		},
		setup: function (gamedatas)
		{
			console.log("Starting game setup", gamedatas);
//
// Translate
//
			this.BAGS = {yellow: _('Soldier'), red: _('Settler'), green: _('Taíno'), blue: _('Caribe'), white: _('Rebels')};
//
//			dojo.connect($('game_play_area'), 'click', () => this.restoreServerGameState());
//
// Card tooltips
//
			new dijit.Tooltip({connectId: "ebd-body", selector: ".SScard", showDelay: 1000, hideDelay: 0, getContent: (node) =>
				{
					let html = '';
					if (node.dataset.id in gamedatas.CARDS && node.dataset.id <= 18)
					{
						html += `<div style='display:flex;flex-direction:row;'>`;
						html += `<div class='SScard' style='width:350px;background-position-x:${node.style['background-position-x']}'></div>`;
						html += `<div style='width:300px;line-height:150%;'>`;
						html += `<H2>${gamedatas.CARDS[node.dataset.id].title}</H2>`;
						html += `<HR>`;
						html += `<div style='margin: 10px 0px;'>${_(gamedatas.CARDS[node.dataset.id][1])}</div>`;
						html += `<div style='margin: 10px 0px;'>${_(gamedatas.CARDS[node.dataset.id][2]).replaceAll('. ', '.<BR>')}</div>`;
						html += `<HR>`;
						html += `<div><I>${dojo.string.substitute(_('Reinforcement value: ${value}'), {value: gamedatas.CARDS[node.dataset.id][0]})}</I></div>`;
						html += `</div>`;
						html += `</div>`;
					}
					return html;
				}});
//
			this.board = new Board(this);
//
// Fate card
//
			dojo.place(`<div class='SSfate'><div class='SScard'></div></div>`, 'player_boards');
			dojo.place(`<div class='SSfate'><div class='SScard'></div></div>`, 'SSboard');
//
			if ('fate' in gamedatas) this.fate(gamedatas.fate);
//
// Event cards
//
			if ('hand' in gamedatas) this.hand(gamedatas.hand);
//
			this.connectClass('SScard', 'click', (event) => {
				dojo.stopEvent(event);
				if (this.isCurrentPlayerActive() && dojo.hasClass(event.currentTarget, 'SSselectable'))
				{
					dojo.query('.SShand>.SScard', 'SSplayArea').removeClass('SSselected');
					dojo.addClass(event.currentTarget, 'SSselected');
				}
			});
//
// Units & Counters
//
			if ('units' in gamedatas) for (let unit of Object.values(gamedatas.units)) this.placeUnit(unit);
			if ('counters' in gamedatas) for (let counter of Object.values(gamedatas.counters)) this.placeCounter(counter);
//
			this.setupNotifications();
//
			console.log("Ending game setup");
		},
		onEnteringState: function (stateName, state)
		{
			console.log('Entering state: ' + stateName, state.args);
//
// Selected event cards
//
			if ('event' in this.gamedatas) dojo.query(`.SScard[data-id='${this.gamedatas.event}']`, 'SShand').addClass('SSselected');
//
			if (!state.args) return;
//
			switch (stateName)
			{
				case 'activation':
//
					{
						for (let location of state.args.locations)
						{
							const node = dojo.place(`<div id='SSaction-${location}' class='SSaction' style='background:${COLORS[state.args.faction]};filter:blur(25px);z-index:-1;'></div>`, 'SSboard');
							dojo.style(node, {left: `${BOARD[location][0] - 5}%`, top: `${BOARD[location][1] - 5}%`});
							dojo.connect(node, 'click', (event) => {
								dojo.stopEvent(event);
								if (this.isCurrentPlayerActive()) this.bgaPerformAction('actActivation', {location: location}).then(() => dojo.query('.SSaction').remove());
							});
						}
					}
					break;
//
				case 'incursionFrom':
//
					{
						for (let location of state.args.locations)
						{
							const node = dojo.place(`<div id='SSaction-${location}' class='SSaction' style='background:${COLORS[state.args.faction]};filter:blur(25px);z-index:-1;'></div>`, 'SSboard');
							dojo.style(node, {left: `${BOARD[location][0] - 5}%`, top: `${BOARD[location][1] - 5}%`});
							dojo.connect(node, 'click', (event) => {
								dojo.stopEvent(event);
								this.setClientState('incursionTo', {descriptionmyturn: _('${you} can select which area to attack'), args: {faction: state.args.faction, location: location, navalDifficulties: state.args.navalDifficulties}});
							});
						}
					}
					break;
//
				case 'incursionTo':
//
					{
						const node = dojo.place(`<div id='SSaction-${state.args.location}' class='SSaction' style='left:${BOARD[state.args.location][0] - 5}%;top:${BOARD[state.args.location][1] - 5}%;background:${COLORS[state.args.faction]};filter:blur(25px);z-index:-1;'></div>`, 'SSboard');
					}
					break;
//
				case 'incursionInjuries':
//
					{
						if (state.args.hits === 1) this.gamedatas.gamestate.descriptionmyturn = _('${you} must reduce one of your units with the highest Attack Factor');
						if (state.args.hits === 2) this.gamedatas.gamestate.descriptionmyturn = _('${you} must eliminate one of your units with the highest Attack Factor');
						this.updatePageTitle();
//
						const node = dojo.place(`<div id='SSaction-${state.args.location}' class='SSaction' style='left:${BOARD[state.args.location][0] - 5}%;top:${BOARD[state.args.location][1] - 5}%;background:${COLORS[state.args.faction]};filter:blur(25px);z-index:-1;'></div>`, 'SSboard');
					}
					break;
//
				case 'buildPalisades':
//
					{
						for (let location of state.args.palisades)
						{
							const node = dojo.place(`<div id='SSaction-${location}' class='SSaction' style='background:${COLORS[state.args.faction]};filter:blur(25px);z-index:-1;'></div>`, 'SSboard');
							dojo.style(node, {left: `${BOARD[location][0] - 5}%`, top: `${BOARD[location][1] - 5}%`});
							dojo.connect(node, 'click', (event) => {
								dojo.stopEvent(event);
								if (this.isCurrentPlayerActive())
								{
									if (![3, 4, 5, 7].includes(location))
									{
										if (dojo.query(`.SScounter.SSprovisional[data-location='${location}'][data-type='palisades']`, 'SSboard').remove().length === 0)
										{
											if (dojo.query(`.SScounter.SSprovisional[data-type='palisades']`, 'SSboard').length < 3)
											{
												const provisional = dojo.place(`<div class='SScounter SSprovisional' data-type='palisades' data-location='${location}'></div>`, 'SSboard');
												dojo.style(provisional, {left: `${BOARD[location][0] - 1.5}%`, top: `${BOARD[location][1] - 1.5}%`});
											}
										}
										$('SSbuildPalisades').innerHTML = dojo.string.substitute(_('Build ${N} palisade(s)'), {N: dojo.query(`.SScounter.SSprovisional[data-type='palisades']`, 'SSboard').length});
									}
								}
							});
						}
					}
					break;
//
				case 'buildCitadels':
//
					{
						for (let location of state.args.citadels)
						{
							const node = dojo.place(`<div id='SSaction-${location}' class='SSaction' style='background:${COLORS[state.args.faction]};filter:blur(25px);z-index:-1;'></div>`, 'SSboard');
							dojo.style(node, {left: `${BOARD[location][0] - 5}%`, top: `${BOARD[location][1] - 5}%`});
							dojo.connect(node, 'click', (event) => {
								dojo.stopEvent(event);
								if (this.isCurrentPlayerActive())
								{
									if (![3, 4, 5, 7].includes(location))
									{
										if (dojo.query(`.SScounter.SSprovisional[data-location='${location}'][data-type='citadels']`, 'SSboard').remove().length === 0)
										{
											if (dojo.query(`.SScounter.SSprovisional[data-type='citadels']`, 'SSboard').length < 2)
											{
												const provisional = dojo.place(`<div class='SScounter SSprovisional' data-type='citadels' data-location='${location}'></div>`, 'SSboard');
												dojo.style(provisional, {left: `${BOARD[location][0] - 1.5}%`, top: `${BOARD[location][1] - 1.5}%`});
											}
										}
										$('SSbuildCitadels').innerHTML = dojo.string.substitute(_('Build ${N} citadel(s)'), {N: dojo.query(`.SScounter.SSprovisional[data-type='citadels']`, 'SSboard').length});
									}
								}
							});
						}
					}
					break;
//
				case 'movementPhase':
//
					{
						const node = dojo.place(`<div id='SSaction-${state.args.location}' class='SSaction' style='background:${COLORS[state.args.faction]};filter:blur(25px);z-index:-1;'></div>`, 'SSboard');
						dojo.style(node, {left: `${BOARD[state.args.location][0] - 5}%`, top: `${BOARD[state.args.location][1] - 5}%`});
					}
					break;
//
				case 'eventCombatPhase':
				case 'reinforcementCombatPhase':
				case 'impulseCombatPhase':
//
					{
						for (let location of state.args.locations)
						{
							const node = dojo.place(`<div id='SSaction-${location}' class='SSaction' style='position:absolute;width:10%;height:10%;border-radius:50%;background:${COLORS[state.args.faction]};filter:blur(25px);z-index:-1;'></div>`, 'SSboard');
							dojo.style(node, {left: `${BOARD[location][0] - 5}%`, top: `${BOARD[location][1] - 5}%`});
							dojo.connect(node, 'click', (event) => {
								dojo.stopEvent(event);
								if (this.isCurrentPlayerActive()) this.bgaPerformAction('actCombat', {location: location}).then(() => dojo.query('.SScombat').remove());
							});
						}
					}
					break;
//
				case 'combatSelectUnits':
				case 'combatHits':
//
					{
						const node = dojo.place(`<div id='SSaction-${location}' class='SSaction' style='background:${COLORS['combat']};filter:blur(25px);z-index:-1;'></div>`, 'SSboard');
						dojo.style(node, {left: `${BOARD[state.args.location][0] - 5}%`, top: `${BOARD[state.args.location][1] - 5}%`});
					}
					break;
//
				case 'combatRetreat':
//
					{
						const node = dojo.place(`<div id='SSaction-${state.args.location}' class='SSaction' style='background:${COLORS[state.args._private.faction]};filter:blur(25px);z-index:-1;'></div>`, 'SSboard');
						dojo.style(node, {left: `${BOARD[state.args.location][0] - 5}%`, top: `${BOARD[state.args.location][1] - 5}%`});
					}
					break;
//
			}
		},
		onLeavingState: function (stateName)
		{
			console.log('Leaving state: ' + stateName);
//
			this.board.clearCanvas();
//
			dojo.query('.SScard', 'SSplayArea').removeClass('SSselectable SSselected');
//
			dojo.query('.SSunit', 'SSboard').removeClass('SSdisabled SSselected');
//
			dojo.query('.SSunit.SSprovisional', 'SSboard').forEach((node) => {
				node.remove();
				this.arrange(node.dataset.location, node.dataset.faction);
			});
//
			dojo.query('.SScounter.SSprovisional', 'SSboard').remove();
//
			dojo.query('.SSaction', 'SSboard').remove();
		}
		,
		onUpdateActionButtons: function (stateName, args)
		{
			console.log('onUpdateActionButtons: ' + stateName, args);
//
			if (this.isCurrentPlayerActive())
			{
				switch (stateName)
				{
//
					case 'startOfGame':
//
						{
							this.addActionButton('SSfate', _('Draw and reveal first fate card'), (event) => {
								dojo.stopEvent(event);
								this.bgaPerformAction('actStartOfGame');
							});
						}
						break;
//
					case 'secretChoice':
//
						{
							dojo.query('.SShand>.SScard', 'SSplayArea').addClass('SSselectable');
//
							this.addActionButton('SSsecretChoice', _('Play event card'), (event) => {
								dojo.stopEvent(event);
								const nodes = dojo.query('.SShand>.SScard.SSselected', 'SSplayArea');
								if (nodes.length === 1) this.bgaPerformAction('actSecretChoice', {card: nodes[0].dataset.id});
							});
						}
						break;
//
					case 'eventResolution':
//
						{
							dojo.query(`.SShand>.SScard[data-id='${args.card}']`, 'SSplayArea').addClass('SSselected');
//
							const container = dojo.place(`<div id='SSunitContainer' class='SSunitContainer'></div>`, 'generalactions');
							for (let unit of Object.values(args.units))
							{
								const node = dojo.place(`<div class='SSunit ${ +unit.reduced === 1 ? 'SSreduced ' : ''}SSselected' data-id='${unit.id}' data-faction='${unit.faction}' data-type='${unit.type}' data-location='${unit.location}'></div>`, container);
								dojo.connect(node, 'click', (event) => {
									dojo.stopEvent(event);
//									$(`SSunit-${unit.id}`).scrollIntoView({block: 'center', inline: 'center'});
									dojo.toggleClass(node, 'SSselected');
									if (event.detail > 1) dojo.query('.SSunit', 'SSunitContainer').addClass('SSselected');
								});
								if (!args.overStacking) dojo.toggleClass(node, 'SSselected');
							}
//
							for (let location of args.event.locations)
							{
								const node = dojo.place(`<div id='SSaction-${location}' class='SSaction' style='position:absolute;width:10%;height:10%;border-radius:50%;background:${COLORS[args.faction]};filter:blur(25px);z-index:-1;'></div>`, 'SSboard');
								dojo.style(node, {left: `${BOARD[location][0] - 5}%`, top: `${BOARD[location][1] - 5}%`});
								dojo.connect(node, 'click', (event) => {
									dojo.stopEvent(event);
									dojo.query('.SSunit.SSselected', 'SSunitContainer').forEach((node) => {
										dojo.addClass(this.placeUnit({id: node.dataset.id, faction: node.dataset.faction, type: node.dataset.type, location: location}), 'SSprovisional');
										dojo.destroy(node);
									});
								});
							}
//
							this.addActionButton('SSreset', _('Reset'), (event) => {
								dojo.stopEvent(event);
								this.restoreServerGameState();
							}, null, false, 'gray');
//
							this.addActionButton('SSdone', _('Done'), (event) => {
								dojo.stopEvent(event);
								const units = dojo.query('.SSunit.SSprovisional', 'SSboard').reduce((L, node) => {
									L[node.dataset.id] = node.dataset.location;
									return L;
								}, {});
								if (!args.overStacking && this.overstacking(Object.keys(units))) return this.showMessage(_('Overstacking'), 'info');
								const nodes = dojo.query('.SSunit', 'SSunitContainer');
								if (nodes.length === 0) this.bgaPerformAction('actEventResolution', {units: JSON.stringify(units)});
								else this.confirmationDialog('All units are not deployed', () => this.bgaPerformAction('actEventResolution', {units: JSON.stringify(units)}));
							});
						}
						break;
//
					case 'reinforcement':
//
						{
							const container = dojo.place(`<div id='SSunitContainer' class='SSunitContainer'></div>`, 'generalactions');
							for (let unit of Object.values(args.units))
							{
								const node = dojo.place(`<div class='SSunit ${ +unit.reduced === 1 ? 'SSreduced ' : ''}SSselected' data-id='${unit.id}' data-faction='${unit.faction}' data-type='${unit.type}' data-location='${unit.location}'></div>`, container);
								dojo.connect(node, 'click', (event) => {
									dojo.stopEvent(event);
//									$(`SSunit-${unit.id}`).scrollIntoView({block: 'center', inline: 'center'});
									dojo.toggleClass(node, 'SSselected');
									if (event.detail > 1) dojo.query('.SSunit', 'SSunitContainer').addClass('SSselected');
								});
								dojo.toggleClass(node, 'SSselected');
							}
//
							for (let [location, priority] of Object.entries(args.locations))
							{
								const node = dojo.place(`<div id='SSaction-${location}' class='SSaction' style='position:absolute;width:10%;height:10%;border-radius:50%;background:${COLORS[args.faction]};filter:blur(25px);z-index:-1;'></div>`, 'SSboard');
								dojo.style(node, {left: `${BOARD[location][0] - 5}%`, top: `${BOARD[location][1] - 5}%`, opacity: [1, .75, .5][priority]});
								dojo.connect(node, 'click', (event) => {
									dojo.stopEvent(event);
									dojo.query('.SSunit.SSselected', 'SSunitContainer').forEach((node) => {
										dojo.addClass(this.placeUnit({id: node.dataset.id, faction: node.dataset.faction, type: node.dataset.type, location: location}), 'SSprovisional');
										dojo.destroy(node);
										dojo.toggleClass('SSreinforcement', 'disabled', dojo.query('.SSunit:not(.SSreduced)', 'SSheal').length + dojo.query('.SSunit.SSprovisional', 'SSboard').length > args.reinforcement);
									});
								});
							}
//
							if (Object.values(args.units).length === 0)
							{
								this.bags = {};
								for (let bag of args.bags)
								{
									this.bags[bag] = 0;
									this.addActionButton(`SSbag-${bag}`, `${this.BAGS[bag]} (${this.bags[bag]})`, (event) => {
										dojo.stopEvent(event);
										if (this.bags[bag] < 1 + Math.min(...Object.values(this.bags)) && Object.values(this.bags).reduce((s, v) => s + v, 0) < args.reinforcement)
										{
											this.bags[bag] += 1;
											event.currentTarget.innerHTML = `${this.BAGS[bag]} (${this.bags[bag]})`;
										}
										else
										{
											this.bags[bag] = 0;
											event.currentTarget.innerHTML = `${this.BAGS[bag]} (${this.bags[bag]})`;
										}
										dojo.toggleClass('SSreinforcement', 'disabled', Object.values(this.bags).reduce((s, v) => s + v, 0) !== args.reinforcement);
									});
									if (['yellow', 'white'].includes(bag)) dojo.style(`SSbag-${bag}`, {background: bag, color: 'black'});
									else dojo.style(`SSbag-${bag}`, {background: bag, color: 'white'});
								}
							}
							else
							{
								const reduced = dojo.query(`.SSunit.SSreduced[data-faction='${args.faction}']`, 'SSboard');
//
								if (reduced.length > 0)
								{
									this.addActionButton('SSheal', _('Flip to full strength'), (event) => {
										dojo.stopEvent(event);
									});
//
									const container = dojo.place(`<div id='SSunitContainer' class='SSunitContainer'></div>`, 'SSheal');
//
									reduced.removeClass('SSdisabled').forEach(node =>
									{
										const unit = node.dataset;
										node = dojo.place(`<div id='SSunit-heal-${unit.id}' class='SSunit SSreduced SSselected' data-id='${unit.id}' data-faction='${unit.faction}' data-type='${unit.type}' data-location='${unit.location}'></div>`, container);
										dojo.connect(node, 'click', (event) => {
											dojo.toggleClass(event.currentTarget, 'SSreduced');
											dojo.toggleClass('SSreinforcement', 'disabled', dojo.query('.SSunit:not(.SSreduced)', 'SSheal').length + dojo.query('.SSunit.SSprovisional', 'SSboard').length > args.reinforcement);
										});
										this.addTooltipHtml(node.id, _(this.gamedatas.LOCATIONS[unit.location]));
									});
								}
							}
//
							this.addActionButton('SSreset', _('Reset'), (event) => {
								dojo.stopEvent(event);
								this.restoreServerGameState();
							}, null, false, 'gray');
//
							this.addActionButton('SSreinforcement', _('Reinforcement'), (event) => {
								dojo.stopEvent(event);
								if (Object.values(args.units).length === 0)
								{
									if (Object.values(this.bags).reduce((s, v) => s + v, 0) === args.reinforcement)
										this.bgaPerformAction('actReinforcement', {reinforcement: JSON.stringify(this.bags)});
								}
								else
								{
									let units = dojo.query('.SSunit.SSprovisional', 'SSboard').reduce((L, node) => {
										L[node.dataset.id] = node.dataset.location;
										return L;
									}, {});
									if (this.overstacking(Object.keys(units))) return this.showMessage(_('Overstacking'), 'info');
//
									units = dojo.query('.SSunit:not(.SSreduced)', 'SSheal').reduce((L, node) => {
										L[node.dataset.id] = 'heal';
										return L;
									}, units);
//
									const nodes = dojo.query('.SSunit', 'SSunitContainer');
									if (nodes.length === 0) this.bgaPerformAction('actReinforcement', {units: JSON.stringify(units)});
									else this.confirmationDialog('All units are not deployed', () => this.bgaPerformAction('actReinforcement', {units: JSON.stringify(units)}));
								}
							});
							dojo.toggleClass('SSreinforcement', 'disabled', Object.values(args.units).length === 0);
						}
						break;
//
					case 'action':
//
						{
							this.addActionButton('SSactivation', _('Activate an area'), (event) => {
								dojo.stopEvent(event);
								this.setClientState('activation', {descriptionmyturn: _('${you} can activate an area')});
							});
//
							this.addActionButton('SSincursion', _('Incursion'), (event) => {
								dojo.stopEvent(event);
								this.setClientState('incursionFrom', {descriptionmyturn: _('${you} can select an area for incursion')});
							});
//
							if (args.palisades) this.addActionButton('SSbuildPalisades', _('Build palisades'), (event) => {
									dojo.stopEvent(event);
									this.setClientState('buildPalisades', {descriptionmyturn: _('${you} can build up to 3 palisades')});
								});
//
							if (args.citadels) this.addActionButton('SSbuildCitadels', _('Build citadels'), (event) => {
									dojo.stopEvent(event);
									this.setClientState('buildCitadels', {descriptionmyturn: _('${you} can build up to 2 citadels')});
								});
//
							this.addActionButton('SSpass', _('Do nothing (TO BE REMOVED)'), (event) => {
								dojo.stopEvent(event);
								this.bgaPerformAction('actPass');
							}, null, false, 'red');
						}
						break;
//
					case 'activation':
//
						{
							this.addActionButton('SScancel', _('Cancel'), (event) => {
								dojo.stopEvent(event);
								this.restoreServerGameState();
							}, null, false, 'gray');
						}
						break;
//
					case 'incursionFrom':
//
						{
							this.addActionButton('SScancel', _('Cancel'), (event) => {
								dojo.stopEvent(event);
								this.restoreServerGameState();
							}, null, false, 'gray');
						}
						break;
//
					case 'incursionTo':
//
						{
							this.movement = new Movement(this, INCURSION, args.faction, args.location, args.navalDifficulties);
//
							const container = dojo.place(`<div id='SSunitContainer' class='SSunitContainer'></div>`, 'generalactions');
							dojo.query(`.SSunit[data-location='${args.location}']`, 'SSboard').forEach(node =>
							{
								const unit = node.dataset;
//
								node = dojo.place(`<div class='SSunit ${ +unit.reduced === 1 ? 'SSreduced ' : ''}SSselected' data-id='${unit.id}' data-faction='${unit.faction}' data-type='${unit.type}' data-location='${unit.location}'></div>`, container);
								dojo.connect(node, 'click', (event) => {
									dojo.stopEvent(event);
								});
//								dojo.toggleClass(node, 'SSselected');
							});
//
							const node = dojo.place(`<div id='SSshipsWear' class='SScounter action-button' data-type='shipsWear'></div>`, 'generalactions');
							dojo.toggleClass(node, 'SSdisabled', args.navalDifficulties !== true);
							dojo.connect(node, 'click', (event) =>
							{
								dojo.stopEvent(event);
								if (!dojo.hasClass(node, 'SSdisabled')) this.movement.shipsWear(args.faction, args.location);
							}
							);
							this.addTooltip(node.id,
									_('Naval difficulties: both players are affected but the Indigenous player is only affected if moving to a non-contiguous area by using rebel units. For each naval movement to a non-contiguous area (including drag and drop movements) the player should reduce one unit in the origin area from its full-strength to its reduced strength side or eliminate one unit if it is by its reduced side already.'),
									_('Ships wear')
									);
//
							this.movement.show(!dojo.hasClass('SSshipsWear', 'SSdisabled'));
//
							this.addActionButton('SScancel', _('Cancel'), (event) => {
								dojo.stopEvent(event);
								this.restoreServerGameState();
							}, null, false, 'gray');
						}
						break;
//
					case 'incursionInjuries':
//
						const container = dojo.place(`<div id='SSunitContainer' class='SSunitContainer'></div>`, 'generalactions');
						for (let unit of Object.values(args.units))
						{
							node = dojo.place(`<div class='SSunit ${ +unit.reduced === 1 ? 'SSreduced ' : ''}SSselected' data-id='${unit.id}' data-faction='${unit.faction}' data-type='${unit.type}' data-location='${unit.location}'></div>`, container);
							dojo.connect(node, 'click', (event) => {
								dojo.stopEvent(event);
//								$(`SSunit-${unit.id}`).scrollIntoView({block: 'center', inline: 'center'});
								this.bgaPerformAction('actIncursionInjuries', {id: unit.id});
							});
						}
						break;
//
					case 'incursionContinue':
//
						this.addActionButton('SSincursionContinueYes', _('Continue'), (event) => this.bgaPerformAction('actIncursionContinue', {continue: true}));
						this.addActionButton('SSincursionContinueNo', _('Abort'), (event) => this.bgaPerformAction('actIncursionContinue', {continue: false}));
//
						break;
//
					case 'buildPalisades':
//
						{
							this.addActionButton('SScancel', _('Cancel'), (event) => {
								dojo.stopEvent(event);
								this.restoreServerGameState();
							}, null, false, 'gray');
//
							this.addActionButton('SSbuildPalisades', dojo.string.substitute(_('Build ${N} palisade(s)'), {N: 0}), (event) => {
								dojo.stopEvent(event);
								const palisades = dojo.query(`.SScounter.SSprovisional[data-type='palisades']`, 'SSboard').reduce((L, node) => [...L, +node.dataset.location], []);
								if (palisades.length > 0) this.bgaPerformAction('actBuildPalisades', {locations: JSON.stringify(palisades)});
							}, null, false, 'red');
						}
						break;
//
					case 'buildCitadels':
//
						{
							this.addActionButton('SScancel', _('Cancel'), (event) => {
								dojo.stopEvent(event);
								this.restoreServerGameState();
							}, null, false, 'gray');
//
							this.addActionButton('SSbuildCitadels', dojo.string.substitute(_('Build ${N} citadel(s)'), {N: 0}), (event) => {
								dojo.stopEvent(event);
								const citadels = dojo.query(`.SScounter.SSprovisional[data-type='citadels']`, 'SSboard').reduce((L, node) => [...L, +node.dataset.location], []);
								if (citadels.length > 0) this.bgaPerformAction('actBuildCitadels', {locations: JSON.stringify(citadels)});
							}, null, false, 'red');
						}
						break;
//
					case 'movementPhase':
//
						{
							this.movement = new Movement(this, MOVEMENT, args.faction, args.location, args.navalDifficulties);
//
							let scribe = false;
							const attestors = dojo.query(`.SScounter[data-type='attestor'][data-location='${args.location}']`, 'SSboard');
//
							const container = dojo.place(`<div id='SSunitContainer' class='SSunitContainer'></div>`, 'generalactions');
							dojo.query(`.SSunit[data-location='${args.location}']`, 'SSboard').forEach(node =>
							{
								const unit = node.dataset;
								if (unit.type === 'Scribes') scribe = attestors;
//
								node = dojo.place(`<div class='SSunit ${ +unit.reduced === 1 ? 'SSreduced ' : ''}SSselected' data-id='${unit.id}' data-faction='${unit.faction}' data-type='${unit.type}' data-location='${unit.location}'></div>`, container);
								dojo.connect(node, 'click', (event) => {
									dojo.stopEvent(event);
//									$(`SSunit-${unit.id}`).scrollIntoView({block: 'center', inline: 'center'});
									if (!dojo.hasClass(node, 'SSremoved'))
									{
										dojo.toggleClass(node, 'SSselected');
										this.movement.show(!dojo.hasClass('SSshipsWear', 'SSdisabled'));
										if (scribe)
										{
											const scribes = dojo.query(`.SSunit.SSselected[data-type='Scribes']`, 'SSunitContainer');
											if (scribes.length === 1 && +scribes[0].dataset.location === args.location)
											{
												const attestors = dojo.query(`.SScounter[data-type='attestor'][data-location='${args.location}']`, 'SSboard');
												dojo.toggleClass('SSscribe', 'disabled', attestors.length === 0);
											}
											else dojo.addClass('SSscribe', 'disabled');
										}
									}
								});
								dojo.toggleClass(node, 'SSselected');
							});
//
							if (scribe && attestors.length > 0)
							{
								this.addActionButton('SSscribe', _('Use scribe'), (event) =>
								{
									dojo.stopEvent(event);
									const scribes = dojo.query(`.SSunit.SSselected[data-type='Scribes']`, 'SSunitContainer');
									if (scribes.length === 1) this.bgaPerformAction('actScribe', {scribe: scribes[0].dataset.id, attestor: attestors[0].dataset.id});
								});
								dojo.addClass('SSscribe', 'disabled');
							}
//
							this.addActionButton('SSreset', _('Reset'), (event) => {
								dojo.stopEvent(event);
								this.movement.restore();
							}, null, false, 'gray');
//
							const node = dojo.place(`<div id='SSshipsWear' class='SScounter action-button' data-type='shipsWear'></div>`, 'generalactions');
							dojo.toggleClass(node, 'SSdisabled', args.navalDifficulties !== true);
							dojo.connect(node, 'click', (event) =>
							{
								dojo.stopEvent(event);
								if (!dojo.hasClass(node, 'SSdisabled')) this.movement.shipsWear(args.faction, args.location);
							}
							);
							this.addTooltip(node.id,
									_('Naval difficulties: both players are affected but the Indigenous player is only affected if moving to a non-contiguous area by using rebel units. For each naval movement to a non-contiguous area (including drag and drop movements) the player should reduce one unit in the origin area from its full-strength to its reduced strength side or eliminate one unit if it is by its reduced side already.'),
									_('Ships wear')
									);
//
							this.addActionButton('SSdone', _('Confirm movement'), (event) => {
								dojo.stopEvent(event);
								const units = this.movement.result();
								if (dojo.query(`.SSunit[data-faction='${args.faction}'][data-location='${args.location}']:not([data-type='Leader'])`, 'SSboard').length > 3)
									return this.showMessage(_('Overstacking'), 'info');
								if (this.overstacking(Object.keys(units))) return this.showMessage(_('Overstacking'), 'info');
								this.bgaPerformAction('actMovementPhase', {units: JSON.stringify(units), shipsWear: JSON.stringify(this.movement.navalDifficulties)});
							}, null, false, 'red');
						}
						break;
//
					case 'combatSelectUnits':
//
						{
							const container = dojo.place(`<div id='SSunitContainer' class='SSunitContainer'></div>`, 'generalactions');
							for (let unit of Object.values(args._private.units))
							{
								const node = dojo.place(`<div class='SSunit ${ +unit.reduced === 1 ? 'SSreduced ' : ''}SSselected' data-id='${unit.id}' data-faction='${unit.faction}' data-type='${unit.type}' data-location='${unit.location}'></div>`, container);
								dojo.connect(node, 'click', (event) => {
									dojo.stopEvent(event);
//									$(`SSunit-${unit.id}`).scrollIntoView({block: 'center', inline: 'center'});
									dojo.toggleClass(node, 'SSselected');
									dojo.toggleClass('SScombatSelectUnits', 'disabled', dojo.query('.SSunit.SSselected', container).length !== 3);
								});
							}
//
							this.addActionButton('SScombatSelectUnits', _('Select only 3 units'), (event) => {
								dojo.stopEvent(event);
								const units = dojo.query('.SSunit.SSselected', container).reduce((L, node) => [...L, +node.dataset.id], []);
								if (units.length === 3) this.bgaPerformAction('actCombatSelectUnits', {units: JSON.stringify(units)});
							});
							dojo.addClass('SScombatSelectUnits', 'disabled');
						}
						break;
//
					case 'combatHits':
//
						{
							const container = dojo.place(`<div id='SSunitContainer' class='SSunitContainer'></div>`, 'generalactions');
							for (let unit of Object.values(args._private.units))
							{
								const node = dojo.place(`<div class='SSunit ${ +unit.reduced === 1 ? 'SSreduced ' : ''}' data-id='${unit.id}' data-faction='${unit.faction}' data-type='${unit.type}' data-location='${unit.location}' data-hits='0'></div>`, container);
								dojo.connect(node, 'click', (event) => {
									dojo.stopEvent(event);
//									$(`SSunit-${unit.id}`).scrollIntoView({block: 'center', inline: 'center'});
									if (args._private.hits > 0)
									{
										if (!dojo.hasClass(node, 'SSreduced'))
										{
											if (+$('SShits').innerHTML < args._private.hits)
											{
												dojo.addClass(node, 'SSselected SSreduced');
												node.dataset.hits++;
												$('SShits').innerHTML++;
											}
										}
										else if (!dojo.hasClass(node, 'SSremoved') && +$('SShits').innerHTML < args._private.hits)
										{
											dojo.addClass(node, 'SSselected SSreduced SSremoved');
											node.dataset.hits++;
											$('SShits').innerHTML++;
										}
										else
										{
											if (dojo.hasClass(node, 'SSremoved'))
											{
												node.dataset.hits--;
												$('SShits').innerHTML--;
												dojo.removeClass(node, 'SSselected SSremoved');
											}
											if (dojo.hasClass(node, 'SSreduced') && +unit.reduced === 0)
											{
												node.dataset.hits--;
												$('SShits').innerHTML--;
												dojo.removeClass(node, 'SSselected SSreduced');
											}
										}
									}
									console.log($('SShits').innerHTML, args._private.hits);
									dojo.toggleClass('SScombatHits', 'disabled', +$('SShits').innerHTML !== args._private.hits);
									if (dojo.query('.SSunit:not(.SSremoved)', container).length === 0) dojo.removeClass('SScombatHits', 'disabled');
								});
							}
//
							this.addActionButton('SScombatHits', `<span id='SShits'>0</span>/${args._private.hits} ` + _('hit(s)'), (event) => {
								dojo.stopEvent(event);
								const units = {};
								dojo.query('.SSunit.SSselected', container).forEach((node) => units[node.dataset.id] = +node.dataset.hits);
								this.bgaPerformAction('actCombatHits', {units: JSON.stringify(units)});
							});
							dojo.addClass('SScombatHits', 'disabled');
						}
						break;
//
					case 'combatRetreat':
//
						{
							this.movement = new Movement(this, RETREAT, args._private.faction, args.location, args.navalDifficulties);
//
							const container = dojo.place(`<div id='SSunitContainer' class='SSunitContainer'></div>`, 'generalactions');
							for (let unit of Object.values(args._private.units))
							{
								const node = dojo.place(`<div class='SSunit ${ +unit.reduced === 1 ? 'SSreduced ' : ''}SSselected' data-id='${unit.id}' data-faction='${unit.faction}' data-type='${unit.type}' data-location='${unit.location}'></div>`, container);
								dojo.connect(node, 'click', (event) => {
									dojo.stopEvent(event);
//									$(`SSunit-${unit.id}`).scrollIntoView({block: 'center', inline: 'center'});
									if (!dojo.hasClass(node, 'SSremoved'))
									{
										dojo.toggleClass(node, 'SSselected');
										this.movement.show(!dojo.hasClass('SSshipsWear', 'SSdisabled'));
									}
								});
								dojo.toggleClass(node, 'SSselected');
							}
//
							this.addActionButton('SSreset', _('Reset'), (event) => {
								dojo.stopEvent(event);
								this.movement.restore();
							}, null, false, 'gray');
//
							const node = dojo.place(`<div id='SSshipsWear' class='SScounter action-button' data-type='shipsWear'></div>`, 'generalactions');
							dojo.toggleClass(node, 'SSdisabled', args.navalDifficulties !== true);
							dojo.connect(node, 'click', (event) =>
							{
								dojo.stopEvent(event);
								if (!dojo.hasClass(node, 'SSdisabled')) this.movement.shipsWear(args._private.faction, args.location);
							}
							);
							this.addTooltip(node.id,
									_('Naval difficulties: both players are affected but the Indigenous player is only affected if moving to a non-contiguous area by using rebel units. For each naval movement to a non-contiguous area (including drag and drop movements) the player should reduce one unit in the origin area from its full-strength to its reduced strength side or eliminate one unit if it is by its reduced side already.'),
									_('Ships wear')
									);
//
							this.addActionButton('SSdone', _('Confirm retreat'), (event) => {
								dojo.stopEvent(event);
								const units = this.movement.result();
								if (dojo.query(`.SSunit[data-faction='${args.faction}'][data-location='${args.location}']:not([data-type='Leader'])`, 'SSboard').length > 3)
									return this.showMessage(_('Overstacking'), 'info');
								if (this.overstacking(Object.keys(units))) return this.showMessage(_('Overstacking'), 'info');
								this.bgaPerformAction('actRetreat', {units: JSON.stringify(units), shipsWear: JSON.stringify(this.movement.navalDifficulties)});
							}, null, false, 'red');
//
//							this.addActionButton('SSnoRetreat', _('No retreat'), (event) => {
//								dojo.stopEvent(event);
//								this.bgaPerformAction('actNoRetreat');
//							});
						}
						break;
//
					case 'divineGraceNatureSpirits':
//
						{
							for (let [unit, die] of Object.entries(args))
							{
								this.addActionButton('SSreroll', _('Re-roll die'), () => this.bgaPerformAction('actDivineGraceNatureSpirits', {type: 're-roll', dice: '[0]'}), null, false, 'red');
								this.addActionButton('SSsubstract', _('Subtract 1 from value'), () => this.bgaPerformAction('actDivineGraceNatureSpirits', {type: '-1', dice: '[0]'}), null, false, 'red');
								this.addActionButton('SSpass', _('Do nothing'), () => this.bgaPerformAction('actDivineGraceNatureSpirits', {type: 'pass'}));
							}
						}
						break;
//
				}
			}
		},
		setupNotifications: function ()
		{
			console.log('notifications subscriptions setup');
//
			dojo.subscribe('fate', (notif) => this.fate(notif.args.fate));
			dojo.subscribe('event', (notif) => dojo.query(`.SScard[data-id='${notif.args.card}']`, 'SShand').remove());
//
			dojo.subscribe('placeUnit', (notif) => this.placeUnit(notif.args.unit));
			dojo.subscribe('removeUnit', (notif) => this.removeUnit(notif.args.unit));
//
			dojo.subscribe('placeCounter', (notif) => this.placeCounter(notif.args.counter));
			dojo.subscribe('removeCounter', (notif) => this.removeCounter(notif.args.counter));
//
			this.setSynchronous();
		},
		setSynchronous()
		{
			this.notifqueue.setSynchronous('placeUnit', DELAY);
			this.notifqueue.setSynchronous('removeUnit', DELAY);
//
			this.notifqueue.setSynchronous('placeCounter', DELAY);
			this.notifqueue.setSynchronous('removeCounter', DELAY);
		},
		format_string_recursive: function (log, args)
		{
			if (log && args && !args.processed)
			{
				args.processed = true;
//
				if ('EVENTS' in args)
					args.EVENTS = `<div style='display:flex'>
<div class='SScard' data-id='${args.EVENTS[0]}' style='flex:1 1 auto;background-position-x:${args.EVENTS[0] / 53 * 100}%'></div>
<div class='SScard' data-id='${args.EVENTS[1]}' style='flex:1 1 auto;background-position-x:${args.EVENTS[1] / 53 * 100}%'></div>
</div>
`;
				if ('BAG' in args)
					args.BAG = BAG.replace('none', args.BAG);
				if ('DICE' in args)
					args.DICE = `<span class='SSdice' style='background-position-x:-${30 * (args.DICE - 1)}px'></span>`;
				if ('UNIT' in args)
					args.UNIT = `<div onclick='dojo.stopEvent(event);gameui.board.focus(+this.dataset.location);' class='SSunit ${ +args.UNIT.reduced === 1 ? 'SSreduced ' : ''}SSselected' data-id='${args.UNIT.id}' data-faction='${args.UNIT.faction}' data-type='${args.UNIT.type}' data-location='${args.UNIT.location}'></div>`;
				if ('PALISADE' in args) args.PALISADE = `<div class='SScounter' data-id='${args.PALISADE.id}' data-type='${args.PALISADE.type}' data-location='${args.PALISADE.location}'></div>`;
				if ('CITADEL' in args) args.CITADEL = `<div class='SScounter' data-id='${args.CITADEL.id}' data-type='${args.CITADEL.type}' data-location='${args.CITADEL.location}'></div>`;
			}
			return this.inherited(arguments);
		},
		fate: function (fate)
		{
			console.log('fate', fate);
//
			if (fate === 0 && this.gamedatas.turn === 1) fate = 52;
			if (fate === 0 && this.gamedatas.turn > 1) fate = 53;
//
			dojo.query('.SSfate>.SScard').forEach((node) => {
				node.dataset.id = fate;
				dojo.setStyle(node, 'background-position-x', `${fate / 53 * 100}%`);
			});
//
		},
		hand: function (hand)
		{
			console.log('hand', hand);
//
			for (let i = 0; i < hand.length; i++)
			{
				let  node = $(`SScard-${i}`);
				if (!node) node = dojo.place(`<div id='SScard-${i}' tabindex='0' class='SScard' data-id='${hand[i]}'></div>`, 'SShand');
				dojo.setStyle(node, 'background-position-x', `${hand[i] / 53 * 100}%`);
			}
		},
		placeUnit: function (unit)
		{
			console.log('placeUnit', unit);
//
			if (dojo.query(`.SSunit.SSprovisional[data-id='${unit.id}']`, 'SSboard').remove().length) this.notifqueue.setSynchronousDuration(0);
//
			let node = $(`SSunit-${unit.id}`);
			if (!node)
			{
				node = dojo.place(`<div id='SSunit-${unit.id}' class='SSunit' data-id='${unit.id}' data-faction='${unit.faction}' data-type='${unit.type}' data-reduced='${unit.reduced}'></div>`, 'SSboard');
				dojo.connect(node, 'click', (event) => {
					const action = $(`SSaction-${event.currentTarget.dataset.location}`);
					if (action)
					{
						dojo.stopEvent(event);
						action.click();
					}
				});
			}
			const from = node.dataset.location;
//
			dojo.toggleClass(node, 'SSreduced', +unit.reduced);
			node.dataset.location = unit.location;
//
			if (from !== unit.location) this.arrange(from, unit.faction);
//
			if (isNaN(unit.location) && unit.location !== 'prisonInSpain') dojo.destroy(node);
			else this.arrange(unit.location, unit.faction);
//
			return node;
		},
		removeUnit: function (unit)
		{
			console.log('removeUnit', unit);
//
			const node = $(`SSunit-${unit.id}`);
			if (node) dojo.destroy(node);
		},
		arrange: function (location, faction)
		{
			const ORDER = ['Leader', 'Cavalry', 'Arquebusiers', 'Swordmen', 'Pawns', 'Scribes', 'Caciques', 'Naborias', 'Calinagos', 'Tamas', 'Captains', 'Troops'];
			const nodes = dojo.query(`.SSunit[data-faction=${faction}][data-location='${location}']`, 'SSboard').sort((a, b) => ORDER.indexOf(b.dataset.type) - ORDER.indexOf(a.dataset.type));
			const delta = nodes.length > 10 ? .25 : .75;
			for (let i = 0; i < nodes.length; i++)
			{
				if (faction === 'Indigenous')
					dojo.style(nodes[i], {'z-index': 10 + i, left: `${BOARD[location][0] - 1.5 + (i - nodes.length / 2 + .5) * delta}%`, top: `${BOARD[location][1] - 5 + .5 * (i - nodes.length / 2) * delta}%`});
				if (faction === 'Spanish')
					dojo.style(nodes[i], {'z-index': 10 + i, left: `${BOARD[location][0] - 1.5 + (i - nodes.length / 2 + .5) * delta}%`, top: `${BOARD[location][1] + 2 + .5 * (i - nodes.length / 2) * delta}%`});
			}
		},
		overstacking: function (units)
		{
			let overStacking = false;
			for (let ID of units)
			{
				const unit = $(`SSunit-${ID}`);
				if (dojo.query(`.SSunit[data-faction='${unit.dataset.faction}'][data-location='${unit.dataset.location}']:not([data-type='Leader'])`, 'SSboard').length > 3) overStacking = true;
			}
			return overStacking;
		},
		placeCounter: function (counter)
		{
			console.log('placeCounter', counter);
//
			let node = $(`SScounter-${counter.id}`);
			if (counter.location === 'aside')
			{
				if (node) node.remove();
				return;
			}
//
			switch (counter.type)
			{
//
				case 'turn':
//
					{
						if (!node)
						{
							node = dojo.place(`<div id='SScounter-${counter.id}' tabindex='0' class='SScounter' data-id='${counter.id}' data-type='${counter.type}' data-location='${counter.location}'></div>`, 'SSboard');
						}
						dojo.style(node, {left: `${TURN[counter.location][0] - 1.5}%`, top: `${TURN[counter.location][1] - 1.5}%`});
						this.addTooltip(node.id,
								_('This track indicates the current turn.'),
								_('Game Turn (1-6): ') + `<B>${counter.location}</B>` + ` (${{1: '1492', 2: '1493-1494', 3: '1495-1496', 4: '1497-1499', 5: '1500-1502', 6: '1503-1505'}[counter.location]})`
								);
					}
					break;
//
				case 'VP':
//
					{
						if (!node)
						{
							node = dojo.place(`<div id='SScounter-${counter.id}' tabindex='0' class='SScounter' data-id='${counter.id}' data-type='${counter.type}' data-location='${counter.location}'></div>`, 'SSboard');
						}
						dojo.style(node, {left: `${VP[counter.location][0] - 1.5}%`, top: `${VP[counter.location][1] - 1.5}%`});
						this.addTooltip(node.id,
								_('Players use the Victory Point Marker to track the accumulated Victory Points (VP) throughout the game.'),
								_('Victory points (0-20): ') + `<B>${counter.location}</B>`
								);
					}
					break;
//
				case 'impulseSpanish':
				case 'impulseIndigenous':
//
					{
						if (!node)
						{
							node = dojo.place(`<div id='SScounter-${counter.id}' tabindex='0' class='SScounter' data-id='${counter.id}' data-type='${counter.type}' data-location='${counter.location}'></div>`, 'SSboard');
						}
						dojo.style(node, {left: `${IMPULSE[counter.location][0] - 1.5}%`, top: `${IMPULSE[counter.location][1] - 1.5}%`});
						this.addTooltip(node.id,
								_('This track is used during the Impulse Phase, a key action phase for both players. The number of impulses available determines the number of actions you can take during your turn. Players use their respective impulse markers to track their impulses during a game turn.'),
								_('Impulses') + ' : ' + `<B>${counter.location}</B>`
								);
					}
					break;
//
				case 'royalSupport':
//
					{
						if (!node)
						{
							node = dojo.place(`<div id='SScounter-${counter.id}' tabindex='0' class='SScounter' data-id='${counter.id}' data-type='${counter.type}' data-location='${counter.location}'></div>`, 'SSboard');
						}
						dojo.style(node, {left: `${ROYALSUPPORT[counter.location][0] - 1.5}%`, top: `${ROYALSUPPORT[counter.location][1] - 1.5}%`});
						this.addTooltip(node.id,
								_('This track is relevant only to the Spanish player. It reflects the level of support received from the Spanish Crown for Columbus\' voyages. The Spanish player could receive advantages depending on the Royal Support Marker position.'),
								_('Royal Support (0-10): ') + `<B>${counter.location}</B>`
								);
					}
					break;
//
				case 'divineGrace':
				case 'natureSpirits':
//
					{
						if (!node)
						{
							node = dojo.place(`<div id='SScounter-${counter.id}' tabindex='0' class='SScounter' data-id='${counter.id}' data-type='${counter.type}' data-location='${counter.location}'></div>`, 'SSboard');
						}
						dojo.style(node, {left: `${IMPULSE[counter.location][0] - 1.5}%`, top: `${IMPULSE[counter.location][1] - 1.5 + 5}%`});
						node.dataset.type = counter.type;
						this.addTooltip(node.id,
								_('This track represents the opposing forces of divine influence throughout the game. A counter is is placed on either the "Divine Grace" side (favoring the Spanish) or the "Nature Spirits" side (favoring the Indigenous players). This can impact various aspects of the game, such as combat outcomes or incursion attempts.'),
								_('Divine Grace / Nature Spirits')
								);
					}
					break;
//
				case 'palisades':
				case 'citadels':
//
					if (!node)
					{
						node = dojo.place(`<div id='SScounter-${counter.id}' tabindex='0' class='SScounter' data-id='${counter.id}' data-type='${counter.type}' data-location='${counter.location}'></div>`, 'SSboard');
						dojo.connect(node, 'click', (event) => {
							const action = $(`SSaction-${event.currentTarget.dataset.location}`);
							if (action)
							{
								dojo.stopEvent(event);
								action.click();
							}
						});
					}
					dojo.style(node, {left: `${BOARD[counter.location][0] - 1.5}%`, top: `${BOARD[counter.location][1] - 1.5}%`});
//
					break;
//
				case 'attestor':
//
					{
						if (!node)
						{
							node = dojo.place(`<div id='SScounter-${counter.id}' tabindex='0' class='SScounter' data-id='${counter.id}' data-type='${counter.type}' data-location='${counter.location}'></div>`, 'SSboard');
						}
						dojo.style(node, {left: `${ATTESTOR[counter.location][0] - 1.5}%`, top: `${ATTESTOR[counter.location][1] - 1.5}%`});
						this.addTooltipHtml(node.id, _('Attestor Marker'));
					}
					break;
//
				case 'shipsWear':
//
					{
						if (!node)
						{
							node = dojo.place(`<div id='SScounter-${counter.id}' tabindex='0' class='SScounter' data-id='${counter.id}' data-type='${counter.type}' data-location='${counter.location}'></div>`, 'SSboard');
						}
						dojo.style(node, {left: `${BOARD[counter.location][0] - 1.5}%`, top: `${BOARD[counter.location][1] - 1.5}%`});
						this.addTooltip(node.id,
								_('Naval difficulties: both players are affected but the Indigenous player is only affected if moving to a non-contiguous area by using rebel units. For each naval movement to a non-contiguous area (including drag and drop movements) the player should reduce one unit in the origin area from its full-strength to its reduced strength side or eliminate one unit if it is by its reduced side already.'),
								_('Ships wear')
								);
					}
					break;
			}
			return node;
		},
		removeCounter: function (counter)
		{
			console.log('removeCounter', counter);
//
			const node = $(`SScounter-${counter.id}`);
			if (node) dojo.destroy(node);
		}
	});
});
