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
	const data = await restaurantModel.get(req.params.restaurantID || 0);

	if(data.success === false){
		return res.status(500).send(helper.invalid_response("Unable to load restaurant"));
	}

	if(data.rows.hasOwnProperty("id") === false || data.rows.id < 1){
		return res.status(404).send(helper.invalid_response("Restaurant not found"));
	}

	res.send({
		"success": data.success,
		"data": data.rows,
		"message": ""
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
	const data = await restaurantModel.delete(req.params.restaurantID || 0);

	if(data.success === false){
		return res.status(500).send(helper.invalid_response("Unable to delete the restaurant"));
	}

	res.send({
		"success": data.success,
		"data": [],
		"message": "Restaurant deleted successfully"
	});
});

module.exports = router;
