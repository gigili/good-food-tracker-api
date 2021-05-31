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

/**
 * @swagger
 * /profile/:userID:
 *   get:
 *     tags:
 *       - Moderator Role
 *     summary: Retrieve a single user
 *     description: Retrieve a user who can review restaurants
 *     parameters:
 *       - name: userID
 *         in: path
 *         description: Numeric ID of the user to return
 *         required: true
 *     responses:
 *       '200':
 *         description: A single user
 *         content:
 *           application/json:
 *             schema:
 *               allOf:
 *                 - $ref: '/helpers/typedefs.yml#components/schemas/Success'
 *                 - type: object
 *                   properties:
 *                     data:
 *                       $ref: '#/components/schemas/User'
 *       '401':
 *         $ref: '#/components/responses/401'
 *       '404':
 *         allOf:
 *           - $ref: '#/components/responses/404'
 *           - content:
 *               application/json:
 *                 schema:
 *                   type: object
 *                   properties:
 *                     message:
 *                       type: string
 *                       example: requested user was not found.
 *       '500':
 *         allOf:
 *           - $ref: '#/components/responses/500'
 *           - content:
 *               application/json:
 *                 schema:
 *                   type: object
 *                   properties:
 *                     message:
 *                       type: string
 *                       example: unable to load user.
*/

router.get("/:userID", utilities.authenticate_token(), async (req: Request, res: Response, _: NextFunction) => {
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

/**
 * @swagger
 * /profile/:userID:
 *   patch:
 *     tags:
 *       - Moderator Role
 *     summary: Update a user
 *     description: Update the name, email, city, and country of an existing user who can review restaurants
 *     parameters:
 *       - name: userID
 *         in: path
 *         description: Numeric ID of the user to update
 *         required: true
 *     requestBody:
 *       description: Provide the new name, email, city, and country of the user (required) and image of the user (optional)
 *       required: true
 *       content:
 *         application/json:
 *           schema:
 *             $ref: '#/components/schemas/NewUser'
 *     responses:
 *       '200':
 *         allOf:
 *           - $ref: '#/components/responses/200'
 *           - content:
 *               application/json:
 *                 schema:
 *                   type: object
 *                   properties:
 *                     message:
 *                       type: string
 *                       example: user updated successfully.
 *       '400':
 *         $ref: '#/components/responses/400'
 *       '401':
 *         $ref: '#/components/responses/401'
 *       '500':
 *         allOf:
 *           - $ref: '#/components/responses/500'
 *           - content:
 *               application/json:
 *                 schema:
 *                   type: object
 *                   properties:
 *                     message:
 *                       type: string
 *                       example: unable to update user.
*/


router.patch("/:userID", utilities.authenticate_token(), async (req: Request, res: Response, _: NextFunction) => {
	const user = Globals.getInstance().user;

	if (!req.user || (req.params["userID"] !== user.guid && user.power < ROLES.Admin)) {
		return res.status(401).send(utilities.invalid_response(translate("not_authorized")));
	}

	const {name, email, cityName, countryID} = req.body;

	const validationResult = validation.validate([
		[name, translate("name"), ["required"]],
		[email, translate("email"), ["required", "valid_email"]],
		[countryID, translate("country"), ["required"]],
		[req.files?.image, translate("profile_image"), [{"allowed_file_type": uploadHelper.AllowedExtensions.images}]]
	]);

	if (validationResult.length > 0) {
		return res.status(400).send(utilities.invalid_response(validationResult));
	}

	const data: {
		name: string,
		email: string,
		userID: string,
		cityName?: string,
		countryID: number
	} = {name, email, userID: req.params["userID"], cityName, countryID};

	if (req.files && Object.keys(req.files).length > 0) {
		const image = req.files.image as UploadedFile;
		const extension = image.name.substring(image.name.lastIndexOf(".") + 1, image.name.length);
		const imagePath = `/images/user/${data.userID}.${extension}`;

		if (!fs.existsSync("./public/images/user")) {
			fs.mkdirSync("./public/images/user", {recursive: true});
		}

		const userImage = await userModel.getProfileImage(data.userID);
		if (userImage.success && userImage.data.image) {
			if (fs.existsSync(`./public/${userImage.data.image}`)){
				fs.unlinkSync(`./public/${userImage.data.image}`);
			}
		}

		const uploadResult = await image.mv(`./public/${imagePath}`).then(() => true).catch(() => false);
		if (uploadResult) {
			Object.assign(data, {image: imagePath});
		}
	}

	const result = await userModel.update(data);
	res.status(result.success ? 200 : 400).send({
		"success": result.success,
		"data": [],
		"message": result.success ? translate("user_profile_update_success") : translate("unable_to_update_profile")
	});
});

/**
 * @swagger
 * /profile/:userID:
 *   delete:
 *     tags:
 *       - Moderator Role
 *     summary: Delete a user
 *     description: Delete a user who can review restaurants
 *     parameters:
 *       - name: userID
 *         in: path
 *         description: Numeric ID of the user to delete
 *         required: true
 *     responses:
 *       '200':
 *         allOf:
 *           - $ref: '#/components/responses/200'
 *           - content:
 *               application/json:
 *                 schema:
 *                   type: object
 *                   properties:
 *                     data:
 *                       type: array
 *                       items: []
 *                     message:
 *                       type: string
 *                       example: user deleted successfully.
 *       '401':
 *         $ref: '#/components/responses/401'
 *       '500':
 *         allOf:
 *           - $ref: '#/components/responses/500'
 *           - content:
 *               application/json:
 *                 schema:
 *                   type: object
 *                   properties:
 *                     message:
 *                       type: string
 *                       example: unable to delete user.
*/

router.delete("/:userID", utilities.authenticate_token(), async (req: Request, res: Response, _: NextFunction) => {
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
