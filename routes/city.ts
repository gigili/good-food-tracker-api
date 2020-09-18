import {NextFunction, Request, Response} from "express";

export {};

const express = require("express");
const router = express.Router();
const cityModel = require("../helpers/database/models/city");
const utilities = require("../helpers/utilities");
const validation = require("../helpers/validation");
const ROLES = require("../helpers/roles");
const translate = require("../helpers/translation");

router.get("/", utilities.authenticateToken, async (req: Request, res: Response, _: NextFunction) => {
	const startLimit: number = req.query.start ? parseInt(req.query.start.toString()) : 0;
	const endLimit: number = req.query.limit ? parseInt(req.query.limit.toString()) : parseInt(process.env.PER_PAGE || "10");

	const data = await cityModel.list(startLimit, endLimit);

	if (!data.success) {
		return res.status(500).send(utilities.invalid_response(translate("unable_to_load_cities")));
	}

	res.send({
		"success": data.success,
		"data": data.data,
		"total": data.total,
		"message": data.message || ""
	});
});

router.get("/:cityID", utilities.authenticateToken, async (req: Request, res: Response, _: NextFunction) => {
	const city = await cityModel.get(parseInt(req.params["cityID"]));

	if (!city.success) {
		return res.status(500).send(utilities.invalid_response(translate("unable_to_load_city")));
	}

	if (!city.data.hasOwnProperty("id") || city.data.id < 1) {
		return res.status(404).send(utilities.invalid_response(translate("city_not_found")));
	}

	res.send({
		"success": city.success,
		"data": city.data,
		"message": ""
	});
});

router.post("/", utilities.authenticateToken(ROLES.Admin),
	async (req: Request, res: Response, _: NextFunction) => {
		const name = req.body.name;

		const nameValidation = validation.validate([[name, translate("name"), ["required", {"min_length": 3}]]]);

		if (nameValidation.length > 0) {
			return res.status(400).send(utilities.invalid_response(nameValidation));
		}

		const result = await cityModel.create(req.body);
		if (!result.success) {
			return res.status(500).send(utilities.invalid_response(translate("unable_to_create_city")));
		}

		res.status(201).send({
			"success": true,
			"message": translate("city_created_success")
		});
	});

router.patch("/:cityID", utilities.authenticateToken(ROLES.Admin),
	async (req: Request, res: Response, _: NextFunction) => {
		const name = req.body.name;
		const nameValidation = validation.validate([[name, translate("name"), ["required", {"min_length": 3}]]]);

		if (nameValidation.length > 0) {
			return res.status(400).send(utilities.invalid_response(nameValidation));
		}

		const data = {
			name: name,
			cityID: parseInt(req.params["cityID"]),
			countryID: parseInt(req.params["countyID"])
		}

		const result = await cityModel.update(data);
		if (!result.success) {
			return res.status(500).send(utilities.invalid_response(translate("unable_to_update_city")));
		}

		res.status(200).send({
			"success": true,
			"message": translate("city_update_success")
		});
	});

router.delete("/:cityID", utilities.authenticateToken(ROLES.Admin),
	async (req: Request, res: Response, _: NextFunction) => {
		const data = await cityModel.delete(parseInt(req.params["cityID"]));

		if (!data.success) {
			return res.status(500).send(utilities.invalid_response(translate("unable_to_delete_city")));
		}

		res.send({
			"success": data.success,
			"data": [],
			"message": translate("city_delete_success")
		});
	});

module.exports = router;
