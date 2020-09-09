export {};
const db = require("../db");
const helper = require("../../../helpers/helper");

const review = {
	async list(startLimit: number = 0, endLimit: number = Number(process.env.PER_PAGE)): Promise<object> {
		const params = [
			parseInt(startLimit.toString()),
			parseInt(endLimit.toString())
		];

		const cities = await db.getResultSet(`
			SELECT review.*, country.name AS countryName FROM ${db.TABLES.City} AS review
			LEFT JOIN ${db.TABLES.Country} AS country ON review.countryID = country.id
			LIMIT ?,?
		`, params);
		const count = await db.getResultSet(`SELECT COUNT(id) as cnt FROM ${db.TABLES.City}`, null, false, true);

		return {
			"success": (cities.success && cities.success),
			"data": cities.rows,
			"total": count.rows["cnt"] || 0
		};
	},
};

module.exports = review;