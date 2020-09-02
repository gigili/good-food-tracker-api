const db = require("./db");
const translate = require("../translation");

const TABLES = {
	User: "user",
	Restaurant: "restaurant",
};

const dbUtils = {
	getUserData(userID) {
		const query = `SELECT id, name, email, username, active FROM ${TABLES.User} WHERE id = ${userID}`;
		return db.getResultSet(query, false, true)
	},

	login(username = "", password = "") {
		const query = `SELECT id FROM ${TABLES.User} WHERE username = '${username}' AND password = '${password}'`
		return db.getResultSet(query, false, true);
	},

	async registerUser(user) {
		const userQuery = `SELECT id, name, email, username, active FROM ${TABLES.User} WHERE username = '${user.username}' OR email = '${user.email}'`;
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

		const insertUserQuery = `INSERT INTO ${TABLES.User} (name,email,username,password,active) VALUES('${user.name}', '${user.email}', '${user.username}', '${user.password}', '1')`;
		const result = await db.getResultSet(insertUserQuery);

		if (result.success === false) {
			return translate("unable_to_create_account");
		}

		return true;
	},

	getTables() {
		return TABLES;
	}
}

module.exports = dbUtils;