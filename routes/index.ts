export {};
import {NextFunction, Request, Response} from "express";

const express = require("express");
const router = express.Router();
const utilities = require("../helpers/utilities");
const validation = require("../helpers/validation");
const translate = require("../helpers/translation");
const userModel = require("../helpers/database/models/user");

router.get("/", function (req: Request, res: Response, _: NextFunction) {
	res.send({
		"message": translate("welcome"),
	});
});

router.post("/login", async (req: Request, res: Response, _: NextFunction) => {
	const userModel = require("../helpers/database/models/user");

	const validationResults = validation.validate([
		[req.body.username, translate("username"), ["required", {"min_length": 3}]],
		[req.body.password, translate("password"), ["required", {"min_length": 10}]],
	]);

	if (validationResults.length > 0) {
		return res.status(400).send(utilities.invalid_response(validationResults));
	}

	const loginResult = await userModel.login(req.body["username"], req.body["password"]);

	if (!loginResult.success) {
		return res.status(400).send(utilities.invalid_response(translate("login_failed")));
	}

	if (!loginResult.hasOwnProperty("data") || !loginResult.data.hasOwnProperty("guid")) {
		return res.status(400).send(utilities.invalid_response(translate("account_doesnt_exist")));
	}

	const user = await userModel.get(loginResult.data["guid"]);
	if (!user.hasOwnProperty("data") || !user.data.hasOwnProperty("guid")) {
		return res.status(400).send(utilities.invalid_response(translate("account_doesnt_exist")));
	}

	const rolesResult = await userModel.getRoles(user.data["guid"]);
	if (rolesResult.success) {
		if (rolesResult.data.hasOwnProperty("name")) {
			Object.assign(user.data, {power: rolesResult.data.power});
		}
	}

	if (parseInt(user.data.active) === 0) {
		return res.status(400).send(utilities.invalid_response(translate("account_not_active")));
	}

	const tokenData = utilities.generate_token(user.data);
	return res.status(200).send({
		"success": true,
		"data": tokenData
	});
});

router.post("/register", async (req: Request, res: Response, _: NextFunction) => {
	const {name, email, username, password} = req.body;

	const validationResults = validation.validate([
		[name, translate("name"), ["required", {"min_length": 5}]],
		[email, translate("email"), ["required", {"min_length": 5}, "valid_email"]],
		[username, translate("username"), ["required", {"min_length": 3}]],
		[password, translate("password"), ["required", {"min_length": 10}]]
	], true);

	if (validationResults.length > 1) {
		return res.status(400).send(utilities.invalid_response(validationResults));
	}

	const result = await userModel.registerUser({name, email, username, password});

	if (result === true) {
		return res.status(201).send({
			"success": true,
			"message": translate("account_created_success")
		});
	}

	return res.status(400).send(utilities.invalid_response(result));
});

module.exports = router;
