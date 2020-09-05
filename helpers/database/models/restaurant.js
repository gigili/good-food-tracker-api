const db = require("../db");
const helper = require("../../../helpers/helper");

const restaurant = {
	async list(startLimit = 0, endLimit = process.env.PER_PAGE) {
		const params = [
			parseInt(startLimit.toString()),
			parseInt(endLimit)
		];

		const restaurants = await db.getResultSet(`SELECT * FROM ${db.getTables().Restaurant} LIMIT ?,?`, params);
		const count = await db.getResultSet(`SELECT COUNT(id) as cnt FROM ${db.getTables().Restaurant}`, null, false, true);
		
		return {
			"success": (restaurants.success && restaurants.success),
			"restaurants": restaurants.rows,
			"total": count.rows["cnt"] || 0
		};
	},

	create(data = {}) {
		const name = data.name;
		let address = data.address || null;
		let city = data.city || null;
		let phone = data.phone || null;
		const delivery = data.delivery || "0";
		const geo_lat = parseFloat(data.geo_lat) || null;
		const geo_long = parseFloat(data.geo_long) || null;

		const insertQuery = `
			INSERT INTO ${db.getTables().Restaurant} (name, address, city, phone, delivery, geo_lat, geo_long)
			VALUES(?, ?, ?, ?, ?, ?, ?);
		`;

		return db.getResultSet(insertQuery, [name, address, city, phone, delivery, geo_lat, geo_long]);
	},

	get(restaurantID = 0) {
		const query = `SELECT * FROM ${db.getTables().Restaurant} WHERE guid = ?`;
		return db.getResultSet(query, [restaurantID], false, true);
	},

	update(data = {}) {
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

		const updateQuery = `
			UPDATE ${db.getTables().Restaurant} 
			SET
				name = ?, 
				address = ?, 
				city = ?, 
				phone = ?, 
				delivery = ?, 
				geo_lat = ?, 
				geo_long = ?
			WHERE guid = ?;
		`;

		return db.getResultSet(updateQuery, [name, address, city, phone, delivery, geo_lat, geo_long], id);
	},

	delete(id = 0) {
		const deleteQuery = `DELETE FROM ${db.getTables().Restaurant} WHERE guid = ?`;
		return db.getResultSet(deleteQuery, [id]);
	}
};

module.exports = restaurant;