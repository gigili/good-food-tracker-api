const app = require("./app");
const debug = require('debug')('good-food-tracker:server');
const http = require('http');
const port = normalizePort(process.env.PORT || '3001');
const server = http.createServer(app);

app.set('port', port);

server.listen(port);
server.on('error', onError);
server.on('listening', onListening);

function normalizePort(val: string | number): string | number | boolean {
	const port = parseInt(val.toString(), 10);

	if (isNaN(port)) {
		return val;
	}

	// named pipe
	if (port >= 0) {
		// port number
		return port;
	}

	return false;
}

function onError(error: { syscall: string, code: string }): void {
	if (error.syscall !== 'listen') {
		throw error;
	}

	const bind = typeof port === 'string'
		? 'Pipe ' + port
		: 'Port ' + port;

	// handle specific listen errors with friendly messages
	switch (error.code) {
		case 'EACCES':
			console.error(bind + ' requires elevated privileges');
			process.exit(1);
			break;
		case 'EADDRINUSE':
			console.error(bind + ' is already in use');
			process.exit(1);
			break;
		default:
			throw error;
	}
}

function onListening(): void {
	const addr = server.address();
	const bind = typeof addr === 'string'
		? 'pipe ' + addr
		: 'port ' + addr.port;

	if (parseInt(process.env.DEVELOPMENT || "0") === 1) {
		debug('Listening on ' + bind);
	}
}
