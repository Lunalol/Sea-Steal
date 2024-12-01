/* global g_gamethemeurl, g_archive_mode */

define(["dojo", "dojo/_base/declare"], function (dojo, declare)
{
	return declare("Board", null,
	{
		constructor: function (bgagame)
		{
			console.log('board constructor');
//
// Reference to BGA game
//
			this.bgagame = bgagame;
//
// Getting scrollArea & board container and map dimensions
//
			this.scrollArea = dojo.byId('SSscrollArea');
			this.board = dojo.byId('SSboard');
//
			this.boardWidth = boardWidth;
			this.boardHeight = boardHeight;
//
			this.canvas = dojo.byId('SScanvas');
			dojo.setAttr(this.canvas, 'width', this.boardWidth);
			dojo.setAttr(this.canvas, 'height', this.boardHeight);
//
			dojo.connect(this.scrollArea, 'click', (event) =>
			{
				navigator.clipboard.writeText(`${Math.round(event.offsetX / boardWidth * 1000) / 10},${Math.round(event.offsetY / boardHeight * 1000) / 10}`);
			});
//
// Slider setting for zoom
//
			$('page-title').appendChild(dojo.byId('SSzoom'));
//
			this.zoomLevel = dojo.byId('SSzoomLevel');
			this.bgagame.onScreenWidthChange = this.resize.bind(this);
//
// Flag to follow drag gestures
//
			this.dragging = false;
//
			dojo.connect(document, 'oncontextmenu', (event) => dojo.stopEvent(event));
//
// Event listeners for drag gestures
//
			dojo.connect(this.scrollArea, 'mousedown', this, 'begin_drag');
			dojo.connect(this.scrollArea, 'mousemove', this, 'drag');
			dojo.connect(this.scrollArea, 'mouseup', this, 'end_drag');
			dojo.connect(this.scrollArea, 'mouseleave', this, 'end_drag');
//
// Event listeners for scaling
//
			dojo.connect(this.scrollArea, 'scroll', this, 'scroll');
			dojo.connect(this.scrollArea, 'wheel', this, 'wheel');
			dojo.connect(this.zoomLevel, 'oninput', this, () => this.setZoom(Math.pow(10., event.target.value / 100), this.scrollArea.clientWidth / 2, this.scrollArea.clientHeight / 2));
			dojo.connect(dojo.byId('SSzoomMinus'), 'onclick', () => this.setZoom(Math.pow(10., (parseInt(this.zoomLevel.value) - 10) / 100), this.scrollArea.clientWidth / 2, this.scrollArea.clientHeight / 2));
			dojo.connect(dojo.byId('SSzoomPlus'), 'onclick', () => this.setZoom(Math.pow(10., (parseInt(this.zoomLevel.value) + 10) / 100), this.scrollArea.clientWidth / 2, this.scrollArea.clientHeight / 2));
//
			dojo.connect(this.scrollArea, 'gesturestart', this, () => this.zooming = this.board.scale);
			dojo.connect(this.scrollArea, 'gestureend', this, () => this.zooming = null);
			dojo.connect(this.scrollArea, 'gesturechange', this, (event) =>
			{
				event.preventDefault();
//
				if (this.zooming !== null)
				{
					const rect = this.scrollArea.getBoundingClientRect();
					this.setZoom(this.zooming * event.scale, event.clientX - rect.left, event.clientY - rect.top);
				}
			});
//
// Initial zoom to cover the whole map or stored in session
//
			const scale = parseFloat(localStorage.getItem(`${this.bgagame.game_id}.${this.bgagame.table_id}.zoomLevel`));
			const sX = parseInt(localStorage.getItem(`${this.bgagame.game_id}.${this.bgagame.table_id}.sX`));
			const sY = parseInt(localStorage.getItem(`${this.bgagame.game_id}.${this.bgagame.table_id}.sY`));
//
			this.setZoom(Math.max(this.scrollArea.clientWidth / this.boardWidth, this.scrollArea.clientHeight / this.boardHeight, isNaN(scale) ? .25 : scale), this.scrollArea.clientWidth / 2, this.scrollArea.clientHeight / 2);
//
			const zoom = parseFloat(this.board.scale);
			this.scrollArea.scrollLeft = isNaN(scale) ? (this.boardWidth * zoom - this.scrollArea.clientWidth) / 2 : sX;
			this.scrollArea.scrollTop = isNaN(scale) ? (this.boardHeight * zoom - this.scrollArea.clientHeight) / 2 : sY;
		},
		resize: function ()
		{
			this.zoomLevel.min = Math.log10(Math.max(this.scrollArea.clientWidth / this.boardWidth, this.scrollArea.clientHeight / this.boardHeight)) * 100.;
			this.zoomLevel.max = 50 + +this.zoomLevel.min;
			this.zoomLevel.value = Math.log10(Math.max(this.scrollArea.clientWidth / this.boardWidth, this.scrollArea.clientHeight / this.boardHeight)) * 100.;
//			this.setZoom(Math.pow(10., this.zoomLevel.value / 100), this.scrollArea.clientWidth / 2, this.scrollArea.clientHeight / 2);
		},
		setZoom: function (scale, x, y)
		{
//
// Calc scale and store in session
//
			scale = Math.max(this.scrollArea.clientWidth / this.boardWidth, this.scrollArea.clientHeight / this.boardHeight, scale);
			localStorage.setItem(`${this.bgagame.game_id}.${this.bgagame.table_id}.zoomLevel`, scale);
//
// Update range value
//
			this.zoomLevel.value = Math.round(Math.log10(scale) * 100.);
//
// Get scroll positions and scale before scaling
//
			let sX = this.scrollArea.scrollLeft;
			let sY = this.scrollArea.scrollTop;
//
// Board scaling
//
			const oldScale = this.board.scale;
			this.board.scale = scale;
			this.board.style.transform = `scale(${scale})`;
//			this.board.style.width = `${this.boardWidth * Math.min(1.0, scale)}px`;
//			this.board.style.height = `${this.boardHeight * Math.min(1.0, scale)}px`;
//
// Set scroll positions after scaling
//
			this.scrollArea.scrollTo(Math.round((x + sX) * (scale / oldScale) - x), Math.round((y + sY) * (scale / oldScale) - y));
		},
		wheel: function (event)
		{
			if (event.ctrlKey)
			{
//
// Ctrl + Wheel
//
				dojo.stopEvent(event);
//
// Update scale only when zoom factor is updated
//
				const oldZoom = parseInt(this.zoomLevel.value);
				const newZoom = Math.min(Math.max(this.zoomLevel.min, oldZoom - 10 * Math.sign(event.deltaY)), this.zoomLevel.max);
				if (oldZoom !== newZoom)
				{
					const rect = this.scrollArea.getBoundingClientRect();
					this.setZoom(Math.pow(10., newZoom / 100.), event.clientX - rect.left, event.clientY - rect.top);
				}
			}
		},
		scroll: function ()
		{
			if (!this.bgagame.isSpectator)
			{
				localStorage.setItem(`${this.bgagame.game_id}.${this.bgagame.table_id}.sX`, this.scrollArea.scrollLeft);
				localStorage.setItem(`${this.bgagame.game_id}.${this.bgagame.table_id}.sY`, this.scrollArea.scrollTop);
			}
		},
		begin_drag: function (event)
		{
			this.startX = event.clientX;
			this.startY = event.clientY;
		},
		drag: function (event)
		{
			if (event.buttons !== 1) return;
			if (Math.max(Math.abs((event.clientX - this.startX), Math.abs(event.clientY - this.startY))) >= 2) this.dragging = true;
//
			if (this.dragging === true)
			{
				this.scrollArea.scrollLeft -= (event.clientX - this.startX);
				this.scrollArea.scrollTop -= (event.clientY - this.startY);
//
				this.startX = event.clientX;
				this.startY = event.clientY;
			}
		},
		end_drag: function (event)
		{
			if (this.dragging)
			{
				if (this.dragging === true)
				{
					window.setTimeout(() => {
						this.dragging = false;
					});
				}
				else
				{
					window.clearTimeout(this.dragging);
					this.dragging = false;
				}
			}
		},
		clearCanvas()
		{
			const ctx = this.canvas.getContext('2d');
			ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
		},
		arrow: function (start, end, color = '#FF000080')
		{
			if (start === end) return;
//
			const ctx = this.canvas.getContext('2d');
//
			ctx.save();
//
			ctx.strokeStyle = '#00000020';
			ctx.fillStyle = color;
//
			const dx = (BOARD[end][0] - BOARD[start][0]) * this.boardWidth / 100.;
			const dy = (BOARD[end][1] - BOARD[start][1]) * this.boardHeight / 100.;
//
			if (dx > 0) angle = Math.atan(dy / dx);
			else if (dx < 0) angle = Math.PI + Math.atan(dy / dx);
			else angle = Math.PI / 2 * Math.sign(dy);
//
			ctx.translate(BOARD[start][0] * this.boardWidth / 100., BOARD[start][1] * this.boardHeight / 100.);
			ctx.scale(Math.sqrt(dx * dx + dy * dy) / 100., Math.sqrt(dx * dx + dy * dy) / 100.);
			ctx.rotate(angle);
//
			ctx.beginPath();
			ctx.moveTo(0, 0);
			ctx.lineTo(-5, 10);
			ctx.lineTo(80, 5);
			ctx.lineTo(80, 10);
			ctx.lineTo(100, 00);
			ctx.lineTo(80, -10);
			ctx.lineTo(80, -5);
			ctx.lineTo(0, -10);
			ctx.lineTo(-5, -10);
			ctx.closePath();
//
			ctx.fill();
			ctx.stroke();
//
			ctx.restore();
		}
	}
	);
});
