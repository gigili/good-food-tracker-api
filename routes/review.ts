export {};
import {NextFunction, Response} from "express";
import {Request} from "../helpers/interfaces/request";

const express = require("express");
const router = express.Router();
const reviewModel = require("../helpers/database/models/review");
const utilities = require("../helpers/utilities");
const validation = require("../helpers/validation");
const translate = require("../helpers/translation");
const uploadHelper = require("../helpers/upload");

/**
 * @swagger
 * /review:
 *   get:
 *     tags:
 *       - User Role
 *     summary: Retrieve a list of reviews
 *     description: Retrieve a list of reviews for the restaurants
 *     parameters:
 *       - name: start
 *         in: query
 *         description: Index of first review to return
 *         default: 0
 *       - name: limit
 *         in: query
 *         description: Number of reviews to return (from start index)
 *         default: 10
 *     responses:
 *       '200':
 *         description: List of reviews
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
 *                         $ref: '#/components/schemas/Review'
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
 *                       example: unable to load reviews.
*/

router.get("/", utilities.authenticate_token(), async (req: Request, res: Response, _: NextFunction) => {
	const startLimit: number = req.query.start ? parseInt(req.query.start.toString()) : 0;
	const endLimit: number = req.query.limit ? parseInt(req.query.limit.toString()) : parseInt(process.env.PER_PAGE || "10");

	const userID = req.user ? req.user.guid : "";
	const data = await reviewModel.list(userID, startLimit, endLimit);

	if (typeof data === "string") {
		return res.status(400).send(utilities.invalid_response(data));
	} else if (!data.success) {
		return res.status(500).send(utilities.invalid_response(translate("unable_to_load_review")));
	}

	res.send({
		success: data.success,
		data: data.data,
		total: data.total,
		message: data.message || ""
	});
});

/**
 * @swagger
 * /review/:reviewID:
 *   get:
 *     tags:
 *       - User Role
 *     summary: Retrieve a single review
 *     description: Retrieve a review for the restaurants
 *     parameters:
 *       - name: reviewID
 *         in: path
 *         description: Numeric ID of the review to return
 *         required: true
 *     responses:
 *       '200':
 *         description: A single review
 *         content:
 *           application/json:
 *             schema:
 *               allOf:
 *                 - $ref: '#/components/schemas/Success'
 *                 - type: object
 *                   properties:
 *                     data:
 *                       $ref: '#/components/schemas/Review'
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
 *                       example: requested review was not found.
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
 *                       example: unable to load review.
*/

router.get("/:reviewID", utilities.authenticate_token(), async (req: Request, res: Response, _: NextFunction) => {
	const reviewGuid: string = String(req.query.reviewID) || "";
	const review = await reviewModel.get(reviewGuid);

	if (typeof review === "string") {
		return res.status(400).send(utilities.invalid_response(review));
	} else if (!review.success) {
		return res.status(500).send(utilities.invalid_response(translate("unable_to_load_review")));
	} else if (review.success && !review.data.hasOwnProperty("guid")) {
		return res.status(404).send(utilities.invalid_response(translate("review_not_found")));
	}

	res.send({
		success: review.success,
		data: review.data,
		message: review.message || ""
	});
});

/**
 * @swagger
 * /review:
 *   post:
 *     tags:
 *       - User Role
 *     summary: Create a new review
 *     description: Create a review of the dish so users can see the review of the restaurant
 *     requestBody:
 *       description: Provide the name of the dish (required) in the restaurant and the image of the restaurant (optional)
 *       required: true
 *       content:
 *         application/json:
 *           schema:
 *             $ref: '#/components/schemas/NewReview'
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
 *                       example: review created successfully.
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
 *                       example: unable to create review.
*/


router.post("/", utilities.authenticate_token(), async (req: Request, res: Response, _: NextFunction) => {
	const validationResult = validation.validate([
		[req.body.dish_name, translate("dish_name"), ["required", {"min_length": 3}]],
	]);

	if (validationResult.length > 0) {
		return res.status(400).send(utilities.invalid_response(validationResult));
	}

	const review = await reviewModel.create({
		...req.body,
		images: req.files ? req.files.images : [],
		userID: req.user ? req.user.guid : ""
	});

	res.status(review.success ? 201 : review.error.code).send({
		success: review.success,
		message: review.success ? translate("review_created_success") : review.message
	});
});

/**
 * @swagger
 * /review/:reviewID:
 *   patch:
 *     tags:
 *       - User Role
 *     summary: Update a review
 *     description: Update the name of an existing dish that users see the review of that restaurant
 *     parameters:
 *       - name: reviewID
 *         in: path
 *         description: Numeric ID of the review to update
 *         required: true
 *     requestBody:
 *       description: Provide the new name of the dish (required) in the restaurant and the image of the restaurant (optional)
 *       required: true
 *       content:
 *         application/json:
 *           schema:
 *             $ref: '#/components/schemas/NewReview'
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
 *                       example: review updated successfully.
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
 *                       example: unable to update review.
*/

router.patch("/:reviewID", utilities.authenticate_token(), async (req: Request, res: Response, _: NextFunction) => {
	const validationResult = validation.validate([
		[req.body.dish_name, translate("dish_name"), ["required", {"min_length": 3}]],
		[req.files?.images, translate("uploaded_file"), [{"allowed_file_type": uploadHelper.AllowedExtensions.images}]]
	]);

	if (validationResult.length > 0) {
		return res.status(400).send(utilities.invalid_response(validationResult));
	}

	const review = await reviewModel.update({
		...req.body,
		reviewID: req.params.reviewID,
		images: req.files ? req.files.images : [],
		userID: req.user ? req.user.guid : ""
	});

	res.status(review.success ? 200 : review.error.code).send({
		success: review.success,
		message: review.success ? translate("review_updated_success") : review.message
	});
});

/**
 * @swagger
 * /review/:reviewID:
 *   delete:
 *     tags:
 *       - User Role
 *     summary: Delete a review
 *     description: Delete a review of a dish for a restaurant
 *     parameters:
 *       - name: reviewID
 *         in: path
 *         description: Numeric ID of the review to delete
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
 *                       example: review deleted successfully.
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
 *                       example: unable to delete review.
*/

router.delete("/:reviewID", utilities.authenticate_token(), async (req: Request, res: Response, _: NextFunction) => {
	const review = await reviewModel.delete(req.params.reviewID);

	res.status(review.success ? 200 : review.error.code).send({
		success: review.success,
		message: review.success ? translate("review_deleted_success") : review.message
	});
});

/**
 * @swagger
 * /review/:reviewID/:imageName:
 *   delete:
 *     tags:
 *       - User Role
 *     summary: Delete an image in the review
 *     description: Delete an image in the review
 *     parameters:
 *       - name: imageName
 *         in: path
 *         description: Name of the image in the review to delete
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
 *                       example: image deleted successfully.
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
 *                       example: unable to delete image.
*/

router.delete("/:reviewID/:imageName", utilities.authenticate_token(), async (req: Request, res: Response, _: NextFunction) => {
	const review = await reviewModel.deleteReviewImage(req.params.reviewID, req.params.imageName);

	res.status(review.success ? 200 : review.error.code).send({
		success: review.success,
		message: review.success ? translate("review_image_deleted_success") : review.message
	});
});

module.exports = router;
