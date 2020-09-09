export {};
import {NextFunction, Request, Response} from "express";

const express = require("express");
const router = express.Router();
const countryModel = require("../helpers/database/models/country");
const helper = require("../helpers/helper");
const validation = require("../helpers/validation");
const ROLES = require("../helpers/roles");
const translate = require("../helpers/translation");

router.get("/", helper.authenticateToken, async (req: Request, res: Response, _: NextFunction) => {
	const startLimit = req.query.start || 0;
	const endLimit = req.query.limit || process.env.PER_PAGE;

	const data = await countryModel.list(startLimit, endLimit);

	if (data.success === false) {
		return res.status(500).send(helper.invalid_response(translate("unable_to_load_countries")));
	}

	res.send({
		"success": data.success,
		"data": data.data,
		"total": data.total,
		"message": data.message || ""
	});
});

router.get("/:countryID", helper.authenticateToken, async (req: Request, res: Response, _: NextFunction) => {
	const data = await countryModel.get(req.params["countryID"] || 0);

	if (data.success === false) {
		return res.status(500).send(helper.invalid_response(translate("unable_to_load_country")));
	}

	if (data.rows.hasOwnProperty("id") === false || data.rows.id < 1) {
		return res.status(404).send(helper.invalid_response(translate("country_not_found")));
	}

	res.send({
		"success": data.success,
		"data": data.rows,
		"message": ""
	});
});

router.post("/", (req: Request, res: Response, nx: NextFunction) => {
	helper.authenticateToken(req, res, nx, ROLES.Admin);
}, async (req: Request, res: Response, _: NextFunction) => {
	const name = req.body.name;

	const nameValidation = validation.validate([[name, translate("name"), ["required", {"min_length": 3}]]]);

	if (nameValidation.length > 0) {
		return res.status(400).send(helper.invalid_response(nameValidation));
	}

	const result = await countryModel.create(req.body);
	if (result.success === false) {
		return res.status(500).send(helper.invalid_response(translate("unable_to_create_country")));
	}

	res.status(201).send({
		"success": true,
		"message": translate("country_created_success")
	});
});

router.patch("/:countryID", (req: Request, res: Response, nx: NextFunction) => {
	helper.authenticateToken(req, res, nx, ROLES.Admin);
}, async (req: Request, res: Response, _: NextFunction) => {
	const {name, code} = req.body;
	const nameValidation = validation.validate([[name, translate("name"), ["required", {"min_length": 3}]]]);

	if (nameValidation.length > 0) {
		return res.status(400).send(helper.invalid_response(nameValidation));
	}

	const data = {
		name: name,
		code: code,
		countryID: req.params["countryID"]
	}

	const result = await countryModel.update(data);
	if (result.success === false) {
		return res.status(500).send(helper.invalid_response(translate("unable_to_update_country")));
	}

	res.status(200).send({
		"success": true,
		"message": translate("country_update_success")
	});
});

router.delete("/:countryID", (req, res, nx) => {
	helper.authenticateToken(req, res, nx, ROLES.Admin);
}, async (req: Request, res: Response, _: NextFunction) => {
	const data = await countryModel.delete(req.params["countryID"] || 0);

	if (data.success === false) {
		return res.status(500).send(helper.invalid_response(translate("unable_to_delete_country")));
	}

	res.send({
		"success": data.success,
		"data": [],
		"message": translate("country_delete_success")
	});
});

module.exports = router;
