import {ResultSet} from "../../interfaces/database"

export {};
const db = require("../db");
const utilities = require("../../utilities");
const translate = require("../../translation");

export interface Restaurant {
	id: number,
	guid: string,
	name: string,
	cityID: number,
	cityName?: string,
	countryName?: string,
	address: string,
	phone: string,
	delivery: string,
	geo_lat: number,
	geo_long: number
}

const restaurant = {
	async list(startLimit: number = 0, endLimit: number = Number(process.env.PER_PAGE)): Promise<{ success: boolean, data: Restaurant[], total: number }> {
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
			"data": restaurants.data,
			"total": count.data.cnt || 0
		};
	},

	get(restaurantID: string): Promise<ResultSet<Restaurant>> {
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
	} = {}): Promise<ResultSet<any>> {
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
	} = {}): Promise<ResultSet<any>> {
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

	async delete(id: string = ""): Promise<ResultSet<any>> {
		const restaurant = await this.get(id);

		if (restaurant.data && !restaurant.data.hasOwnProperty("guid")) {
			return utilities.invalid_response(translate("missing_id_field"));
		}

		const deleteQuery = `DELETE FROM ${db.TABLES.Restaurant} WHERE guid = ?`;
		return db.getResultSet(deleteQuery, [id]);
	}
};

module.exports = restaurant;