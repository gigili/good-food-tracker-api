const express = require('express');
const router = express.Router();
const helper = require("../helpers/helper");
const dbUtils = require("../helpers/database/dbUtils");
const validation = require("../helpers/validation");

router.get('/', function (req, res, _) {
	res.send({
		"message": "Welcome",
	});
});

router.post("/login", async (req, res, _) => {
	if (req.body.hasOwnProperty("username") === false || !req.body["username"]) {
		return res.status(400).send(helper.invalid_response("Invalid username"));
	}

	if (req.body.hasOwnProperty("password") === false || !req.body["password"]) {
		return res.status(400).send(helper.invalid_response("Invalid password"));
	}

	const loginResult = await dbUtils.login(req.body["username"], req.body["password"]);

	if (loginResult.success === false) {
		return res.status(400).send(helper.invalid_response("Login operation failed."))
	}

	if (loginResult.hasOwnProperty("rows") === false || loginResult.rows.hasOwnProperty("id") === false) {
		return res.status(400).send(helper.invalid_response("Account doesn't exist."))
	}

	const user = await dbUtils.getUserData(loginResult.rows.id);
	if (user.hasOwnProperty("rows") === false || user.rows.hasOwnProperty("id") === false) {
		return res.status(400).send(helper.invalid_response("Account doesn't exist."))
	}

	if (parseInt(user.rows.active) === 0) {
		return res.status(400).send(helper.invalid_response("Account is not active. Contact application administrator."))
	}

	const tokenData = helper.generate_token(user.rows);
	return res.status(200).send({
		"success": true,
		"data": tokenData
	});
});

router.post("/register", async (req, res, _) => {
	const {name, email, username, password} = req.body

	const nameValidationMessages = validation.validate(name, "Name", [
		"required",
		{"min_length": 5}
	], true);

	const emailValidationMessages = validation.validate(email, "E-mail", [
		"required",
		{"min_length": 5},
		"valid_email"
	], true);

	const usernameValidationMessages = validation.validate(username, "Username", [
		"required",
		{"min_length": 3},
	], true);

	const passwordValidationMessages = validation.validate(password, "Password", [
		"required",
		{"min_length": 10},
	], true);

	if(nameValidationMessages.length > 0){
		return res.status(400).send(helper.invalid_response(nameValidationMessages));
	}

	if(emailValidationMessages.length > 0){
		return res.status(400).send(helper.invalid_response(emailValidationMessages));
	}

	if(usernameValidationMessages.length > 0){
		return res.status(400).send(helper.invalid_response(usernameValidationMessages));
	}

	if(passwordValidationMessages.length > 0){
		return res.status(400).send(helper.invalid_response(passwordValidationMessages));
	}

	const result = await dbUtils.registerUser({name, email, username, password});

	if(result === true){
		return res.status(201).send({
			"success": true,
			"message": "Account created successfully"
		});
	}

	return res.status(400).send(helper.invalid_response(result));
});

module.exports = router;
