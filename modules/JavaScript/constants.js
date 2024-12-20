//
// Size of board
//
const boardWidth = 4096;
const boardHeight = 3004;
//
const BOARD = {
	0: [90, 31.0],
	1: [14, 24.5],
	2: [23.2, 26.9],
	3: [31.4, 30.1],
	4: [37, 34.1],
	5: [43.4, 35.4],
	6: [48.6, 48.7],
	7: [59.5, 46.2],
	8: [68.4, 48.7],
	9: [80.3, 54.4],
	10: [31.3, 46.4],
	11: [90.6, 63.1],
	12: [87.5, 85.0],
	13: [40.7, 9],
	14: [51.2, 8],
	15: [59, 27.6],
	prisonInSpain: [92.0, 41.5],
	shipsWear: [80.0, 33.0]
};
const ATTESTOR = {
	1: [14.1, 16.0],
	2: [23.3, 18.3],
	7: [59.5, 37.6],
	8: [68.5, 40.0],
	9: [80.4, 49.0]
};
const TURN = {
	1: [7.1, 65.6],
	2: [11.15, 65.6],
	3: [15.15, 65.6],
	4: [19.15, 65.6],
	5: [23.25, 65.6],
	6: [27.25, 65.6]
};
const VP = {
	0: [7.10, 77.8],
	1: [11.15, 77.8],
	2: [15.15, 77.8],
	3: [19.15, 77.8],
	4: [23.15, 77.8],
	5: [27.15, 77.8],
	6: [31.2, 77.8],
	7: [7.10, 84.2],
	8: [11.15, 84.2],
	9: [15.15, 84.2],
	10: [19.15, 84.2],
	11: [23.15, 84.2],
	12: [27.15, 84.2],
	13: [31.2, 84.2],
	14: [7.10, 90.9],
	15: [11.15, 90.9],
	16: [15.15, 90.9],
	17: [19.15, 90.9],
	18: [23.15, 90.9],
	19: [27.15, 90.9],
	20: [31.2, 90.9]
};
const ROYALSUPPORT = {
	0: [72.85, 10.5],
	1: [77.20, 10.5],
	2: [81.55, 10.5],
	3: [85.95, 10.5],
	4: [90.30, 10.5],
	5: [94.60, 10.5],
	6: [77.20, 20.75],
	7: [81.55, 20.75],
	8: [85.95, 20.75],
	9: [90.30, 20.75],
	10: [94.60, 20.75]
};
const IMPULSE = {
	0: [40.2, 91.0],
	1: [44.2, 91.0],
	2: [47.9, 91.0],
	3: [51.8, 91.0],
	4: [55.9, 91.0],
	5: [59.7, 91.0]
};
const COLORS = {Indigenous: '#0000ffC0', Spanish: '#ff0000C0', combat: '#000000c0'};
//
const MOVEMENT = 0;
const DRAGANDDROP = 1;
const RETREAT = 2;
