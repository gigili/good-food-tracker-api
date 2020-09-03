const express = require("express");
const router = express.Router();
const restaurantModel = require("../helpers/database/models/restaurant");
const helper = require("../helpers/helper");
const validation = require("../helpers/validation");
const translate = require("../helpers/translation");

router.get("/", async (req, res, _) => {
	const startLimit = req.query.start || 0;
	const endLimit = req.query.limit || process.env.PER_PAGE;

	const data = await restaurantModel.list(startLimit, endLimit);

	if (data.success === false) {
		return res.status(500).send(helper.invalid_response(translate("unable_to_load_restaurants")));
	}

	res.send({
		"success": data.success,
		"data": data.restaurants,
		"total": data.total,
		"message": data.message || ""
	});
});

router.get("/:restaurantID", async (req, res, _) => {
	const data = await restaurantModel.get(req.params["restaurantID"] || 0);

	if (data.success === false) {
		return res.status(500).send(helper.invalid_response(translate("unable_to_load_restaurant")));
	}

	if (data.rows.hasOwnProperty("id") === false || data.rows.id < 1) {
		return res.status(404).send(helper.invalid_response(translate("restaurant_not_found")));
	}

	res.send({
		"success": data.success,
		"data": data.rows,
		"message": ""
	});
});

router.post("/", helper.authenticateToken, async (req, res, _) => {
	const name = req.body.name;

	const nameValidation = validation.validate([[name, translate("name"), ["required", {"min_length": 3}]]]);

	if (nameValidation.length > 0) {
		return res.status(400).send(helper.invalid_response(nameValidation));
	}

	const result = await restaurantModel.create(req.body);

	if (result.success === false) {
		return res.status(500).send(helper.invalid_response(translate("unable_to_create_restaurant")));
	}

	res.status(201).send({
		"success": true,
		"message": translate("restaurant_created_success")
	});
});

router.patch("/:restaurantID", helper.authenticateToken, async (req, res, _) => {
	const name = req.body.name;

	const nameValidation = validation.validate([[name, translate("name"), ["required", {"min_length": 3}]]]);

	if (nameValidation.length > 0) {
		return res.status(400).send(helper.invalid_response(nameValidation));
	}

	const data = req.body;
	Object.assign(data, req.params);

	const result = await restaurantModel.update(data);
	if (result.success === false) {
		return res.status(500).send(helper.invalid_response(translate("unable_to_update_restaurant")));
	}

	res.status(200).send({
		"success": true,
		"message": translate("restaurant_update_success")
	});
});

router.delete("/:restaurantID", helper.authenticateToken, async (req, res, _) => {
	const data = await restaurantModel.delete(req.params["restaurantID"] || 0);

	if (data.success === false) {
		return res.status(500).send(helper.invalid_response(translate("unable_to_delete_restaurant")));
	}

	res.send({
		"success": data.success,
		"data": [],
		"message": translate("restaurant_delete_success")
	});
});

module.exports = router;
