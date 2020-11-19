export {};
import {NextFunction, Request, Response} from "express";

const express = require("express");
const router = express.Router();
const countryModel = require("../helpers/database/models/country");
const utilities = require("../helpers/utilities");
const validation = require("../helpers/validation");
const ROLES = require("../helpers/roles");
const translate = require("../helpers/translation");

router.get("/", utilities.authenticate_token(), async (req: Request, res: Response, _: NextFunction) => {
	const startLimit: number = req.query.start ? parseInt(req.query.start.toString()) : 0;
	const endLimit: number = req.query.limit ? parseInt(req.query.limit.toString()) : parseInt(process.env.PER_PAGE || "10");

	const data = await countryModel.list(startLimit, endLimit);

	if (!data.success) {
		return res.status(500).send(utilities.invalid_response(translate("unable_to_load_countries")));
	}

	res.send({
		"success": data.success,
		"data": data.data,
		"total": data.total,
		"message": data.message || ""
	});
});

router.get("/:countryID", utilities.authenticate_token(), async (req: Request, res: Response, _: NextFunction) => {
	const countries = await countryModel.get(parseInt(req.params["countryID"]));

	if (!countries.success) {
		return res.status(500).send(utilities.invalid_response(translate("unable_to_load_country")));
	}

	if (!countries.data.hasOwnProperty("id") || countries.data.id < 1) {
		return res.status(404).send(utilities.invalid_response(translate("country_not_found")));
	}

	res.send({
		"success": countries.success,
		"data": countries.data,
		"message": ""
	});
});

router.post("/", utilities.authenticate_token(ROLES.Admin),
	async (req: Request, res: Response, _: NextFunction) => {
		const name = req.body.name;

		const nameValidation = validation.validate([[name, translate("name"), ["required", {"min_length": 3}]]]);

		if (nameValidation.length > 0) {
			return res.status(400).send(utilities.invalid_response(nameValidation));
		}

		const result = await countryModel.create(req.body);
		if (!result.success) {
			return res.status(500).send(utilities.invalid_response(translate("unable_to_create_country")));
		}

		res.status(201).send({
			"success": true,
			"message": translate("country_created_success")
		});
	});

router.patch("/:countryID", utilities.authenticate_token(ROLES.Admin),
	async (req: Request, res: Response, _: NextFunction) => {
		const {name, code} = req.body;
		const nameValidation = validation.validate([[name, translate("name"), ["required", {"min_length": 3}]]]);

		if (nameValidation.length > 0) {
			return res.status(400).send(utilities.invalid_response(nameValidation));
		}

		const data = {
			name: name,
			code: code,
			countryID: parseInt(req.params["countryID"])
		}

		const result = await countryModel.update(data);
		if (!result.success) {
			return res.status(500).send(utilities.invalid_response(translate("unable_to_update_country")));
		}

		res.status(200).send({
			"success": true,
			"message": translate("country_update_success")
		});
	});

router.delete("/:countryID", utilities.authenticate_token(ROLES.Admin),
	async (req: Request, res: Response, _: NextFunction) => {
		const data = await countryModel.delete(parseInt(req.params["countryID"]));

		if (!data.success) {
			return res.status(500).send(utilities.invalid_response(translate("unable_to_delete_country")));
		}

		res.send({
			"success": data.success,
			"data": [],
			"message": translate("country_delete_success")
		});
	});

module.exports = router;
