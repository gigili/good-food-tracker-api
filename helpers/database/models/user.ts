import {ResultSet} from "../../interfaces/database"

export {};

const utilities = require("../../utilities");
const translate = require("../../translation");
const db = require("../db");

export interface User {
	id: number,
	guid: string,
	name: string,
	email: string,
	username: string,
	password?: string | null,
	image: string | null,
	power: number
}

module.exports = {
	login(username: string = "", password: string = ""): Promise<ResultSet> {
		const params = [username, password];
		const query = `SELECT id, guid FROM ${db.TABLES.User} WHERE username = ? AND password = ?`;
		return db.getResultSet(query, params, false, true);
	},
	async registerUser(user: { username: string, email: string, name: string, password: string }): Promise<string | boolean> {
		const params = [
			db.TABLES.User,
			user.username,
			user.email
		];

		const userQuery = `SELECT id, name, email, username, active FROM ? WHERE username = ? OR email = ?`;
		const userData = await db.getResultSet(userQuery, params, false, true);

		if (userData.success && userData.data.hasOwnProperty("id") && userData.data.id > 0) {
			if (userData.data.username === user.username) {
				return translate("username_taken");
			}

			if (userData.data.email === user.email) {
				return translate("email_in_use");
			}

			return translate("account_already_exists");
		}

		const registerParams = [user.name, user.email, user.username, user.password, "1"];
		const insertUserQuery = `INSERT INTO ${db.TABLES.User} (name,email,username,password,active) VALUES(?, ?, ?, ?, ?)`;
		const result = await db.getResultSet(insertUserQuery, registerParams);

		if (!result.success) {
			return translate("unable_to_create_account");
		}

		return true;
	},

	getRoles(userID: string): Promise<ResultSet> {
		return db.getResultSet(`
			SELECT r.name, r.power FROM ${db.TABLES.User} AS u
			LEFT JOIN ${db.TABLES.Role} AS r ON r.id = u.roleID
			WHERE u.guid = ?  
		`, [userID], false, true);
	},

	get(userID: string): Promise<ResultSet> {
		const query = `SELECT id, guid, name, email, username, image, active FROM ${db.TABLES.User} WHERE guid = ?`;
		return db.getResultSet(query, [userID], false, true);
	},

	update(data: object | any = {}): Promise<ResultSet> {
		const params = [];
		let query = `UPDATE ${db.TABLES.User} SET `;
		for (const key in data) {
			if (data.hasOwnProperty(key)) {
				if (typeof data[key] !== "undefined" && data[key] !== "" && key !== "userID") {
					query += `${key} = ?, `;
					params.push(data[key]);
				}
			}
		}

		query = utilities.rtrim(query, ", ");
		query += ` WHERE guid = ?`;
		params.push(data.userID);

		return db.getResultSet(query, params);
	},

	delete(guid: string): Promise<ResultSet> {
		const query = `DELETE FROM ${db.TABLES.User} WHERE guid = ?`;
		return db.getResultSet(query, [guid]);
	}
};