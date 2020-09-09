export {};
import {NextFunction, Request, Response} from "express";

const express = require("express");
const router = express.Router();
const reviewModel = require("../helpers/database/models/review");
const helper = require("../helpers/helper");
const validation = require("../helpers/validation");
const ROLES = require("../helpers/roles");
const translate = require("../helpers/translation");

router.get("/", helper.authenticateToken, async (req: Request, res: Response, _: NextFunction) => {
	const startLimit = req.query.start || 0;
	const endLimit = req.query.limit || process.env.PER_PAGE;

	const data = await reviewModel.list(startLimit, endLimit);

	if (data.success === false) {
		return res.status(500).send(helper.invalid_response(translate("unable_to_load_reviews")));
	}

	res.send({
		"success": data.success,
		"data": data.data,
		"total": data.total,
		"message": data.message || ""
	});
});

module.exports = router;
