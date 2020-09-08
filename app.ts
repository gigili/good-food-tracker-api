const debug = require('debug')('good-food-tracker:server');
const http = require('http');
const port = normalizePort(process.env.PORT || '3000');
const express = require('express');
const path = require('path');
const cookieParser = require('cookie-parser');
const fileUpload = require("express-fileupload");
require('dotenv').config();

const indexRouter = require('./routes/index');
const restaurantRouter = require('./routes/restaurant');
const profileRoutes = require('./routes/profile');

const app = express();
app.set('port', port);

if (parseInt(process.env.DEVELOPMENT || "0") === 1) {
	const logger = require('morgan');
	app.use(logger('dev'));
}

app.use(express.json());
app.use(express.urlencoded({extended: false}));
app.use(cookieParser());
app.use(fileUpload({
	limits: {fileSize: 5 * 1024 * 1024}, //5MB File limit
}));
app.use(express.static(path.join(__dirname, 'public')));

app.use('/', indexRouter);
app.use('/restaurant', restaurantRouter);
app.use('/profile', profileRoutes);

const server = http.createServer(app);

server.listen(port);
server.on('error', onError);
server.on('listening', onListening);

//module.exports = app;

function normalizePort(val){
	const port = parseInt(val.toString(), 10);

	if (isNaN(port)) {
		// named pipe
		return val;
	}

	if (port >= 0) {
		// port number
		return port;
	}

	return false;
}
function onError(error){
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
function onListening(){
	const addr = server.address();
	const bind = typeof addr === 'string'
		? 'pipe ' + addr
		: 'port ' + addr.port;
	debug('Listening on ' + bind);
}