define(["dojo", "dojo/_base/declare", "ebg/core/gamegui", "ebg/counter",
	g_gamethemeurl + "modules/JavaScript/constants.js"
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
			this.board = $('SSboard');
//
			for (let unit of Object.values(gamedatas.units)) this.placeUnit(unit);
			for (let counter of Object.values(gamedatas.counters)) this.placeCounter(counter);
//
			dojo.connect(this.board, 'click', (event) => {
				const x = Math.round(event.offsetX / event.currentTarget.offsetWidth * 100 * 100) / 100;
				const y = Math.round(event.offsetY / event.currentTarget.offsetHeight * 100 * 100) / 100;
				console.log(`${x},${y}`);
				navigator.clipboard.writeText(`${x},${y}`)
			});
//
			this.setupNotifications();
//
			console.log("Ending game setup");
		},
		onEnteringState: function (stateName, args)
		{
			console.log('Entering state: ' + stateName, args);
//
			switch (stateName)
			{
			}
		},
		onLeavingState: function (stateName)
		{
			console.log('Leaving state: ' + stateName);
//
			switch (stateName)
			{
			}
		},
		onUpdateActionButtons: function (stateName, args)
		{
			console.log('onUpdateActionButtons: ' + stateName, args);
//
			if (this.isCurrentPlayerActive())
			{
				switch (stateName)
				{
					case 'startOfGame':

						this.addActionButton('SSfate', _('Draw and reveal fate card'), (event) => {
							dojo.stopEvent(event);
							this.bgaPerformAction('cancel', {FACTION: this.FACTION});
						});
				}
			}
		},
		setupNotifications: function ()
		{
			console.log('notifications subscriptions setup');
//
		},
		placeUnit: function (unit)
		{
			console.log('placeUnit', unit);
//
			let node = $(`SSunit-${unit.id}`);
			if (!node)
			{
				node = dojo.place(`<div id='SSunit-${unit.id}' class='SSunit' data-faction='${unit.faction}' data-type='${unit.type}' data-location='${unit.location}' class='SSunit'></div>`, this.board);
				dojo.connect(node, 'click', (event) => {
					dojo.stopEvent(event);
					console.log(event.currentTarget.dataset);
				});
			}

			const nodes = dojo.query(`.SSunit[data-location='${unit.location}']`, this.board);
			for (let i = 0; i < nodes.length; i++)
			{
				dojo.style(nodes[i], {left: `${BOARD[unit.location][0] - 2 + i }%`, top: `${BOARD[unit.location][1] - 2 + i }%`});
			}
		},
		placeCounter: function (counter)
		{
			console.log('placeCounter', counter);
//
			let node = $(`SScounter-${counter.id}`);
			if (!node)
			{

			}
		}
	});
});
