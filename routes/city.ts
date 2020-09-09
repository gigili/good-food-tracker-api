export {};
import {NextFunction, Request, Response} from "express";

const express = require("express");
const router = express.Router();
const cityModel = require("../helpers/database/models/city");
const helper = require("../helpers/helper");
const validation = require("../helpers/validation");
const ROLES = require("../helpers/roles");
const translate = require("../helpers/translation");

router.get("/", helper.authenticateToken, async (req: Request, res: Response, _: NextFunction) => {
	const startLimit = req.query.start || 0;
	const endLimit = req.query.limit || process.env.PER_PAGE;

	const data = await cityModel.list(startLimit, endLimit);

	if (data.success === false) {
		return res.status(500).send(helper.invalid_response(translate("unable_to_load_cities")));
	}

	res.send({
		"success": data.success,
		"data": data.data,
		"total": data.total,
		"message": data.message || ""
	});
});

router.get("/:cityID", helper.authenticateToken, async (req: Request, res: Response, _: NextFunction) => {
	const data = await cityModel.get(req.params["cityID"] || 0);

	if (data.success === false) {
		return res.status(500).send(helper.invalid_response(translate("unable_to_load_city")));
	}

	if (data.rows.hasOwnProperty("id") === false || data.rows.id < 1) {
		return res.status(404).send(helper.invalid_response(translate("city_not_found")));
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

	const result = await cityModel.create(req.body);
	if (result.success === false) {
		return res.status(500).send(helper.invalid_response(translate("unable_to_create_city")));
	}

	res.status(201).send({
		"success": true,
		"message": translate("city_created_success")
	});
});

router.patch("/:cityID", (req: Request, res: Response, nx: NextFunction) => {
	helper.authenticateToken(req, res, nx, ROLES.Admin);
}, async (req: Request, res: Response, _: NextFunction) => {
	const name = req.body.name;
	const nameValidation = validation.validate([[name, translate("name"), ["required", {"min_length": 3}]]]);

	if (nameValidation.length > 0) {
		return res.status(400).send(helper.invalid_response(nameValidation));
	}

	const data = {
		name: name,
		cityID: req.params["cityID"]
	}

	const result = await cityModel.update(data);
	if (result.success === false) {
		return res.status(500).send(helper.invalid_response(translate("unable_to_update_city")));
	}

	res.status(200).send({
		"success": true,
		"message": translate("city_update_success")
	});
});

router.delete("/:cityID", (req, res, nx) => {
	helper.authenticateToken(req, res, nx, ROLES.Admin);
}, async (req: Request, res: Response, _: NextFunction) => {
	const data = await cityModel.delete(req.params["cityID"] || 0);

	if (data.success === false) {
		return res.status(500).send(helper.invalid_response(translate("unable_to_delete_city")));
	}

	res.send({
		"success": data.success,
		"data": [],
		"message": translate("city_delete_success")
	});
});

module.exports = router;
