export {};

const express = require('express');
const path = require('path');
const cookieParser = require('cookie-parser');
const fileUpload = require("express-fileupload");
require('dotenv').config();

const indexRouter = require('./routes/index');
const restaurantRouter = require('./routes/restaurant');
const profileRoutes = require('./routes/profile');
const cityRoutes = require('./routes/city');
const countryRoutes = require('./routes/country');

const app = express();

if (parseInt(process.env.DEVELOPMENT || "0") === 1) {
	const logger = require('morgan');
	app.use(logger('dev'));
}

app.use(express.json());
app.use(express.urlencoded({extended: false}));
app.use(cookieParser());
app.use(express.static(path.join(__dirname, 'public')));
app.use(fileUpload({
	limits: {
		fileSize: 5 * 1024 * 1024 //5MB File limit
	},
}));

app.use('/', indexRouter);
app.use('/restaurant', restaurantRouter);
app.use('/profile', profileRoutes);
app.use('/city', cityRoutes);
app.use('/country', countryRoutes);

module.exports = app;