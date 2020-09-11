export {};

import {ResultSet} from "../../interfaces/database"
import {UploadedFile} from "express-fileupload";

const fs = require("fs");
const db = require("../db");
const userModel = require("./user");
const translate = require("../../translation");

const review = {
	async list(userGuid: string, startLimit: number = 0, endLimit: number = Number(process.env.PER_PAGE)): Promise<ResultSet> {
		const user = await userModel.get(userGuid);
		const userID = user.data.id || null;

		if (!user.success || userID === null) {
			return translate("invalid_user_provided");
		}

		const params = [
			userID,
			parseInt(startLimit.toString()),
			parseInt(endLimit.toString())
		];

		const reviews = await db.getResultSet(`
			SELECT review.* FROM ${db.TABLES.Review} AS review
			WHERE userID = ? LIMIT ?,?
		`, params);

		const count = await db.getResultSet(
			`SELECT COUNT(id) as cnt FROM ${db.TABLES.Review} WHERE userID = ?`,
			[userID],
			false,
			true
		);

		if (!reviews.success) {
			return reviews;
		}

		for (const review of reviews.data) {
			const imageQuery = `SELECT file FROM ${db.TABLES.ReviewImage} WHERE reviewID = ?`;
			const images = await db.getResultSet(imageQuery, [review.id]);
			review.images = images.data.map((image: { file: string }) => image.file);
		}

		return {
			"success": (reviews.success && reviews.success),
			"data": reviews.data,
			"total": count.data["cnt"] || 0
		};
	},

	async get(reviewGuid: string, returnImages: boolean = true): Promise<ResultSet> {
		const query = `SELECT * FROM ${db.TABLES.Review} WHERE guid = ?`;
		const review = await db.getResultSet(query, [reviewGuid]);

		if (returnImages) {
			const imageQuery = `SELECT * FROM ${db.TABLES.ReviewImage} WHERE reviewID = ?`;
			const images = db.getResultSet(imageQuery, [review.data.id]);
			review.data.images = images.data.map((image: { file: string }) => image.file);
		}

		return review;
	},

	async create(review: {
		restaurantID: number,
		userID: string,
		dish_name: string,
		price: number,
		comment?: string,
		wait_time?: string,
		type: string,
		private: string,
		images?: UploadedFile | UploadedFile[]
	}): Promise<ResultSet> {
		const user = await userModel.get(review.userID);

		if (!user.success || user.data.id === null) {
			return utilities.invalid_response(translate("invalid_user_provided"));
		}

		const query = `
			INSERT INTO ${db.TABLES.Review} (restaurantID, userID, dish_name, price, comment, wait_time, type, private) 
			VALUES(?, ?, ?, ?, ?, ?, ?, ?)
		`;

		const result = await db.getResultSet(query, [
			review.restaurantID,
			user.data.id,
			review.dish_name,
			review.price || 0,
			review.comment,
			review.wait_time || 0,
			review.type || '0',
			review.private || '1'
		]);

		if (!result.success) {
			return result;
		}

		const newReviewData = await db.getResultSet(
			`SELECT guid FROM ${db.TABLES.Review} WHERE id = ?`,
			[result.data.insertId],
			false,
			true
		);

		if (!Array.isArray(review.images)) {
			review.images = [review.images as UploadedFile];
		}

		if (review.images && review.images.length > 0) {
			for (const image of review.images) {
				const extension = image.name.substring(image.name.lastIndexOf(".") + 1, image.name.length);
				const imagePath = `./public/images/reviews/${newReviewData.data.guid}`;

				if (!fs.existsSync(imagePath)) {
					fs.mkdirSync(imagePath, {recursive: true});
				}

				await image.mv(`${imagePath}/${image.md5}.${extension}`);
				const imageSavedPath = `${imagePath}/${image.md5}.${extension}`.replace("./public", "");

				const insertImageQuery = `INSERT INTO ${db.TABLES.ReviewImage} (reviewID, userID, file) VALUES (?, ?, ?);`;
				await db.getResultSet(insertImageQuery, [result.data.insertId, user.data.id, imageSavedPath]);
			}
		}

		return newReviewData;
	}
};

module.exports = review;