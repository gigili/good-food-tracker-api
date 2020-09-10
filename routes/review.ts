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
	const userID = req["user"]["guid"];
	const data = await reviewModel.list(userID, startLimit, endLimit);

	if(typeof data === "string"){
		return res.status(400).send(helper.invalid_response(data));
	}else if (data.success === false) {
		return res.status(500).send(helper.invalid_response(translate("unable_to_load_review")));
	}

	res.send({
		"success": data.success,
		"data": data.data,
		"total": data.total,
		"message": data.message || ""
	});
});

router.get("/:reviewID", helper.authenticateToken, async (req: Request, res: Response, _: NextFunction) => {
	const reviewGuid = req.query.reviewID || null;
	const review = await reviewModel.get(reviewGuid);

	if(typeof review === "string"){
		return res.status(400).send(helper.invalid_response(review));
	}else if (review.success === false) {
		return res.status(500).send(helper.invalid_response(translate("unable_to_load_review")));
	}else if(review.success === true && review.rows.hasOwnProperty("guid") === false){
		return res.status(404).send(helper.invalid_response(translate("review_not_found")));
	}

	res.send({
		"success": review.success,
		"data": review.data,
		"message": review.message || ""
	});
});

router.post("/", helper.authenticateToken, async (req: Request, res: Response, _: NextFunction) => {
	res.send({
		"success": true,
		"data": [],
		"message": ""
	});
});

module.exports = router;
