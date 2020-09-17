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
	image?: string | null,
	roleID?: number,
	power: number,
	active?: string,
	create_at?: string
}

export interface Role {
	id?: number,
	name: string,
	power: number
}

module.exports = {
	login(username: string = "", password: string = ""): Promise<ResultSet<User>> {
		const params = [username, password];
		const query = `SELECT id, guid, name, email, username FROM ${db.TABLES.User} WHERE username = ? AND password = ?`;
		return db.getResultSet(query, params, false, true);
	},

	async registerUser(user: { username: string, email: string, name: string, password: string }): Promise<string | boolean> {
		const params = [
			user.username,
			user.email
		];

		const userQuery = `SELECT id, name, email, username, active FROM ${db.TABLES.User} WHERE username = ? OR email = ?`;
		const userData = await db.getResultSet(userQuery, params, false, true);

		console.log(userData);
		if (userData.success && userData.data.hasOwnProperty("id") && userData.data.id > 0) {
			if (userData.data.email === user.email) {
				return translate("email_in_use");
			}

			if (userData.data.username === user.username) {
				return translate("username_taken");
			}

			return translate("account_already_exists");
		}

		const registerParams = [user.name, user.email, user.username, user.password, "1"];
		const insertUserQuery = `INSERT INTO ${db.TABLES.User} (name,email,username,password,active) VALUES(?, ?, ?, ?, ?)`;
		const result = await db.getResultSet(insertUserQuery, registerParams);

		if (!result.success) {
			return result.message || translate("unable_to_create_account");
		}

		return true;
	},

	getRoles(userID: string): Promise<ResultSet<Role>> {
		return db.getResultSet(`
			SELECT r.name, r.power FROM ${db.TABLES.User} AS u
			LEFT JOIN ${db.TABLES.Role} AS r ON r.id = u.roleID
			WHERE u.guid = ?  
		`, [userID], false, true);
	},

	get(userID: string): Promise<ResultSet<User>> {
		const query = `SELECT id, guid, name, email, username, image, active FROM ${db.TABLES.User} WHERE guid = ?`;
		return db.getResultSet(query, [userID], false, true);
	},

	update(data: { [key: string]: string | number }): Promise<ResultSet<any>> {
		const params = [
			data.name,
			data.email,
			data.image,
			data.userID.toString()
		];

		const query = `UPDATE ${db.TABLES.User} SET name = ?, email = ?, image = ? WHERE guid = ? `;
		return db.getResultSet(query, params);
	},

	delete(guid: string): Promise<ResultSet<any>> {
		const query = `DELETE FROM ${db.TABLES.User} WHERE guid = ?`;
		return db.getResultSet(query, [guid]);
	}
};