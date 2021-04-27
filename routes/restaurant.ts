export {};
import {NextFunction, Request, Response} from "express";

const express = require("express");
const router = express.Router();
const restaurantModel = require("../helpers/database/models/restaurant");
const utilities = require("../helpers/utilities");
const validation = require("../helpers/validation");
const ROLES = require("../helpers/roles");
const translate = require("../helpers/translation");

/**
 * @swagger
 * /restaurant:
 *   get:
 *     tags:
 *       - User Role
 *     summary: Retrieve a list of restaurants
 *     description: Retrieve a list of restaurants that users can review
 *     parameters:
 *       - name: start
 *         in: query
 *         description: Index of first restaurant to return
 *         default: 0
 *       - name: limit
 *         in: query
 *         description: Number of restaurants to return (from start index)
 *         default: 10
 *     responses:
 *       '200':
 *         description: List of restaurants
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
 *                         $ref: '#/components/schemas/Restaurant'
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
 *                       example: unable to restaurants.
*/

router.get("/", utilities.authenticate_token(), async (req: Request, res: Response, _: NextFunction) => {
	const startLimit: number = req.query.start ? parseInt(req.query.start.toString()) : 0;
	const endLimit: number = req.query.limit ? parseInt(req.query.limit.toString()) : parseInt(process.env.PER_PAGE || "10");
	const cityID: number | null = req.query.cityID ? parseInt(req.query.cityID.toString()) : null;

	const restaurants = await restaurantModel.list(startLimit, endLimit, cityID);

	if (!restaurants.success) {
		return res.status(500).send(utilities.invalid_response(translate("unable_to_load_restaurants")));
	}

	res.send({
		"success": restaurants.success,
		"data": restaurants.data,
		"total": restaurants.total
	});
});

/**
 * @swagger
 * /restaurant/:restaurantID:
 *   get:
 *     tags:
 *       - User Role
 *     summary: Retrieve a single restaurant
 *     description: Retrieve a restaurant that users can review
 *     parameters:
 *       - name: cityID
 *         in: path
 *         description: Numeric ID of the restaurant to return
 *         required: true
 *     responses:
 *       '200':
 *         description: A single restaurant
 *         content:
 *           application/json:
 *             schema:
 *               allOf:
 *                 - $ref: '#/components/schemas/Success'
 *                 - type: object
 *                   properties:
 *                     data:
 *                       $ref: '#/components/schemas/Restaurant'
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
 *                       example: requested restaurant was not found.
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
 *                       example: unable to load restaurant.
*/

router.get("/:restaurantID", utilities.authenticate_token(), async (req: Request, res: Response, _: NextFunction) => {
	const restaurant = await restaurantModel.get(req.params["restaurantID"] || "");

	if (!restaurant.success) {
		return res.status(500).send(utilities.invalid_response(translate("unable_to_load_restaurant")));
	}

	if (!restaurant.data.hasOwnProperty("id") || restaurant.data.id < 1) {
		return res.status(404).send(utilities.invalid_response(translate("restaurant_not_found")));
	}

	res.send({
		"success": restaurant.success,
		"data": restaurant.data,
		"message": ""
	});
});

/**
 * @swagger
 * /restaurant:
 *   post:
 *     tags:
 *       - User Role
 *     summary: Create a new restaurant
 *     description: Create a restaurant so users can review
 *     requestBody:
 *       description: Provide the name of the restaurant to be created (required)
 *       required: true
 *       content:
 *         application/json:
 *           schema:
 *             $ref: '#/components/schemas/NewRestaurant'
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
 *                       example: restaurant created successfully.
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
 *                       example: unable to create restaurant.
*/

router.post("/", utilities.authenticate_token(), async (req: Request, res: Response, _: NextFunction) => {
	const name = req.body.name;

	const nameValidation = validation.validate([[name, translate("name"), ["required", {"min_length": 3}]]]);

	if (nameValidation.length > 0) {
		return res.status(400).send(utilities.invalid_response(nameValidation));
	}

	const result = await restaurantModel.create(req.body);
	if (!result.success) {
		return res.status(500).send(utilities.invalid_response(translate("unable_to_create_restaurant")));
	}

	res.status(201).send({
		"success": true,
		"message": translate("restaurant_created_success")
	});
});

/**
 * @swagger
 * /restaurant/:restaurantID:
 *   patch:
 *     tags:
 *       - User Role
 *     summary: Update a restaurant
 *     description: Update the name of an existing restaurant that users can review
 *     parameters:
 *       - name: restaurantID
 *         in: path
 *         description: Numeric ID of the restaurant to update
 *         required: true
 *     requestBody:
 *       description: Provide the new name of the restaurant (required) and the ID of the restaurant (optional)
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
 *                       example: restaurant updated successfully.
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
 *                       example: unable to update restaurant.
*/

router.patch("/:restaurantID", utilities.authenticate_token(), async (req: Request, res: Response, _: NextFunction) => {
	const name = req.body.name;

	const nameValidation = validation.validate([[name, translate("name"), ["required", {"min_length": 3}]]]);

	if (nameValidation.length > 0) {
		return res.status(400).send(utilities.invalid_response(nameValidation));
	}

	const data = req.body;
	Object.assign(data, req.params);

	const result = await restaurantModel.update(data);
	if (!result.success) {
		return res.status(500).send(utilities.invalid_response(translate("unable_to_update_restaurant")));
	}

	res.status(200).send({
		"success": true,
		"message": translate("restaurant_update_success")
	});
});

/**
 * @swagger
 * /restaurant/:restaurantID:
 *   delete:
 *     tags:
 *       - Admin Role
 *     summary: Delete a restaurant
 *     description: Delete a restaurant that users can review
 *     parameters:
 *       - name: cityID
 *         in: path
 *         description: Numeric ID of the restaurant to delete
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
 *                       example: restaurant deleted successfully.
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
 *                       example: unable to delete restaurant.
*/

router.delete("/:restaurantID", utilities.authenticate_token(ROLES.Admin),
	async (req: Request, res: Response, _: NextFunction) => {
		const data = await restaurantModel.delete(req.params["restaurantID"] || "");

		if (!data.success) {
			return res.status(500).send(utilities.invalid_response(translate("unable_to_delete_restaurant")));
		}

		res.send({
			"success": data.success,
			"data": [],
			"message": translate("restaurant_delete_success")
		});
	});

module.exports = router;
