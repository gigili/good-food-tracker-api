export {};
import {NextFunction, Response} from "express";
import {Request} from "../helpers/interfaces/request";

const express = require("express");
const router = express.Router();
const reviewModel = require("../helpers/database/models/review");
const utilities = require("../helpers/utilities");
const validation = require("../helpers/validation");
const translate = require("../helpers/translation");

router.get("/", utilities.authenticateToken, async (req: Request, res: Response, _: NextFunction) => {
	const startLimit: number = req.query.start ? parseInt(req.query.start.toString()) : 0;
	const endLimit: number = req.query.limit ? parseInt(req.query.limit.toString()) : parseInt(process.env.PER_PAGE || "10");

	const userID = req.user ? req.user.guid : "";
	const data = await reviewModel.list(userID, startLimit, endLimit);

	if (typeof data === "string") {
		return res.status(400).send(utilities.invalid_response(data));
	} else if (!data.success) {
		return res.status(500).send(utilities.invalid_response(translate("unable_to_load_review")));
	}

	res.send({
		success: data.success,
		data: data.data,
		total: data.total,
		message: data.message || ""
	});
});

router.get("/:reviewID", utilities.authenticateToken, async (req: Request, res: Response, _: NextFunction) => {
	const reviewGuid: string = String(req.query.reviewID) || "";
	const review = await reviewModel.get(reviewGuid);

	if (typeof review === "string") {
		return res.status(400).send(utilities.invalid_response(review));
	} else if (!review.success) {
		return res.status(500).send(utilities.invalid_response(translate("unable_to_load_review")));
	} else if (review.success && !review.data.hasOwnProperty("guid")) {
		return res.status(404).send(utilities.invalid_response(translate("review_not_found")));
	}

	res.send({
		success: review.success,
		data: review.data,
		message: review.message || ""
	});
});

router.post("/", utilities.authenticateToken, async (req: Request, res: Response, _: NextFunction) => {
	const validationResult = validation.validate([
		[req.body.dish_name, translate("dish_name"), ["required", {"min_length": 3}]],
	]);

	if (validationResult.length > 0) {
		return res.status(400).send(utilities.invalid_response(validationResult));
	}

	const review = await reviewModel.create({
		...req.body,
		images: req.files ? req.files.images : [],
		userID: req.user ? req.user.guid : ""
	});

	res.status(review.success ? 201 : 500).send({
		success: review.success,
		message: review.success ? translate("review_created_success") : translate("review_created_error")
	});
});

module.exports = router;
