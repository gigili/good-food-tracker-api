export {};
const db = require("../db");
const helper = require("../../../helpers/helper");
const userModel = require("./user");
const translate = require("../../translation");

const review = {
	async list(userGuid: string, startLimit: number = 0, endLimit: number = Number(process.env.PER_PAGE)): Promise<object> {
		const user = await userModel.get(userGuid);
		const userID = user.rows.id || null;

		if (user.success === false || userID === null) {
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
			"data": reviews.rows,
			"total": count.rows["cnt"] || 0
		};
	},

	async get(reviewGuid: string, returnImages: boolean = false) {
		const query = `SELECT * FROM ${db.TABLES.Review} WHERE guid = ?`;
		const review = await db.getResultSet(query, [reviewGuid]);

		if (returnImages === true) {
			const imageQuery = `SELECT * FROM ${db.TABLES.ReviewImage} WHERE reviewID = ?`;
			const images = db.getResultSet(imageQuery, [review.rows.id]);
			review.rows.images = images.rows;
		}

		return review;
	}
};

module.exports = review;