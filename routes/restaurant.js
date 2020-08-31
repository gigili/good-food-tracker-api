const express = require('express');
const router = express.Router();
const helper = require("../helpers/helper");
const dbUtils = require("../helpers/database/dbUtils");
const validation = require("../helpers/validation");

router.get('/', function (req, res, _) {
	res.send({
		"message": "List of restaurants",
	});
});

module.exports = router;
