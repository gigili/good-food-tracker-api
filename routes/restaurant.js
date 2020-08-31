const express = require('express');
const router = express.Router();
const restaurantModel = require("../helpers/database/models/restaurant");
const helper = require("../helpers/helper");
const validation = require("../helpers/validation");

router.get('/', async (req, res, _) => {
	const startLimit = req.body.start || 0;
	const endLimit = req.body.limit || process.env.PER_PAGE;

	const data = await restaurantModel.list(startLimit, endLimit);

	if (data.success === false) {
		return res.status(500).send(helper.invalid_response(""));
	}

	res.send({
		"success": data.success,
		"data": data.restaurants,
		"total": data.total,
		"message": data.message || ""
	});
});

router.post('/', async (req, res, _) => {
	const name = req.body.name;

	const nameValidation = validation.validate(name, "Name", ["required", {"min_length": 3}]);

	if (nameValidation.length > 0) {
		return res.status(400).send(helper.invalid_response(nameValidation));
	}

	const result = await restaurantModel.create(req.body);

	if (result.success === false) {
		return res.status(500).send(helper.invalid_response("Unable to create a new restaurant"));
	}

	res.status(201).send({
		"success": true,
		"message": "Restaurant created successfully"
	});
});

router.get('/:restaurantID', async (req, res, _) => {
	res.send({
		"message": "READ restaurant",
	});
});

router.patch('/:restaurantID', async (req, res, _) => {
	const name = req.body.name;

	const nameValidation = validation.validate(name, "Name", ["required", {"min_length": 3}]);

	if (nameValidation.length > 0) {
		return res.status(400).send(helper.invalid_response(nameValidation));
	}

	const data = req.body;
	Object.assign(data, req.params);

	const result = await restaurantModel.update(data);
	if (result.success === false) {
		return res.status(500).send(helper.invalid_response("Unable to update restaurant"));
	}

	res.status(200).send({
		"success": true,
		"message": "Restaurant updated successfully"
	});
});

router.delete('/:restaurantID', async (req, res, _) => {
	res.send({
		"message": "DELETE restaurant",
	});
});

module.exports = router;
