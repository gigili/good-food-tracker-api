export {};
import {NextFunction, Request, Response} from "express";

const express = require("express");
const router = express.Router();
const restaurantModel = require("../helpers/database/models/restaurant");
const utilities = require("../helpers/utilities");
const validation = require("../helpers/validation");
const ROLES = require("../helpers/roles");
const translate = require("../helpers/translation");

router.get("/", utilities.authenticateToken(), async (req: Request, res: Response, _: NextFunction) => {
	const startLimit: number = req.query.start ? parseInt(req.query.start.toString()) : 0;
	const endLimit: number = req.query.limit ? parseInt(req.query.limit.toString()) : parseInt(process.env.PER_PAGE || "10");

	const restaurants = await restaurantModel.list(startLimit, endLimit);

	if (!restaurants.success) {
		return res.status(500).send(utilities.invalid_response(translate("unable_to_load_restaurants")));
	}

	res.send({
		"success": restaurants.success,
		"data": restaurants.data,
		"total": restaurants.total
	});
});

router.get("/:restaurantID", utilities.authenticateToken(), async (req: Request, res: Response, _: NextFunction) => {
	const restaurant = await restaurantModel.get(req.params["restaurantID"] || "");

	if (!restaurant.success) {
		return res.status(500).send(utilities.invalid_response(translate("unable_to_load_restaurant")));
	}

	if (!restaurant.data.hasOwnProperty("id") || restaurant.data.id < 1) {
		return res.status(404).send(utilities.invalid_response(translate("restaurant_not_found")));
	}

	res.send({
		"success": restaurant.success,
		"data": restaurant.data,
		"message": ""
	});
});

router.post("/", utilities.authenticateToken(), async (req: Request, res: Response, _: NextFunction) => {
	const name = req.body.name;

	const nameValidation = validation.validate([[name, translate("name"), ["required", {"min_length": 3}]]]);

	if (nameValidation.length > 0) {
		return res.status(400).send(utilities.invalid_response(nameValidation));
	}

	const result = await restaurantModel.create(req.body);
	if (!result.success) {
		return res.status(500).send(utilities.invalid_response(translate("unable_to_create_restaurant")));
	}

	res.status(201).send({
		"success": true,
		"message": translate("restaurant_created_success")
	});
});

router.patch("/:restaurantID", utilities.authenticateToken(), async (req: Request, res: Response, _: NextFunction) => {
	const name = req.body.name;

	const nameValidation = validation.validate([[name, translate("name"), ["required", {"min_length": 3}]]]);

	if (nameValidation.length > 0) {
		return res.status(400).send(utilities.invalid_response(nameValidation));
	}

	const data = req.body;
	Object.assign(data, req.params);

	const result = await restaurantModel.update(data);
	if (!result.success) {
		return res.status(500).send(utilities.invalid_response(translate("unable_to_update_restaurant")));
	}

	res.status(200).send({
		"success": true,
		"message": translate("restaurant_update_success")
	});
});

router.delete("/:restaurantID", utilities.authenticateToken(ROLES.Admin),
	async (req: Request, res: Response, _: NextFunction) => {
		const data = await restaurantModel.delete(req.params["restaurantID"] || "");

		if (!data.success) {
			return res.status(500).send(utilities.invalid_response(translate("unable_to_delete_restaurant")));
		}

		res.send({
			"success": data.success,
			"data": [],
			"message": translate("restaurant_delete_success")
		});
	});

module.exports = router;
