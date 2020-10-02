import {ResultSet} from "../../interfaces/database"

export {};

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

		let updateImage = ", image = ? ";
		if (typeof data.image === "undefined") {
			params.splice(2, 1);
			updateImage = "";
		}

		const query = `UPDATE ${db.TABLES.User} SET name = ?, email = ? ${updateImage} WHERE guid = ? `;
		return db.getResultSet(query, params);
	},

	delete(guid: string): Promise<ResultSet<any>> {
		const query = `DELETE FROM ${db.TABLES.User} WHERE guid = ?`;
		return db.getResultSet(query, [guid]);
	},

	addRefreshToken(userID: number, token: string) {
		const query = `INSERT INTO ${db.TABLES.RefreshToken} (userID, token) VALUES (?, ?)`;
		db.getResultSet(query, [userID, token]);
	},

	revokeRefreshToken(userID: number, token?: string): Promise<ResultSet<any>> {
		const params = [userID.toString()];
		let query = `UPDATE ${db.TABLES.RefreshToken} SET is_revoked = '1', revoked_at = NOW() WHERE userID = ? AND is_revoked = '0' `;

		if (token && token?.length > 0) {
			query += ` AND token = ? `;
			params.push(token);
		}

		return db.getResultSet(query, params);
	},

	getRefreshToken(token: string, userID: number): Promise<ResultSet<{ token: string, is_revoked: string }>> {
		const query = `SELECT token, is_revoked FROM ${db.TABLES.RefreshToken} WHERE token = ? AND userID = ?`;
		return db.getResultSet(query, [token, userID], false, true);
	},

	getProfileImage(userID: string): Promise<ResultSet<{ image?: string }>> {
		const query = `SELECT image FROM ${db.TABLES.User} WHERE guid = ?`;
		return db.getResultSet(query, [userID], false, true);
	}
};
