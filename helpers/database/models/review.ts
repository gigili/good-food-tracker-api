export {};

import {User} from "./user";
import {Globals} from "../../globals";
import {ResultSet} from "../../interfaces/database"
import {UploadedFile} from "express-fileupload";

const fs = require("fs");
const db = require("../db");
const translate = require("../../translation");
const utilities = require("../../utilities");
const ROLES = require("../../roles");

export interface Review {
	id: number,
	guid: string,
	restaurantID: number,
	userID: number,
	dish_name: string,
	price: number,
	comment: string,
	wait_time: string,
	type: string,
	private: string,
	created_at: string,
	images?: string[],
}

const review = {
	async list(userGuid: string, startLimit: number = 0, endLimit: number = Number(process.env.PER_PAGE)): Promise<ResultSet<{ success: boolean, data: Review[], total: number }>> {
		const user: User = Globals.getInstance().user;
		const userID = user.id || null;

		if (!user || user.id === null) {
			return utilities.invalid_response(translate("invalid_user_provided"), {errorCode: 401});
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

	async get(reviewGuid: string, returnImages: boolean = true): Promise<ResultSet<Review>> {
		const query = `SELECT * FROM ${db.TABLES.Review} WHERE guid = ?`;
		const review = await db.getResultSet(query, [reviewGuid], false, true);

		if (returnImages) {
			const imageQuery = `SELECT * FROM ${db.TABLES.ReviewImage} WHERE reviewID = ?`;
			const images = await db.getResultSet(imageQuery, [review.data.id]);
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
	}): Promise<ResultSet<any>> {
		const user: User = Globals.getInstance().user;

		if (!user || user.id === null) {
			return utilities.invalid_response(translate("invalid_user_provided"), {errorCode: 401});
		}

		const query = `
			INSERT INTO ${db.TABLES.Review} (restaurantID, userID, dish_name, price, comment, wait_time, type, private) 
			VALUES(?, ?, ?, ?, ?, ?, ?, ?)
		`;

		const result = await db.getResultSet(query, [
			review.restaurantID,
			user.id,
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
				await db.getResultSet(insertImageQuery, [result.data.insertId, user.id, imageSavedPath]);
			}
		}

		return newReviewData;
	},

	async update(review: {
		reviewID: string,
		restaurantID: number,
		userID: string,
		dish_name: string,
		price: number,
		comment?: string,
		wait_time?: string,
		type: string,
		private: string,
		images?: UploadedFile | UploadedFile[]
	}): Promise<ResultSet<any>> {
		const user: User = Globals.getInstance().user;

		if (!user || user.id === null) {
			return utilities.invalid_response(translate("invalid_user_provided"), {errorCode: 401});
		}

		const oldReview = await this.get(review.reviewID);

		if (typeof (oldReview.data as Review).guid === "undefined") {
			return utilities.invalid_response(translate("review_not_found"), {errorCode: 404});
		}

		if (!oldReview.data.hasOwnProperty("userID") || (oldReview.data as Review).userID !== Globals.getInstance().user.id) {
			return utilities.invalid_response(translate("not_authorized"), {errorCode: 401});
		}

		const query = `
			UPDATE ${db.TABLES.Review} 
			SET
				dish_name = ?, 
				price = ?, 
				comment = ?, 
				wait_time = ?, 
				type = ?, 
				private = ?
			WHERE guid = ?
		`;

		const result = await db.getResultSet(query, [
			review.dish_name,
			review.price || 0,
			review.comment,
			review.wait_time || 0,
			review.type || '0',
			review.private || '1',
			review.reviewID
		]);

		if (!result.success) {
			return result;
		}

		if (!Array.isArray(review.images)) {
			review.images = [review.images as UploadedFile];
		}

		if (review.images && review.images.length > 0) {
			for (const image of review.images) {
				const extension = image.name.substring(image.name.lastIndexOf(".") + 1, image.name.length);
				const imagePath = `./public/images/reviews/${review.reviewID}`;

				if (!fs.existsSync(imagePath)) {
					fs.mkdirSync(imagePath, {recursive: true});
				}

				const imageName = `${image.md5}.${extension}`;
				await image.mv(`${imagePath}/${imageName}`);
				const imageSavedPath = `${imagePath}/${imageName}`.replace("./public", "");

				const checkImageQuery = `SELECT * FROM ${db.TABLES.ReviewImage} WHERE reviewID = ? AND file = ?`;
				const imageCheck = await db.getResultSet(checkImageQuery, [(oldReview.data as Review).id, imageSavedPath])

				if (imageCheck.success && imageCheck.data.length === 0) {
					const insertImageQuery = `INSERT INTO ${db.TABLES.ReviewImage} (reviewID, userID, file) VALUES (?, ?, ?);`;
					await db.getResultSet(insertImageQuery, [(oldReview.data as Review).id, user.id, imageSavedPath]);
				}
			}
		}

		return result;
	},

	async delete(reviewID: string): Promise<ResultSet<any>> {
		const review = await this.get(reviewID);
		const user = Globals.getInstance().user;

		if (!review.success) {
			return utilities.invalid_response(translate("review_not_found"), {errorCode: 404});
		}

		if (typeof (review.data as Review).guid === "undefined") {
			return utilities.invalid_response(translate("review_not_found"), {errorCode: 404});
		}

		if ((review.data as Review).userID !== user.id && user.power < ROLES.Admin) {
			return utilities.invalid_response(translate("not_authorized"), {errorCode: 401});
		}

		const reviewImagesPath = `./public/images/reviews/${reviewID}`;
		if (fs.existsSync(reviewImagesPath)) {
			fs.rmdirSync(reviewImagesPath, {recursive: true});
		}

		const query = `DELETE FROM ${db.TABLES.Review} WHERE guid = ?`;
		return db.getResultSet(query, [reviewID]);
	},

	async deleteReviewImage(reviewID: string, imageName: string): Promise<ResultSet<any>> {
		const review = await this.get(reviewID);
		const user = Globals.getInstance().user;

		if (!review.success) {
			return review;
		}

		const data = review.data as Review;

		if (data.guid === "undefined") {
			return utilities.invalid_response(translate("review_not_found"), {errorCode: 404});
		}

		if (data.userID !== user.id && user.power < ROLES.Admin) {
			return utilities.invalid_response(translate("not_authorized"), {errorCode: 401});
		}

		const imagePath = `/images/reviews/${reviewID}/${imageName}`;

		if (
			typeof data.images === "undefined" ||
			data.images.length === 0 ||
			!data.images.includes(imagePath)
		) {
			return utilities.invalid_response(translate("file_not_found"), {errorCode: 404});
		}

		const query = `DELETE FROM ${db.TABLES.ReviewImage} WHERE reviewID = ? AND file = ?`;
		const result = await db.getResultSet(query, [data.id, imageName]);

		if (result.success) {
			if (fs.existsSync(`./public/${imagePath}`)) {
				fs.unlinkSync(`./public/${imagePath}`);
			}
		}

		return result;
	}
};

module.exports = review;