const db = require("../db");
const helper = require("../../helper");
const translate = require("../../translation");

module.exports = {
	login(username = "", password = "") {
		const query = `SELECT id, guid FROM ${db.getTables().User} WHERE username = '${username}' AND password = '${password}'`;
		return db.getResultSet(query, false, true);
	},
	async registerUser(user) {
		const userQuery = `SELECT id, name, email, username, active FROM ${db.getTables().User} WHERE username = '${user.username}' OR email = '${user.email}'`;
		const userData = await db.getResultSet(userQuery, false, true);

		if (userData.success === true && userData.rows.hasOwnProperty("id") && userData.rows.id > 0) {
			if (userData.rows.username === user.username) {
				return translate("username_taken");
			}

			if (userData.rows.email === user.email) {
				return translate("email_in_use");
			}

			return translate("account_already_exists");
		}

		const insertUserQuery = `INSERT INTO ${db.getTables().User} (name,email,username,password,active) VALUES('${user.name}', '${user.email}', '${user.username}', '${user.password}', '1')`;
		const result = await db.getResultSet(insertUserQuery);

		if (result.success === false) {
			return translate("unable_to_create_account");
		}

		return true;
	},

	get(userID) {
		const query = `SELECT id, guid, name, email, username, active FROM ${db.getTables().User} WHERE guid = '${userID}'`;
		return db.getResultSet(query, false, true);
	},

	update(data = {}) {
		let query = `UPDATE ${db.getTables().User} SET `;
		for (const key in data) {
			if (typeof data[key] !== "undefined" && data[key] !== "" && key !== "userID") {
				query += `${key} = '${data[key]}', `;
			}
		}

		query = helper.rtrim(query, ", ");
		query += ` WHERE guid = '${data.userID}'`;

		return db.getResultSet(query);
	},
	delete(guid) {
		const query = `DELETE FROM ${db.getTables().User} WHERE guid = '${guid}'`;
		return db.getResultSet(query)
	}
};