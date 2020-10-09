import {ResultSet} from "../../interfaces/database"
import {DefaultDBResponse, RefreshTokenData} from "../../interfaces/types";

export {};

const translate = require("../../translation");
const utilities = require("../../utilities");
const db = require("../db");
const cityModel = require("./city");

export interface User {
	id: number,
	guid: string,
	name: string,
	email: string,
	username: string,
	password?: string | null,
	image?: string | null,
	roleID?: number,
	cityID?: number,
	cityName?: string,
	countryID?: number,
	countryName?: string,
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
		const query = `
			SELECT 
				u.id, u.guid, u.name, u.email, u.username, r.power, u.cityID, 
				ci.name as cityName, cr.id as countryID, cr.name as countryName 
			FROM ${db.TABLES.User} AS u
			LEFT JOIN ${db.TABLES.Role} AS r ON r.id = u.roleID 
			LEFT JOIN ${db.TABLES.City} AS ci ON ci.id = u.cityID
			LEFT JOIN ${db.TABLES.Country} AS cr ON cr.id = ci.countryID
			WHERE username = ? AND password = ?
		`;
		return db.getResultSet(query, params, false, true);
	},

	async registerUser(user: {
		username: string,
		email: string,
		name: string,
		password: string,
		cityID?: number,
		cityName?: string,
		countryID: number
	}): Promise<string | boolean> {
		const params = [
			user.username,
			user.email
		];

		const cityID = cityModel.findOrCreateCityBy("name", user.cityName || "", user.countryID);

		if (!cityID) {
			return translate("invalid_city_selected");
		}

		const userQuery = `SELECT id, name, email, username, active FROM ${db.TABLES.User} WHERE username = ? OR email = ?`;
		const userData = await db.getResultSet(userQuery, params, false, true);

		if (userData.success && userData.data.hasOwnProperty("id") && userData.data.id > 0) {
			if (userData.data.email === user.email) {
				return translate("email_in_use");
			}

			if (userData.data.username === user.username) {
				return translate("username_taken");
			}

			return translate("account_already_exists");
		}

		const registerParams = [user.name, user.email, user.username, user.password, "1", cityID];
		const insertUserQuery = `INSERT INTO ${db.TABLES.User} (name,email,username,password,active, cityID) VALUES(?, ?, ?, ?, ?, ?)`;
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
		const query = `
			SELECT 
				u.id, u.guid, u.name, u.email, u.username, r.power, u.cityID, 
				ci.name as cityName, cr.id as countryID, cr.name as countryName 
			FROM ${db.TABLES.User} AS u
			LEFT JOIN ${db.TABLES.Role} AS r ON r.id = u.roleID 
			LEFT JOIN ${db.TABLES.City} AS ci ON ci.id = u.cityID
			LEFT JOIN ${db.TABLES.Country} AS cr ON cr.id = ci.countryID
			WHERE guid = ?
		`;
		return db.getResultSet(query, [userID], false, true);
	},

	async update(data: { [key: string]: string | number }): Promise<ResultSet<[]> | object> {
		let cityID = await cityModel.findOrCreateCityBy("name", data.cityName, data.countryID);
		if (!cityID) {
			return {success: false, message: translate("invalid_city_selected")};
		}

		const params = [
			data.name,
			data.email,
			data.image,
			cityID || null,
			data.userID.toString()
		];

		let updateImage = ", image = ? ";
		if (typeof data.image === "undefined") {
			params.splice(2, 1);
			updateImage = "";
		}

		const query = `UPDATE ${db.TABLES.User} SET name = ?, email = ?, cityID = IFNULL(?, cityID) ${updateImage} WHERE guid = ? `;
		return db.getResultSet(query, params);
	},

	delete(guid: string): Promise<ResultSet<DefaultDBResponse>> {
		const query = `DELETE FROM ${db.TABLES.User} WHERE guid = ?`;
		return db.getResultSet(query, [guid]);
	},

	addRefreshToken(userID: number, token: string): Promise<ResultSet<DefaultDBResponse>> {
		const query = `INSERT INTO ${db.TABLES.RefreshToken} (userID, token) VALUES (?, ?)`;
		return db.getResultSet(query, [userID, token]);
	},

	revokeRefreshToken(userID: number, token?: string): Promise<ResultSet<DefaultDBResponse>> {
		const params = [userID.toString()];
		let query = `DELETE FROM ${db.TABLES.RefreshToken} WHERE userID = ? `;

		if (token && token?.length > 0) {
			query += ` AND token = ? `;
			params.push(token);
		}

		return db.getResultSet(query, params);
	},

	getRefreshToken(userID: number): Promise<ResultSet<RefreshTokenData>> {
		const query = `SELECT token FROM ${db.TABLES.RefreshToken} WHERE userID = ?`;
		return db.getResultSet(query, [userID], false, true);
	},

	getProfileImage(userID: string): Promise<ResultSet<{ image?: string }>> {
		const query = `SELECT image FROM ${db.TABLES.User} WHERE guid = ?`;
		return db.getResultSet(query, [userID], false, true);
	}
};
