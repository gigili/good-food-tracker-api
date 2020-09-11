import {ResultSet} from "../../interfaces/database"

export {};
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

		return {
			"success": (reviews.success && reviews.success),
			"data": reviews.data,
			"total": count.data["cnt"] || 0
		};
	},

	async get(reviewGuid: string, returnImages: boolean = false): Promise<ResultSet> {
		const query = `SELECT * FROM ${db.TABLES.Review} WHERE guid = ?`;
		const review = await db.getResultSet(query, [reviewGuid]);

		if (returnImages) {
			const imageQuery = `SELECT * FROM ${db.TABLES.ReviewImage} WHERE reviewID = ?`;
			const images = db.getResultSet(imageQuery, [review.data.id]);
			review.data.images = images.data;
		}

		return review;
	},

	async create(review: {
		restaurantID: number,
		userID: string,
		dish_name: string,
		price: number,
		comment?: string,
		type: string,
		private: string
	}) {
		const user = await userModel.get(review.userID);

		if (!user.success || user.data.id === null) {
			return translate("invalid_user_provided");
		}
	}
};

module.exports = review;