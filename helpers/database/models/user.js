const helper = require("../../helper");
const translate = require("../../translation");
const db = require("../db");

module.exports = {
	login(username = "", password = "") {
		const params = [username, password];
		const query = `SELECT id, guid FROM ${db.getTables().User} WHERE username = ? AND password = ?`;
		return db.getResultSet(query, params, false, true);
	},
	async registerUser(user) {
		const params = [
			db.getTables().User,
			user.username,
			user.email
		];

		const userQuery = `SELECT id, name, email, username, active FROM ? WHERE username = ? OR email = ?`;
		const userData = await db.getResultSet(userQuery, params, false, true);

		if (userData.success === true && userData.rows.hasOwnProperty("id") && userData.rows.id > 0) {
			if (userData.rows.username === user.username) {
				return translate("username_taken");
			}

			if (userData.rows.email === user.email) {
				return translate("email_in_use");
			}

			return translate("account_already_exists");
		}

		const registerParams = [user.name, user.email, user.username, user.password, "1"];
		const insertUserQuery = `INSERT INTO ${db.getTables().User} (name,email,username,password,active) VALUES(?, ?, ?, ?, ?)`;
		const result = await db.getResultSet(insertUserQuery, registerParams);

		if (result.success === false) {
			return translate("unable_to_create_account");
		}

		return true;
	},

	getRoles(userID) {
		return db.getResultSet(`
			SELECT r.name, r.power FROM ${db.getTables().User} AS u
			LEFT JOIN ${db.getTables().Role} AS r ON r.id = u.roleID
			WHERE u.guid = ?  
		`, [userID], false, true);
	},

	get(userID) {
		const query = `SELECT id, guid, name, email, username, image, active FROM ${db.getTables().User} WHERE guid = ?`;
		return db.getResultSet(query, [userID], false, true);
	},

	update(data = {}) {
		const params = [];
		let query = `UPDATE ${db.getTables().User} SET `;
		for (const key in data) {
			if (typeof data[key] !== "undefined" && data[key] !== "" && key !== "userID") {
				query += `${key} = ?, `;
				params.push(data[key]);
			}
		}

		query = helper.rtrim(query, ", ");
		query += ` WHERE guid = ?`;
		params.push(data.userID);

		return db.getResultSet(query, params);
	},
	delete(guid) {
		const query = `DELETE FROM ${db.getTables().User} WHERE guid = ?`;
		return db.getResultSet(query, [guid]);
	}
};