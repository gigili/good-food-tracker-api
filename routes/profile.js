const express = require("express");
const router = express.Router();
const userModel = require("../helpers/database/models/user");
const helper = require("../helpers/helper");
const translate = require("../helpers/translation");
const validation = require("../helpers/validation");

router.get("/:userID", helper.authenticateToken, async (req, res, _) => {
	const user = await userModel.get(req.params["userID"] || "");

	if (user.rows.length === 0) {
		return res.status(404).send(helper.invalid_response(translate("account_doesnt_exist")));
	}

	res.send({
		"success": user.success,
		"data": user.rows,
		"message": ""
	});
});

router.patch("/:userID", helper.authenticateToken, async (req, res, _) => {
	if (req.params["userID"] !== req["user"]["guid"]) {
		return res.status(401).send(helper.invalid_response(translate("not_authorized")));
	}

	const {name, email} = req.body;
	const data = {name, email};

	Object.assign(data, {"userID": req.params["userID"]});

	if (req.files && Object.keys(req.files).length > 0) {
		const image = req.files.image;
		const extension = image.name.substring(image.name.lastIndexOf(".") + 1, image.name.length);
		const imagePath = `./public/images/user/${data.userID}.${extension}`;
		const uploadResult = await image.mv(imagePath).then(() => true).catch(() => false);
		if (uploadResult === true) {
			Object.assign(data, {image: imagePath});
		}
	}

	const validationResult = validation.validate([
		[name, translate("name"), ["required", {"min_length": 5}]],
		[email, translate("email"), ["required", "valid_email"]]
	], true);

	if (validationResult.length > 0) {
		return res.status(400).send(helper.invalid_response(validationResult));
	}

	const result = await userModel.update(data);

	res.send({
		"success": result.success,
		"data": [],
		"message": result.success === true ? translate("user_profile_update_success") : translate("unable_to_update_profile")
	});
});

module.exports = router;