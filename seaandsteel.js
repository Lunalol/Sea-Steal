/* global g_gamethemeurl, ebg, _, dijit */

const DELAY = 500;
//
define(["dojo", "dojo/_base/declare", "ebg/core/gamegui", "ebg/counter",
	g_gamethemeurl + "modules/JavaScript/constants.js",
	g_gamethemeurl + "modules/JavaScript/board.js",
	g_gamethemeurl + "modules/JavaScript/drag&drop.js"
], function (dojo, declare)
{
	return declare("bgagame.seaandsteel", ebg.core.gamegui, {
		constructor: function ()
		{
			console.log('seaandsteel constructor');
		},
		setup: function (gamedatas)
		{
			console.log("Starting game setup", gamedatas);
//
//			dojo.connect($('game_play_area'), 'click', () => this.restoreServerGameState());
//
// Card tooltips
//
			new dijit.Tooltip({connectId: "ebd-body", selector: ".SScard", showDelay: 1000, hideDelay: 0, getContent: (node) =>
				{
					return `<div class='SScard' style='width:400px;background-position-x:${node.style['background-position-x']}'></div>`;
				}});
//
			this.board = new Board(this);
//
// Fate card
//
			dojo.place(`<div class='SSfate'><div id='SSfate' tabindex='0' class='SScard'></div></div>`, 'player_boards');
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
												dojo.style(provisional, {left: `${BOARD[location][0] - 2}%`, top: `${BOARD[location][1] - 2}%`});
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
												dojo.style(provisional, {left: `${BOARD[location][0] - 2}%`, top: `${BOARD[location][1] - 2}%`});
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
						const node = dojo.place(`<div id='SSaction-${location}' class='SSaction' style='background:${COLORS[state.args.faction]};filter:blur(25px);z-index:-1;'></div>`, 'SSboard');
						dojo.style(node, {left: `${BOARD[state.args.location][0] - 5}%`, top: `${BOARD[state.args.location][1] - 5}%`});
					}
					break;
//
				case 'eventCombatPhase':
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
						dojo.query('.SSunit', 'SSboard').addClass('SSdisabled');
//
						const node = dojo.place(`<div id='SSaction-${location}' class='SSaction' style='background:${COLORS['combat']};filter:blur(25px);z-index:-1;'></div>`, 'SSboard');
						dojo.style(node, {left: `${BOARD[state.args.location][0] - 5}%`, top: `${BOARD[state.args.location][1] - 5}%`});
					}
					break;
//
				case 'retreat':
//
					{
						dojo.query('.SSunit', 'SSboard').addClass('SSdisabled');
//
						const node = $(`SSunit-${state.args.unit.id}`);
						dojo.toggleClass(node, 'SSdisabled');
//
						for (let location of state.args.locations)
						{
							const node = dojo.place(`<div id='SSaction-${location}' class='SSaction' style='background:${COLORS[state.args.faction]};filter:blur(25px);z-index:-1;'></div>`, 'SSboard');
							dojo.style(node, {left: `${BOARD[location][0] - 5}%`, top: `${BOARD[location][1] - 5}%`});
							dojo.connect(node, 'click', (event) => {
								dojo.stopEvent(event);
								if (this.isCurrentPlayerActive())
								{
									const units = dojo.query('.SSunit.SSselected', 'SSunitContainer').reduce((L, node) => [...L, +node.dataset.id], []);
									if (units.length > 0) this.bgaPerformAction('actRetreat', {location: location, units: JSON.stringify(units)});
								}
							});
							this.board.arrow(+state.args.unit.location, location, '#45a1bf40');
						}
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
		},
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
						this.addActionButton('SSfate', _('Draw and reveal fate card'), (event) => {
							dojo.stopEvent(event);
							this.bgaPerformAction('actStartOfGame');
						});
						break;
//
					case 'secretChoice':
//
						dojo.query('.SShand>.SScard', 'SSplayArea').addClass('SSselectable');
//
						this.addActionButton('SSsecretChoice', _('Play event card'), (event) => {
							dojo.stopEvent(event);
							const nodes = dojo.query('.SShand>.SScard.SSselected', 'SSplayArea');
							if (nodes.length === 1) this.bgaPerformAction('actSecretChoice', {card: nodes[0].dataset.id});
						});
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
								dojo.toggleClass(node, 'SSselected');
							}
//
							for (let location of args.locations)
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
					case 'action':
//
						this.addActionButton('SSactivation', _('Activate an area'), (event) => {
							dojo.stopEvent(event);
							this.setClientState('activation', {descriptionmyturn: _('${you} can activate an area')});
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
						this.addActionButton('SSpass', _('Do nothing'), (event) => {
							dojo.stopEvent(event);
							this.bgaPerformAction('actPass');
						}, null, false, 'red');
//
						break;
//
					case 'activation':
//
						this.addActionButton('SScancel', _('Cancel'), (event) => {
							dojo.stopEvent(event);
							this.restoreServerGameState();
						}, null, false, 'gray');
//
						break;
//
					case 'buildPalisades':
//
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
//
						break;
//
					case 'buildCitadels':
//
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
//
						break;
//
					case 'movementPhase':
//
						{
							let scribe = false;
//
							const container = dojo.place(`<div id='SSunitContainer' class='SSunitContainer'></div>`, 'generalactions');
							for (let unit of Object.values(args.units))
							{
								if (unit.type === 'Scribes') scribe = true;
//
								const node = dojo.place(`<div class='SSunit ${ +unit.reduced === 1 ? 'SSreduced ' : ''}SSselected' data-id='${unit.id}' data-faction='${unit.faction}' data-type='${unit.type}' data-location='${unit.location}'></div>`, container);
								dojo.connect(node, 'click', (event) => {
									dojo.stopEvent(event);
//									$(`SSunit-${unit.id}`).scrollIntoView({block: 'center', inline: 'center'});
									dojo.toggleClass(node, 'SSselected');
									this.movement.show();
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
								});
								dojo.toggleClass(node, 'SSselected');
							}
							this.movement = new Movement(this, args);
//
							if (scribe)
							{
								this.addActionButton('SSscribe', _('Use scribe'), (event) =>
								{
									dojo.stopEvent(event);
									const scribes = dojo.query(`.SSunit.SSselected[data-type='Scribes']`, 'SSunitContainer');
									const attestors = dojo.query(`.SScounter[data-type='attestor'][data-location='${args.location}']`, 'SSboard');
									if (scribes.length === 1 && attestors.length === 1) this.bgaPerformAction('actScribe', {scribe: scribes[0].dataset.id, attestor: attestors[0].dataset.id});
								});
								dojo.addClass('SSscribe', 'disabled');
							}
//
							this.addActionButton('SSreset', _('Reset'), (event) => {
								dojo.stopEvent(event);
								this.movement.restore();
							}, null, false, 'gray');
							this.addActionButton('SSdone', _('Confirm movement'), (event) => {
								dojo.stopEvent(event);
								const units = this.movement.result();
								if (this.overstacking(Object.keys(units))) return this.showMessage(_('Overstacking'), 'info');
								this.bgaPerformAction('actMovementPhase', {units: JSON.stringify(units)});
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
							dojo.query('.SSunit', 'SSboard').addClass('SSdisabled');
							const container = dojo.place(`<div id='SSunitContainer' class='SSunitContainer'></div>`, 'generalactions');
							for (let unit of Object.values(args._private.units))
							{
								const node = dojo.place(`<div class='SSunit ${ +unit.reduced === 1 ? 'SSreduced ' : ''}SSselected' data-id='${unit.id}' data-faction='${unit.faction}' data-type='${unit.type}' data-location='${unit.location}''></div>`, container);
								dojo.connect(node, 'click', (event) => {
									dojo.stopEvent(event);
									const node = $(`SSunit-${unit.id}`);
//									$(`SSunit-${unit.id}`).scrollIntoView({block: 'center', inline: 'center'});
									dojo.removeClass(node, 'SSdisabled');
									this.setClientState('retreat', {descriptionmyturn: _("${you} can choose a retreat location"), args: {faction: args._private.faction, unit: unit, locations: args._private.locations[unit.id]}});
								});
							}
//
							this.addActionButton('SSnoRetreat', _('No retreat'), (event) => {
								dojo.stopEvent(event);
								this.bgaPerformAction('actNoRetreat');
							});
						}
						break;
//
					case 'retreat':
//
						{
							const container = dojo.place(`<div id='SSunitContainer' class='SSunitContainer'></div>`, 'generalactions');
							const node = dojo.place(`<div class='SSunit ${ +args.unit.reduced === 1 ? 'SSreduced ' : ''}SSselected' data-id='${args.unit.id}' data-faction='${args.unit.faction}' data-type='${args.unit.type}'></div>`, container);
//
							this.addActionButton('SScancel', _('Cancel'), (event) => {
								dojo.stopEvent(event);
								this.restoreServerGameState();
							});
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
			dojo.subscribe('event', (notif) => this.gamedatas.event = notif.args.event);
//
			dojo.subscribe('placeUnit', (notif) => this.placeUnit(notif.args.unit));
			dojo.subscribe('placeCounter', (notif) => this.placeCounter(notif.args.counter));
			dojo.subscribe('removeCounter', (notif) => this.removeCounter(notif.args.counter));
//
			this.setSynchronous();
		},
		setSynchronous()
		{
			this.notifqueue.setSynchronous('placeUnit', DELAY);
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
<div class='SScard' style='flex:1 1 auto;background-position-x:${args.EVENTS[0] / 62 * 100}%'></div>
<div class='SScard' style='flex:1 1 auto;background-position-x:${args.EVENTS[1] / 62 * 100}%'></div>
</div>
`;
				if ('DICE' in args)
					args.DICE = `<span class='SSdice' style='background-position-x:-${30 * (args.DICE - 1)}px'></span>`;
				if ('UNIT' in args)
					args.UNIT = `<div class='SSunit ${ +args.UNIT.reduced === 1 ? 'SSreduced ' : ''}SSselected' data-id='${args.UNIT.id}' data-faction='${args.UNIT.faction}' data-type='${args.UNIT.type}'></div>`;
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
			const node = $(`SSfate`);
			if (node)
			{
				node.dataset.id = fate;
				dojo.setStyle(node, 'background-position-x', `${fate / 62 * 100}%`);
			}
//
		},
		hand: function (hand)
		{
			console.log('hand', hand);
//
			for (let i = 0; i < hand.length; i++)
			{
				const node = $(`SScard-${i}`);
				if (node)
				{
					node.dataset.id = hand[i];
					dojo.setStyle(node, 'background-position-x', `${hand[i] / 62 * 100}%`);
				}
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
				node = dojo.place(`<div id='SSunit-${unit.id}' class='SSunit' data-id='${unit.id}' data-faction='${unit.faction}' data-type='${unit.type}'></div>`, 'SSboard');
				dojo.connect(node, 'click', (event) => {
					dojo.stopEvent(event);
				});
			}
			const from = node.dataset.location;
//
			dojo.toggleClass(node, 'SSreduced', +unit.reduced);
			node.dataset.location = unit.location;
//
			if (from !== unit.location) this.arrange(from, unit.faction);
			if (isNaN(unit.location)) dojo.destroy(node);
			else this.arrange(unit.location, unit.faction);
//
			return node;
		},
		arrange: function (location, faction)
		{
			const ORDER = ['Leader', 'Cavalry', 'Arquebusiers', 'Swordmen', 'Pawns', 'Scribes', 'Caciques', 'Naborias', 'Calinagos', 'Tamas', 'Captains', 'Troops'];
			const nodes = dojo.query(`.SSunit[data-faction=${faction}][data-location='${location}']`, 'SSboard').sort((a, b) => ORDER.indexOf(b.dataset.type) - ORDER.indexOf(a.dataset.type));
			const delta = nodes.length > 10 ? .5 : 1;
			for (let i = 0; i < nodes.length; i++)
			{
				if (faction === 'Indigenous')
					dojo.style(nodes[i], {'z-index': 10 + i, left: `${BOARD[location][0] - 4 + (i - nodes.length / 2) * delta}%`, top: `${BOARD[location][1] - 0 + (i - nodes.length / 2) * delta}%`});
				if (faction === 'Spanish')
					dojo.style(nodes[i], {'z-index': 10 + i, left: `${BOARD[location][0] + 1 + (i - nodes.length / 2) * delta}%`, top: `${BOARD[location][1] - 5 + (i - nodes.length / 2) * delta}%`});
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
			if (counter.location === 'aside') return;
//
			let node = $(`SScounter-${counter.id}`);
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
						dojo.style(node, {left: `${TURN[counter.location][0] - 2}%`, top: `${TURN[counter.location][1] - 2}%`});
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
						dojo.style(node, {left: `${VP[counter.location][0] - 2}%`, top: `${VP[counter.location][1] - 2}%`});
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
						dojo.style(node, {left: `${IMPULSE[counter.location][0] - 2}%`, top: `${IMPULSE[counter.location][1] - 2}%`});
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
						dojo.style(node, {left: `${ROYALSUPPORT[counter.location][0] - 2}%`, top: `${ROYALSUPPORT[counter.location][1] - 2}%`});
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
						dojo.style(node, {left: `${IMPULSE[counter.location][0] - 2}%`, top: `${IMPULSE[counter.location][1] - 2}%`});
						this.addTooltip(node.id,
								_('This track represents the opposing forces of divine influence throughout the game. A countercounter is is placed on either the "Divine Grace" side (favoring the Spanish) or the "Nature Spirits" side (favoring the Indigenous players). This can impact various aspects of the game, such as combat outcomes or incursion attempts.'),
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
							const action = $(`SSaction-${counter.location}`);
							if (action)
							{
								dojo.stopEvent(event);
								action.click();
							}
						});
					}
					dojo.style(node, {left: `${BOARD[counter.location][0] - 2}%`, top: `${BOARD[counter.location][1] - 2}%`});
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
						dojo.style(node, {left: `${ATTESTOR[counter.location][0] - 2}%`, top: `${ATTESTOR[counter.location][1] - 2}%`});
						this.addTooltipHtml(node.id, _('Attestor Marker'));
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
