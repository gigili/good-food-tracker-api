export {};
const db = require("../db");
const utilities = require("../../utilities");
const translate = require("../../translation")

const city = {
	async list(startLimit: number = 0, endLimit: number = Number(process.env.PER_PAGE)): Promise<object> {
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
			"data": cities.rows,
			"total": count.rows["cnt"] || 0
		};
	},

	get(cityID: number): Promise<object> {
		const query = `
		 	SELECT city.*, country.name AS countryName FROM ${db.TABLES.City} AS city
			LEFT JOIN ${db.TABLES.Country} AS country ON city.countryID = country.id
		 	WHERE city.id = ?
		 `;
		return db.getResultSet(query, [cityID], false, true);
	},

	create(data: { name?: string, countryID?: number } = {}): Promise<object> {
		const {name, countryID} = data;
		const insertQuery = `INSERT INTO ${db.TABLES.City} (name, countryID) VALUES(?, ?);`;
		return db.getResultSet(insertQuery, [name, countryID || null]);
	},

	update(data: { cityID?: number, name?: string, countryID?: number } = {}): Promise<object> {
		const {cityID, name, countryID} = data;

		if (!cityID) {
			return utilities.invalid_response(translate("missing_id_field"));
		}

		const updateQuery = `UPDATE ${db.TABLES.City} SET name = ?, countryID = ? WHERE id = ?;`;
		return db.getResultSet(updateQuery.toString(), [name, countryID || null, cityID]);
	},

	async delete(id: number): Promise<object> {
		const restaurant = await this.get(id);

		if (!restaurant.rows.hasOwnProperty("id")) {
			return {"success": false};
		}

		const deleteQuery = `DELETE FROM ${db.TABLES.City} WHERE id = ?`;
		return db.getResultSet(deleteQuery, [id]);
	}
};

module.exports = city;