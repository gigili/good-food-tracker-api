const express = require('express');
const router = express.Router();
const helper = require("../helpers/helper");
const db = require("../helpers/database");

router.get('/', function (req, res, next) {
	res.send({
		"message": "Welcome",
	});
});

router.post("/login", async (req, res, next) => {
	if (req.body.hasOwnProperty("username") === false || !req.body["username"]) {
		return res.send(helper.invalid_response("Invalid username"));
	}

	if (req.body.hasOwnProperty("password") === false || !req.body["password"]) {
		return res.send(helper.invalid_response("Invalid password"));
	}

	const loginResult = await db.login(req.body["username"], req.body["password"]);

	if (loginResult.success === false) {
		return res.send(helper.invalid_response("Login operation failed."))
	}

	if (loginResult.hasOwnProperty("rows") === false || loginResult.rows.hasOwnProperty("id") === false) {
		return res.send(helper.invalid_response("Account doesn't exist."))
	}

	const user = await db.getUserData(loginResult.rows.id);
	if (user.hasOwnProperty("rows") === false || user.rows.hasOwnProperty("id") === false) {
		return res.send(helper.invalid_response("Account doesn't exist."))
	}

	if (parseInt(user.rows.active) === 0) {
		return res.send(helper.invalid_response("Account is not active. Contact application administrator."))
	}

	const tokenData = helper.generate_token(user.rows);
	res.send(tokenData);
});

module.exports = router;
