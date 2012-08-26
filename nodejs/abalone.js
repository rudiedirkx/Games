var _websocket = require('websocket'),
	WebSocketServer = _websocket.server,
	WSC = _websocket.connection.prototype,
	HTTPServer = require('http').Server,
	WebSocketConnectionManager = require('wsconnmgr'),
	MysqlClient = require('mysql'),
	dbConfig = require('./inc.abalone.js.php'),
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
 * Database
 */

var db = MysqlClient.createConnection({
	host: 'localhost',
	user: dbConfig.user,
	password: dbConfig.password,
	database: dbConfig.database,
});

db.on('error', function() {
	inspect(arguments);
});

// Truncate that shit
log('Truncating tables...');
var t=0;
['balls', 'players', 'games'].forEach(function(tbl) {
	db.query('DELETE FROM abalone_' + tbl, function() {
		db.query('ALTER TABLE abalone_' + tbl + ' AUTO_INCREMENT = 1', function() {
			2 == ++t && log('Done. Truncated tables.');
		});
	});
});



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
	var turn = Math.random() > 0.5 ? 'white' : 'black';
	db.query('INSERT INTO abalone_games SET ?', {
		turn: turn,
	}, function(err, result) {
		callback({id: result.insertId, turn: turn});
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

function getGameStatus(playerId, balls, callback) {
log(':: starting full stack fetch');
	db.query('SELECT * FROM abalone_players WHERE id = ' + playerId, function(err, rows) {
		var player = rows[0],
			gameId = player.game_id;
		db.query('SELECT * FROM abalone_games WHERE id = ' + gameId, function(err, rows) {
			var game = rows[0];
			q = db.query('SELECT * FROM abalone_players WHERE game_id = ' + gameId + ' AND id <> ' + playerId, function(err, rows) {
log(':: full stack fetch done');
				var opponent = rows[0];
				callback(player, game, opponent, false);
			});
		});
	});
}

function sendGameStatus(client, player, game, opponent, balls, action) {
	var status = {
		waiting_for_turn: game.turn != player.color,
		waiting_for_player: !opponent,
		color: player.color,
		name: player.username,
		opponent_name: opponent ? opponent.username : '',
		balls: balls,
	};
	client.sendCmd('status', status, action);
}

function startGame(white, black, callback) {
	var players = {black: black, white: white};
		balls = {"black":["1:1:5","2:1:4","3:1:3","4:1:2","5:1:1","1:2:6","2:2:5","3:2:4","4:2:3","5:2:2","6:2:1","3:3:5","4:3:4","5:3:3"],"white":["5:7:7","6:7:6","7:7:5","4:8:9","5:8:8","6:8:7","7:8:6","8:8:5","9:8:4","5:9:9","6:9:8","7:9:7","8:9:6","9:9:5"]},
		done = 0,
		ballsALaStatus = [];
	['white', 'black'].forEach(function(color) {
		var colorBalls = balls[color].map(function(ball) {
			ball = ball.split(':').map(parseFloat);
			ball.unshift(players[color].id);

			var ballALaStatus = ball.slice();
			ballALaStatus[0] = color;
			ballsALaStatus.push(ballALaStatus);

			return ball;
		});
		q = db.query('INSERT INTO abalone_balls (player_id, x, y, z) VALUES (?), (?), (?), (?), (?), (?), (?), (?), (?), (?), (?), (?), (?), (?)', colorBalls, function(err, result) {
			if ( 2 == ++done ) {
				callback(ballsALaStatus);
			}
		});
	});
}



/**
 * Init WebSocket server to accept TCP connections
 */

wsServer = new WebSocketServer({
	httpServer: new HTTPServer().listen(8083, function() {
		var port = this._connectionKey.split(':')[2];
		log('Server is listening on port ' + port);
	})
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
		client.data.name = client.data.id;

		newGame(client, function(game) {
			var dbGameId = game.id;
client.send('I created a game for you: ' + dbGameId);
			lastGame.data.db_game_id = dbGameId;
			client.data.db_game_id = dbGameId;
client.send("It's " + game.turn + "'s turn. " + ( 'white' == game.turn ? 'Yeeeh!' : 'Sorry =(' ));

			newPlayer(client, dbGameId, function(dbPlayerId) {
client.send('I created a player for you: ' + dbPlayerId);
				client.data.db_player_id = dbPlayerId;

				// Send game status
				getGameStatus(dbPlayerId, false, function(player, game, opponent) {
					sendGameStatus(client, player, game, opponent, [], 'created game');
				});
			});
		});
	}
	// Add to existing game
	else {
client.send('You are player 2 (black)');
		lastGame.add(client);
		client.data.db_game_id = lastGame.data.db_game_id;
		client.data.color = 'black';
		client.data.name = client.data.id;

		newPlayer(client, lastGame.data.db_game_id, function(dbPlayerId) {
client.send('I created a player for you: ' + dbPlayerId);
			client.data.db_player_id = dbPlayerId;

			var opponentClient = client.data.getOpponent();
			client.data.opponent = opponentClient;
			opponentClient.data.opponent = client;

			getGameStatus(dbPlayerId, false, function(player, game, opponent) {
				// Start game
				startGame(opponent, player, function(balls) {
					// Send game status
					sendGameStatus(client, player, game, opponent, balls, 'joined game');
					sendGameStatus(opponentClient, opponent, game, player, balls, 'opponent joined game');
				});
			});
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
					var name = String(msg.data),
						opponent = client.data.game.allButId(client.data.id)[0];

					// Save name locally
					client.data.name = name;

					// Save name in database
					db.query('UPDATE abalone_players SET ? WHERE id = ?', [{username: name}, client.data.db_player_id], function() {
						// Notify opponent
						if ( opponent ) {
							opponent.sendCmd('names', {
								//name: opponent.data.name,
								opponent_name: name,
							});
						}
					});

					break;
			}
		}
	});

	client.on('close', function(reasonCode, description) {
		// Drop game and tell other player to bounce
	});

}); // WebsocketServer onRequest
