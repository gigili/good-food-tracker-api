export {};
const db = require("../db");
const utilities = require("../../utilities");
const translate = require("../../translation");

const restaurant = {
	async list(startLimit: number = 0, endLimit: number = Number(process.env.PER_PAGE)): Promise<object> {
		const params = [
			parseInt(startLimit.toString()),
			parseInt(endLimit.toString())
		];

		const restaurants = await db.getResultSet(`
			SELECT r.*, city.name as cityName, country.name as countryName FROM ${db.TABLES.Restaurant} AS r
			LEFT JOIN ${db.TABLES.City} AS city ON r.cityID = city.id
			LEFT JOIN ${db.TABLES.Country} AS country ON city.countryID = country.id 
			LIMIT ?,?
		`, params);
		const count = await db.getResultSet(`SELECT COUNT(id) as cnt FROM ${db.TABLES.Restaurant}`, null, false, true);

		return {
			"success": (restaurants.success && restaurants.success),
			"restaurants": restaurants.rows,
			"total": count.rows["cnt"] || 0
		};
	},

	get(restaurantID: string): Promise<object> {
		const query = `
			SELECT r.*, city.name as cityName, country.name as countryName FROM ${db.TABLES.Restaurant} AS r
			LEFT JOIN ${db.TABLES.City} AS city ON r.cityID = city.id
			LEFT JOIN ${db.TABLES.Country} AS country ON city.countryID = country.id
			WHERE r.guid = ?
		`;
		return db.getResultSet(query, [restaurantID], false, true);
	},

	create(data: {
		name?: string,
		address?: string,
		cityID?: number,
		phone?: string,
		delivery?: string,
		geo_lat?: number,
		geo_long?: number
	} = {}): Promise<object> {
		const name = data["name"];
		let address = data.address || null;
		let cityID = data.cityID || null;
		let phone = data.phone || null;
		const delivery = data.delivery || "0";
		const geo_lat = data.geo_lat || null;
		const geo_long = data.geo_long || null;

		const insertQuery = `
			INSERT INTO ${db.TABLES.Restaurant} (name, address, cityID, phone, delivery, geo_lat, geo_long)
			VALUES(?, ?, ?, ?, ?, ?, ?);
		`;

		return db.getResultSet(insertQuery, [name, address, cityID, phone, delivery, geo_lat, geo_long]);
	},

	update(data: {
		restaurantID?: string,
		name?: string,
		address?: string,
		cityID?: number,
		phone?: string,
		delivery?: string,
		geo_lat?: number,
		geo_long?: number
	} = {}): Promise<object> {
		const id = data.restaurantID || null;
		const name = data.name;
		let address = data.address || null;
		let cityID = data.cityID || null;
		let phone = data.phone || null;
		const delivery = data.delivery || "0";
		const geo_lat = data.geo_lat || null;
		const geo_long = data.geo_long || null;

		if (!id) {
			return utilities.invalid_response(translate("missing_id_field"));
		}

		const updateQuery = `UPDATE ${db.TABLES.Restaurant} SET name = ?, address = ?, cityID = ?, phone = ?, delivery = ?, geo_lat = ?, geo_long = ? WHERE guid = ?;`;
		return db.getResultSet(updateQuery.toString(), [name, address, cityID, phone, delivery, geo_lat, geo_long, id]);
	},

	async delete(id: string = ""): Promise<object> {
		const restaurant = await this.get(id);

		if (!restaurant.rows.hasOwnProperty("guid")) {
			return {"success": false};
		}

		const deleteQuery = `DELETE FROM ${db.TABLES.Restaurant} WHERE guid = ?`;
		return db.getResultSet(deleteQuery, [id]);
	}
};

module.exports = restaurant;