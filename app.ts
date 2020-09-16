import {NextFunction, Request, Response} from "express";
import {Globals} from "./helpers/globals";

export {};

const express = require('express');
const path = require('path');
const cors = require('cors');
const cookieParser = require('cookie-parser');
const fileUpload = require("express-fileupload");
require('dotenv').config();

const indexRouter = require('./routes/index');
const restaurantRouter = require('./routes/restaurant');
const profileRoutes = require('./routes/profile');
const cityRoutes = require('./routes/city');
const countryRoutes = require('./routes/country');
const reviewRoutes = require('./routes/review');

const app = express();

app.use(cors({
	"origin": "*",
	"methods": "GET,HEAD,PUT,PATCH,POST,DELETE",
	"preflightContinue": false,
	"optionsSuccessStatus": 204
}));

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

app.use(function (req: Request, res: Response, next: NextFunction) {
	let langOptions = [req.body.lang, req.query.lang, req.params.lang];
	let lang: string = "english";

	for (const x of langOptions) {
		if (typeof x !== "undefined" && x !== null && x !== "") {
			lang = x;
			break;
		}
	}

	Object.assign(req, {lang});
	Globals.getInstance().language = lang;
	next();
})

app.use('/', indexRouter);
app.use('/restaurant', restaurantRouter);
app.use('/profile', profileRoutes);
app.use('/city', cityRoutes);
app.use('/country', countryRoutes);
app.use('/review', reviewRoutes);

module.exports = app;