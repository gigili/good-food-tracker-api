export {};

import {UploadedFile} from "express-fileupload";
import {NextFunction, Response} from "express";
import {Request} from "../helpers/interfaces/request";
import {Globals} from "../helpers/globals";

const express = require("express");
const router = express.Router();
const userModel = require("../helpers/database/models/user");
const utilities = require("../helpers/utilities");
const translate = require("../helpers/translation");
const validation = require("../helpers/validation");
const fs = require("fs");
const ROLES = require("../helpers/roles");
const uploadHelper = require("../helpers/upload");

router.get("/:userID", utilities.authenticateToken, async (req: Request, res: Response, _: NextFunction) => {
	const user = await userModel.get(req.params["userID"] || "");

	if (user.data.length === 0) {
		return res.status(404).send(utilities.invalid_response(translate("account_doesnt_exist")));
	}

	res.send({
		"success": user.success,
		"data": user.data,
		"message": ""
	});
});

router.patch("/:userID", utilities.authenticateToken, async (req: Request, res: Response, _: NextFunction) => {
	const user = Globals.getInstance().user;

	if (!req.user || (req.params["userID"] !== user.guid && user.power < ROLES.Admin)) {
		return res.status(401).send(utilities.invalid_response(translate("not_authorized")));
	}

	const {name, email} = req.body;

	const validationResult = validation.validate([
		[name, translate("name"), ["required"]],
		[email, translate("email"), ["required", "valid_email"]],
		[req.files?.image, translate("profile_image"), [{"allowed_file_type": uploadHelper.AllowedExtensions.images}]]
	]);

	if (validationResult.length > 0) {
		return res.status(400).send(utilities.invalid_response(validationResult));
	}

	const data: { name: string, email: string, userID: string } = {name, email, userID: req.params["userID"]};

	if (req.files && Object.keys(req.files).length > 0) {
		const image = req.files.image as UploadedFile;
		const extension = image.name.substring(image.name.lastIndexOf(".") + 1, image.name.length);
		const imagePath = `/images/user/${data["userID"]}.${extension}`;

		if (!fs.existsSync("./public/images/user")) {
			fs.mkdirSync("./public/images/user", {recursive: true});
		}

		const uploadResult = await image.mv(`./public/${imagePath}`).then(() => true).catch(() => false);
		if (uploadResult) {
			Object.assign(data, {image: imagePath});
		}
	}

	const result = await userModel.update(data);

	res.send({
		"success": result.success,
		"data": [],
		"message": result.success ? translate("user_profile_update_success") : translate("unable_to_update_profile")
	});
});

router.delete("/:userID", utilities.authenticateToken, async (req: Request, res: Response, _: NextFunction) => {
	if (!req.user || req.user.power < ROLES.Admin && req.params["userID"] !== req["user"]["guid"]) {
		return res.status(401).send(utilities.invalid_response(translate("not_authorized")));
	}

	const guid = req.params.userID;
	const result = await userModel.delete(guid);
	const success = result.success;
	const statusCode = success ? 200 : 500;
	const message = success ? translate("user_profile_deleted_success") : translate("unable_to_delete_profile");

	res.status(statusCode).send({
		"success": success,
		"data": [],
		"message": message
	});
});

module.exports = router;