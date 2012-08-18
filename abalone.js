var _websocket = require('websocket'),
	WebSocketServer = _websocket.server,
	WSC = _websocket.connection.prototype,
	HTTPServer = require('http').Server,
	WebSocketConnectionManager = require('wsconnmgr'),
	MysqlClient = require('mysql'),
	util = require('util'),
	httpServer,
	wsServer,
	connection,
	games,
	lastGame;

function log(msg) {
	console.log.apply(console, arguments);
}

function inspect(ass, depth) {
	undefined === depth && (depth = 3);
	return console.log('string' == typeof ass ? ass : util.inspect(ass, false, depth, true));
}

WSC.sendJSON = function(data, callback) {
	return this.send(JSON.stringify(data), callback);
};

WSC.sendCmd = function(cmd, data, action) {
	data = {cmd: cmd, data: data};
	if ( action ) {
		data.action = action;
		log('=> ' + action);
	}
	return this.sendJSON(data);
};



/**
 * Init HTTP server for initial connection/request
 */

httpServer = new HTTPServer(function(request, response) {
	log('Received request for ' + request.url);

	response.writeHead(404);
	response.end();
});

httpServer.listen(8083, function() {
	var port = this._connectionKey.split(':')[2];
	log('Server is listening on port ' + port);
});



/**
 * Database
 */

var db = MysqlClient.createConnection({
	host: 'localhost',
	user: 'root',
	password: 'rybinsk',
	database: 'games',
});

// Truncate that shit
db.query('DELETE FROM abalone_players');
db.query('ALTER TABLE abalone_players AUTO_INCREMENT = 1');
db.query('DELETE FROM abalone_games');
db.query('ALTER TABLE abalone_games AUTO_INCREMENT = 1');



/**
 * Games manager
 */

games = [];
games.add = function() {
	// New manager
	var game = new WebSocketConnectionManager({
		db_game_id: 0,
		db_player_id: 0,
		name: '',
		getOpponent: function() {
			return this.data.game.allButId(this.data.id)[0];
		},
	});

	// Add to stack
	this.push(game);

	return game;
};

function newGame(client, callback) {
	db.query('INSERT INTO abalone_games SET ?', {
		turn: Math.random() > 0.5 ? 'white' : 'black'
	}, function(err, result) {
		callback(result.insertId);
	});
}

function newPlayer(client, gameId, callback) {
	var color = client.data.color;
	db.query('INSERT INTO abalone_players SET ?', {
		game_id: gameId,
		color: color,
		username: client.data.id,
		password: '',
	}, function(err, result) {
		callback(result.insertId);
	});
}

function getGameStatus(playerId, callback) {
	db.query('SELECT * FROM abalone_players WHERE id = ' + playerId, function(err, rows) {
		var player = rows[0],
			gameId = player.game_id;
		db.query('SELECT * FROM abalone_games WHERE id = ' + gameId, function(err, rows) {
			var game = rows[0];
			q = db.query('SELECT * FROM abalone_players WHERE game_id = ' + gameId + ' AND id <> ' + playerId, function(err, rows) {
				var opponent = rows[0];
				callback(player, game, opponent);
			});
		});
	});
}

function sendGameStatus(client, action) {
	// Get game status
	var playerId = client.data.db_player_id;
	getGameStatus(playerId, function(player, game, opponent) {
		var status = {
			waiting_for_turn: game.turn != player.color,
			waiting_for_player: !opponent,
			color: player.color,
			name: player.username,
			opponent_name: opponent ? opponent.username : '',
			balls: [],
		};
		client.sendCmd('status', status, action);
	});
}



/**
 * Init WebSocket server to accept TCP connections
 */

wsServer = new WebSocketServer({
	httpServer: httpServer
});

wsServer.on('request', function(request) {

	/**
	 * Create new (personal) TCP connection for client
	 */

	// Create connection
	var client = request.accept();

	// Create new game
	if ( !lastGame || 2 == lastGame.size() ) {
client.send('You are player 1 (white)');
		lastGame = games.add();
		lastGame.add(client);
		client.data.color = 'white';

		newGame(client, function(dbGameId) {
client.send('I created a game for you: ' + dbGameId);
			lastGame.data.db_game_id = dbGameId;
			client.data.db_game_id = dbGameId;

			newPlayer(client, dbGameId, function(dbPlayerId) {
client.send('I created a player for you: ' + dbPlayerId);
				client.data.db_player_id = dbPlayerId;

				// Send game status
				sendGameStatus(client, 'created game');
			});
		});
	}
	// Add to existing game
	else {
client.send('You are player 2 (black)');
		lastGame.add(client);
		client.data.db_game_id = lastGame.data.db_game_id;
		client.data.color = 'black';

		newPlayer(client, lastGame.data.db_game_id, function(dbPlayerId) {
client.send('I created a player for you: ' + dbPlayerId);
			client.data.db_player_id = dbPlayerId;

			var opponent = client.data.getOpponent();
			client.data.opponent = opponent;
			opponent.data.opponent = client;

			// Send game status
			sendGameStatus(client, 'joined game');
			sendGameStatus(opponent, 'opponent joined game');
		});
	}

	client.data.game = lastGame;

	client.on('message', function(message) {
		if (message.type === 'utf8') {
log('Incoming: ' + message.utf8Data);
			var client = this,
				msg;
			try {
				msg = JSON.parse(message.utf8Data);
			}
			catch (ex) {
				return;
			}

			switch ( msg.cmd ) {
				case 'eval':
					eval(msg.data);
					break;

				case 'name':
					var name = msg.data,
						opponent = client.data.game.allButId(client.data.id)[0];

					// Save name
					client.data.name = name;

					// Notify opponent
					if ( opponent ) {
						opponent.sendCmd('opponent name', name);
					}
					break;
			}
		}
	});

	client.on('close', function(reasonCode, description) {
		// Drop game and tell other player to bounce
	});

}); // WebsocketServer onRequest
