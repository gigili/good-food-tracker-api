import {NextFunction, Request, Response} from "express";

export {};

const express = require("express");
const router = express.Router();
const cityModel = require("../helpers/database/models/city");
const utilities = require("../helpers/utilities");
const validation = require("../helpers/validation");
const ROLES = require("../helpers/roles");
const translate = require("../helpers/translation");

/**
 * @swagger
 * /city:
 *   get:
 *     tags:
 *       - User Role
 *     summary: Retrieve a list of cities
 *     description: Retrieve a list of cities where users can review restaurants
 *     parameters:
 *       - name: start
 *         in: query
 *         description: Index of first city to return
 *         default: 0
 *       - name: limit
 *         in: query
 *         description: Number of cities to return (from start index)
 *         default: 10
 *     responses:
 *       '200':
 *         description: List of cities
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
 *                         $ref: '#/components/schemas/City'
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
 *                       example: unable to load cities.
*/

router.get("/", utilities.authenticate_token(), async (req: Request, res: Response, _: NextFunction) => {
	const startLimit: number = req.query.start ? parseInt(req.query.start.toString()) : 0;
	const endLimit: number = req.query.limit ? parseInt(req.query.limit.toString()) : parseInt(process.env.PER_PAGE || "10");

	const data = await cityModel.list(startLimit, endLimit);

	if (!data.success) {
		return res.status(500).send(utilities.invalid_response(translate("unable_to_load_cities")));
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
 * /city/:cityID:
 *   get:
 *     tags:
 *       - User Role
 *     summary: Retrieve a single city
 *     description: Retrieve a city where users can review restaurants
 *     parameters:
 *       - name: cityID
 *         in: path
 *         description: Numeric ID of the city to return
 *         required: true
 *     responses:
 *       '200':
 *         description: A single city
 *         content:
 *           application/json:
 *             schema:
 *               allOf:
 *                 - $ref: '#/components/schemas/Success'
 *                 - type: object
 *                   properties:
 *                     data:
 *                       $ref: '#/components/schemas/City'
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
 *                       example: requested city was not found.
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
 *                       example: unable to load city.
*/

router.get("/:cityID", utilities.authenticate_token(), async (req: Request, res: Response, _: NextFunction) => {
	const city = await cityModel.get(parseInt(req.params["cityID"]));

	if (!city.success) {
		return res.status(500).send(utilities.invalid_response(translate("unable_to_load_city")));
	}

	if (!city.data.hasOwnProperty("id") || city.data.id < 1) {
		return res.status(404).send(utilities.invalid_response(translate("city_not_found")));
	}

	res.send({
		"success": city.success,
		"data": city.data,
		"message": ""
	});
});

/**
 * @swagger
 * /city:
 *   post:
 *     tags:
 *       - Admin Role
 *     summary: Create a new city
 *     description: Create a city so users can review its restaurants
 *     requestBody:
 *       description: Provide the name of the city to be created (required) and the ID of the country (optional)
 *       required: true
 *       content:
 *         application/json:
 *           schema:
 *             $ref: '#/components/schemas/NewCity'
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
 *                       example: city created successfully.
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
 *                       example: unable to create city.
*/

router.post("/", utilities.authenticate_token(ROLES.Admin),
	async (req: Request, res: Response, _: NextFunction) => {
		const name = req.body.name;

		const nameValidation = validation.validate([[name, translate("name"), ["required", {"min_length": 3}]]]);

		if (nameValidation.length > 0) {
			return res.status(400).send(utilities.invalid_response(nameValidation));
		}

		const result = await cityModel.create(req.body);
		if (!result.success) {
			return res.status(500).send(utilities.invalid_response(translate("unable_to_create_city")));
		}

		res.status(201).send({
			"success": true,
			"message": translate("city_created_success")
		});
	});

/**
 * @swagger
 * /city/:cityID:
 *   patch:
 *     tags:
 *       - Admin Role
 *     summary: Update a city
 *     description: Update the name of an existing city where users can review restaurants
 *     parameters:
 *       - name: cityID
 *         in: path
 *         description: Numeric ID of the city to update
 *         required: true
 *     requestBody:
 *       description: Provide the new name of the city (required) and the ID of the country (optional)
 *       required: true
 *       content:
 *         application/json:
 *           schema:
 *             $ref: '#/components/schemas/NewCity'
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
 *                       example: unable to update city.
*/

router.patch("/:cityID", utilities.authenticate_token(ROLES.Admin),
	async (req: Request, res: Response, _: NextFunction) => {
		const name = req.body.name;
		const nameValidation = validation.validate([[name, translate("name"), ["required", {"min_length": 3}]]]);

		if (nameValidation.length > 0) {
			return res.status(400).send(utilities.invalid_response(nameValidation));
		}

		const data = {
			name: name,
			cityID: parseInt(req.params["cityID"]),
			countryID: parseInt(req.params["countyID"])
		}

		const result = await cityModel.update(data);
		if (!result.success) {
			return res.status(500).send(utilities.invalid_response(translate("unable_to_update_city")));
		}

		res.status(200).send({
			"success": true,
			"message": translate("city_update_success")
		});
	});

/**
 * @swagger
 * /city/:cityID:
 *   delete:
 *     tags:
 *       - Admin Role
 *     summary: Delete a city
 *     description: Delete a city where users can review restaurants
 *     parameters:
 *       - name: cityID
 *         in: path
 *         description: Numeric ID of the city to delete
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
 *                       example: city deleted successfully.
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
 *                       example: unable to delete city.
*/

router.delete("/:cityID", utilities.authenticate_token(ROLES.Admin),
	async (req: Request, res: Response, _: NextFunction) => {
		const data = await cityModel.delete(parseInt(req.params["cityID"]));

		if (!data.success) {
			return res.status(500).send(utilities.invalid_response(translate("unable_to_delete_city")));
		}

		res.send({
			"success": data.success,
			"data": [],
			"message": translate("city_delete_success")
		});
	});

module.exports = router;
