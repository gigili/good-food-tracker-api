const db = require("../db");
const dbUtils = require("../dbUtils");
const helper = require("../../../helpers/helper");

const restaurant = {
	async list(startLimit = 0, endLimit = process.env.PER_PAGE) {
		const restaurants = await db.getResultSet(`SELECT * FROM ${dbUtils.getTables().Restaurant} LIMIT ${startLimit},${endLimit}`);
		const count = await db.getResultSet(`SELECT COUNT(id) as cnt FROM ${dbUtils.getTables().Restaurant}`, false, true);

		return {
			"success": (restaurants.success && restaurants.success),
			"restaurants": restaurants.rows,
			"total": count.rows["cnt"] || 0
		}
	},

	async create(data = {}) {
		const name = data.name;
		let address = data.address || null;
		let city = data.city || null;
		let phone = data.phone || null;
		const delivery = data.delivery || "0";
		const geo_lat = parseFloat(data.geo_lat) || null;
		const geo_long = parseFloat(data.geo_long) || null;

		if (address !== null) {
			address = `'${address}'`;
		}

		if (city !== null) {
			city = `'${city}'`;
		}

		if (phone !== null) {
			phone = `'${phone}'`;
		}

		const insertQuery = `
			INSERT INTO ${dbUtils.getTables().Restaurant} (name, address, city, phone, delivery, geo_lat, geo_long)
			VALUES('${name}', ${address}, ${city}, ${phone}, '${delivery}', ${geo_lat}, ${geo_long});
		`;

		return await db.getResultSet(insertQuery);
	},

	async get(restaurantID = 0) {
		const query = `SELECT * FROM ${dbUtils.getTables().Restaurant} WHERE id = ${restaurantID}`;
		return await db.getResultSet(query, false, true);
	},

	async update(data = {}) {
		const id = data.restaurantID || null;
		const name = data.name;
		let address = data.address || null;
		let city = data.city || null;
		let phone = data.phone || null;
		const delivery = data.delivery || "0";
		const geo_lat = parseFloat(data.geo_lat) || null;
		const geo_long = parseFloat(data.geo_long) || null;

		if (id === null || id < 1) {
			return helper.invalid_response("Missing ID field");
		}

		if (address !== null) {
			address = `'${address}'`;
		}

		if (city !== null) {
			city = `'${city}'`;
		}

		if (phone !== null) {
			phone = `'${phone}'`;
		}

		const updateQuery = `
			UPDATE ${dbUtils.getTables().Restaurant} 
			SET
				name = '${name}', 
				address = ${address}, 
				city = ${city}, 
				phone = ${phone}, 
				delivery = '${delivery.toString()}', 
				geo_lat = ${geo_lat}, 
				geo_long = ${geo_long}
			WHERE id = ${id};
		`;

		return await db.getResultSet(updateQuery);
	},

	async delete(id = 0) {
		const deleteQuery = `DELETE FROM ${dbUtils.getTables().Restaurant} WHERE id = ${id}`;
		return await db.getResultSet(deleteQuery);
	}
}

module.exports = restaurant;