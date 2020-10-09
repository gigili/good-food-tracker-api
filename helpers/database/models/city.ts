import {ResultSet} from "../../interfaces/database"
import {DefaultDBResponse} from "../../interfaces/types";

export {};
const db = require("../db");
const utilities = require("../../utilities");
const translate = require("../../translation")

export interface City {
	id: number,
	name: string,
	countryID: number,
	countryName?: string
}

const city = {
	async list(startLimit: number = 0, endLimit: number = Number(process.env.PER_PAGE)): Promise<ResultSet<{ success: boolean, data: City[], total: number }>> {
		const params = [
			parseInt(startLimit.toString()),
			parseInt(endLimit.toString())
		];

		const cities = await db.getResultSet(`
			SELECT city.*, country.name AS countryName FROM ${db.TABLES.City} AS city
			LEFT JOIN ${db.TABLES.Country} AS country ON city.countryID = country.id
			LIMIT ?,?
		`, params);
		const count = await db.getResultSet(`SELECT COUNT(id) as cnt FROM ${db.TABLES.City}`, null, false, true);

		return {
			"success": (cities.success && cities.success),
			"data": cities.data,
			"total": count.data.cnt || 0
		};
	},

	get(cityID: number): Promise<ResultSet<City>> {
		const query = `
		 	SELECT city.*, country.name AS countryName FROM ${db.TABLES.City} AS city
			LEFT JOIN ${db.TABLES.Country} AS country ON city.countryID = country.id
		 	WHERE city.id = ?
		 `;
		return db.getResultSet(query, [cityID], false, true);
	},

	create(data: { name?: string, countryID?: number } = {}): Promise<ResultSet<DefaultDBResponse>> {
		const {name, countryID} = data;
		const insertQuery = `INSERT INTO ${db.TABLES.City} (name, countryID) VALUES(?, ?);`;
		return db.getResultSet(insertQuery, [name, countryID || null]);
	},

	update(data: { cityID?: number, name?: string, countryID?: number } = {}): Promise<ResultSet<DefaultDBResponse>> {
		const {cityID, name, countryID} = data;

		if (!cityID) {
			return utilities.invalid_response(translate("missing_id_field"));
		}

		const updateQuery = `UPDATE ${db.TABLES.City} SET name = ?, countryID = ? WHERE id = ?;`;
		return db.getResultSet(updateQuery.toString(), [name, countryID || null, cityID]);
	},

	async delete(id: number): Promise<ResultSet<DefaultDBResponse>> {
		const restaurant = await this.get(id);

		if (restaurant.data && !restaurant.data.hasOwnProperty("id")) {
			return utilities.invalid_response(translate("missing_id_field"));
		}

		const deleteQuery = `DELETE FROM ${db.TABLES.City} WHERE id = ?`;
		return db.getResultSet(deleteQuery, [id]);
	},

	findBy(column: string, value: string): Promise<ResultSet<City>> {
		const query = `
		 	SELECT city.*, country.name AS countryName FROM ${db.TABLES.City} AS city
			LEFT JOIN ${db.TABLES.Country} AS country ON city.countryID = country.id
		 	WHERE city.${column} = ?
		 `;
		console.log(value);
		return db.getResultSet(query, [value], false, true);
	},

	async findOrCreateCityBy(column: string, value: string, countryID: number): Promise<number | null> {
		let cityID = null;
		if (value) {
			const cityData = await this.findBy(column, value);
			if (cityData.success && cityData.data.hasOwnProperty("id")) {
				cityID = (cityData.data as City).id;
			} else {
				const result = await this.create({
					name: value,
					countryID: countryID
				}) as ResultSet<DefaultDBResponse>;
				if (result.success && result.data.hasOwnProperty("insertId")) {
					cityID = (result.data as DefaultDBResponse).insertId;
				}
			}
		}

		return cityID;
	}
};

module.exports = city;