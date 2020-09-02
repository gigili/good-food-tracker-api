const express = require('express');
const path = require('path');
const cookieParser = require('cookie-parser');
require('dotenv').config();

String.prototype.format = String.prototype.format || function () {
	let str = this.toString();
	if (arguments.length) {
		const t = typeof arguments[0];
		const args = ("string" === t || "number" === t) ? Array.prototype.slice.call(arguments) : arguments[0];

		for (let key in args) {
			const replaceValue = (key && args.hasOwnProperty(key) === true) ? args[key] : "";
			str = str.replace(new RegExp("\\{" + key + "\\}", "gi"), replaceValue.toString());
		}
	}

	return str;
};

const indexRouter = require('./routes/index');
const restaurantRouter = require('./routes/restaurant');
const app = express();

if(parseInt(process.env.DEVELOPMENT) === 1) {
	const logger = require('morgan');
	app.use(logger('dev'));
}

app.use(express.json());
app.use(express.urlencoded({extended: false}));
app.use(cookieParser());
app.use(express.static(path.join(__dirname, 'public')));

app.use('/', indexRouter);
app.use('/restaurant', restaurantRouter);

module.exports = app;
