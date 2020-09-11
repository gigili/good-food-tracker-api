import {DbResultSet} from "../../interfaces/database"

export {};
const db = require("../db");
const utilities = require("../../utilities");
const translate = require("../../translation");

const country = {
	async list(startLimit: number = 0, endLimit: number = Number(process.env.PER_PAGE)): Promise<DbResultSet> {
		const params = [
			parseInt(startLimit.toString()),
			parseInt(endLimit.toString())
		];

		const cities = await db.getResultSet(`SELECT * FROM ${db.TABLES.Country} LIMIT ?,?`, params);
		const count = await db.getResultSet(`SELECT COUNT(id) as cnt FROM ${db.TABLES.Country}`, null, false, true);

		return {
			"success": (cities.success && cities.success),
			"data": cities.rows,
			"total": count.rows["cnt"] || 0
		};
	},

	get(countryID: number): Promise<DbResultSet> {
		const query = `SELECT * FROM ${db.TABLES.Country} WHERE id = ?`;
		return db.getResultSet(query, [countryID], false, true);
	},

	create(data: { countryID?: number, name?: string, code?: string } = {}): Promise<DbResultSet> {
		const {name, countryID, code} = data;
		const insertQuery = `INSERT INTO ${db.TABLES.Country} (name, code ) VALUES(?, ?);`;
		return db.getResultSet(insertQuery, [name, code, countryID]);
	},

	update(data: { countryID?: number, name?: string, code?: string } = {}): Promise<DbResultSet> {
		const {countryID, name, code} = data;

		if (!countryID) {
			return utilities.invalid_response(translate("missing_id_field"));
		}

		const updateQuery = `UPDATE ${db.TABLES.Country} SET name = ?, code = ? WHERE id = ?;`;
		return db.getResultSet(updateQuery.toString(), [name, code, countryID]);
	},

	async delete(id: number): Promise<DbResultSet> {
		const restaurant = await this.get(id);

		if (restaurant.rows && !restaurant.rows.hasOwnProperty("id")) {
			return {"success": false};
		}

		const deleteQuery = `DELETE FROM ${db.TABLES.Country} WHERE id = ?`;
		return db.getResultSet(deleteQuery, [id]);
	}
};

module.exports = country;