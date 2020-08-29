const express = require('express');
const router = express.Router();
const helper = require("../helpers/helper");
const db = require("../helpers/database");

router.get('/', function (req, res, _) {
	res.send({
		"message": "Welcome",
	});
});

router.post("/login", async (req, res, _) => {
	if (req.body.hasOwnProperty("username") === false || !req.body["username"]) {
		res.send(helper.invalid_response("Invalid username"));
		return res.sendStatus(400);
	}

	if (req.body.hasOwnProperty("password") === false || !req.body["password"]) {
		res.send(helper.invalid_response("Invalid password"));
		return res.sendStatus(400);
	}

	const loginResult = await db.login(req.body["username"], req.body["password"]);

	if (loginResult.success === false) {
		res.send(helper.invalid_response("Login operation failed."))
		return res.sendStatus(400);
	}

	if (loginResult.hasOwnProperty("rows") === false || loginResult.rows.hasOwnProperty("id") === false) {
		res.send(helper.invalid_response("Account doesn't exist."))
		return res.sendStatus(400);
	}

	const user = await db.getUserData(loginResult.rows.id);
	if (user.hasOwnProperty("rows") === false || user.rows.hasOwnProperty("id") === false) {
		res.send(helper.invalid_response("Account doesn't exist."))
		return res.sendStatus(400);
	}

	if (parseInt(user.rows.active) === 0) {
		res.send(helper.invalid_response("Account is not active. Contact application administrator."))
		return res.sendStatus(400);
	}

	const tokenData = helper.generate_token(user.rows);
	res.send(tokenData);
	res.sendStatus(200);
});

router.post("/register", async (req, res, _) => {
	res.send(helper.invalid_response("NOT IMPLEMENTED!"));
	res.sendStatus(500);
});

module.exports = router;
