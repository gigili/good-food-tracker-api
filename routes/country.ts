export {};
import {NextFunction, Request, Response} from "express";

const express = require("express");
const router = express.Router();
const countryModel = require("../helpers/database/models/country");
const utilities = require("../helpers/utilities");
const validation = require("../helpers/validation");
const ROLES = require("../helpers/roles");
const translate = require("../helpers/translation");

/**
 * @swagger
 * /country:
 *   get:
 *     tags:
 *       - User Role
 *     summary: Retrieve a list of countries
 *     description: Retrieve a list of countries where users can review restaurants
 *     parameters:
 *       - name: start
 *         in: query
 *         description: Index of first country to return
 *         default: 0
 *       - name: limit
 *         in: query
 *         description: Number of countries to return (from start index)
 *         default: 10
 *     responses:
 *       '200':
 *         description: List of countries
 *         content:
 *           application/json:
 *             schema:
 *               allOf:
 *                 - $ref: '#/components/schemas/Success'
 *                 - type: object
 *                   properties:
 *                     data:
 *                       type: array
 *                       items:
 *                         $ref: '#/components/schemas/Country'
 *                     total:
 *                       type: integer
 *                       example: 1
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
 *                       example: unable to load countries.
*/

router.get("/", utilities.authenticate_token(), async (req: Request, res: Response, _: NextFunction) => {
	const startLimit: number = req.query.start ? parseInt(req.query.start.toString()) : 0;
	const endLimit: number = req.query.limit ? parseInt(req.query.limit.toString()) : parseInt(process.env.PER_PAGE || "10");

	const data = await countryModel.list(startLimit, endLimit);

	if (!data.success) {
		return res.status(500).send(utilities.invalid_response(translate("unable_to_load_countries")));
	}

	res.send({
		"success": data.success,
		"data": data.data,
		"total": data.total,
		"message": data.message || ""
	});
});

/**
 * @swagger
 * /country/:countryID:
 *   get:
 *     tags:
 *       - User Role
 *     summary: Retrieve a single country
 *     description: Retrieve a country where users can review restaurants
 *     parameters:
 *       - name: countryID
 *         in: path
 *         description: Numeric ID of the country to return
 *         required: true
 *     responses:
 *       '200':
 *         description: A single country
 *         content:
 *           application/json:
 *             schema:
 *               allOf:
 *                 - $ref: '#/components/schemas/Success'
 *                 - type: object
 *                   properties:
 *                     data:
 *                       $ref: '#/components/schemas/Country'
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
 *                       example: requested country was not found.
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
 *                       example: unable to load country.
*/

router.get("/:countryID", utilities.authenticate_token(), async (req: Request, res: Response, _: NextFunction) => {
	const countries = await countryModel.get(parseInt(req.params["countryID"]));

	if (!countries.success) {
		return res.status(500).send(utilities.invalid_response(translate("unable_to_load_country")));
	}

	if (!countries.data.hasOwnProperty("id") || countries.data.id < 1) {
		return res.status(404).send(utilities.invalid_response(translate("country_not_found")));
	}

	res.send({
		"success": countries.success,
		"data": countries.data,
		"message": ""
	});
});

/**
 * @swagger
 * /country:
 *   post:
 *     tags:
 *       - Admin Role
 *     summary: Create a new country
 *     description: Create a country so users can review its restaurants
 *     requestBody:
 *       description: Provide the name of the country to be created (required) and the ID of the country (optional)
 *       required: true
 *       content:
 *         application/json:
 *           schema:
 *             $ref: '#/components/schemas/NewCountry'
 *     responses:
 *       '201':
 *         allOf:
 *           - $ref: '#/components/responses/200'
 *           - description: Created
 *           - content:
 *               application/json:
 *                 schema:
 *                   type: object
 *                   properties:
 *                     message:
 *                       type: string
 *                       example: country created successfully.
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
 *                       example: unable to create country.
*/

router.post("/", utilities.authenticate_token(ROLES.Admin),
	async (req: Request, res: Response, _: NextFunction) => {
		const name = req.body.name;

		const nameValidation = validation.validate([[name, translate("name"), ["required", {"min_length": 3}]]]);

		if (nameValidation.length > 0) {
			return res.status(400).send(utilities.invalid_response(nameValidation));
		}

		const result = await countryModel.create(req.body);
		if (!result.success) {
			return res.status(500).send(utilities.invalid_response(translate("unable_to_create_country")));
		}

		res.status(201).send({
			"success": true,
			"message": translate("country_created_success")
		});
	});

/**
 * @swagger
 * /city/:countryID:
 *   patch:
 *     tags:
 *       - Admin Role
 *     summary: Update a country
 *     description: Update the name of an existing country where users can review restaurants
 *     parameters:
 *       - name: countryID
 *         in: path
 *         description: Numeric ID of the country to update
 *         required: true
 *     requestBody:
 *       description: Provide the new name of the country (required) and the ID of the country (optional)
 *       required: true
 *       content:
 *         application/json:
 *           schema:
 *             $ref: '#/components/schemas/NewCountry'
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
 *                       example: city updated successfully.
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
 *                       example: unable to update country.
*/

router.patch("/:countryID", utilities.authenticate_token(ROLES.Admin),
	async (req: Request, res: Response, _: NextFunction) => {
		const {name, code} = req.body;
		const nameValidation = validation.validate([[name, translate("name"), ["required", {"min_length": 3}]]]);

		if (nameValidation.length > 0) {
			return res.status(400).send(utilities.invalid_response(nameValidation));
		}

		const data = {
			name: name,
			code: code,
			countryID: parseInt(req.params["countryID"])
		}

		const result = await countryModel.update(data);
		if (!result.success) {
			return res.status(500).send(utilities.invalid_response(translate("unable_to_update_country")));
		}

		res.status(200).send({
			"success": true,
			"message": translate("country_update_success")
		});
	});

/**
 * @swagger
 * /country/:countryID:
 *   delete:
 *     tags:
 *       - Admin Role
 *     summary: Delete a country
 *     description: Delete a country where users can review restaurants
 *     parameters:
 *       - name: countryID
 *         in: path
 *         description: Numeric ID of the country to delete
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
 *                       example: country deleted successfully.
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
 *                       example: unable to delete country.
*/

router.delete("/:countryID", utilities.authenticate_token(ROLES.Admin),
	async (req: Request, res: Response, _: NextFunction) => {
		const data = await countryModel.delete(parseInt(req.params["countryID"]));

		if (!data.success) {
			return res.status(500).send(utilities.invalid_response(translate("unable_to_delete_country")));
		}

		res.send({
			"success": data.success,
			"data": [],
			"message": translate("country_delete_success")
		});
	});

module.exports = router;
