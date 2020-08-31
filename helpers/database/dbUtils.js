const db = require("./db");

const TABLES = {
	"User": "user",
	"Restaurant": "restaurant",
};

const dbUtils = {
	async getUserData(userID) {
		const query = `SELECT id, name, email, username, active FROM ${TABLES.User} WHERE id = ${userID}`;
		return await db.getResultSet(query, false, true)
	},

	async login(username = "", password = "") {
		const query = `SELECT id FROM ${TABLES.User} WHERE username = '${username}' AND password = '${password}'`
		return await db.getResultSet(query, false, true);
	},

	async registerUser(user) {
		const userQuery = `SELECT id, name, email, username, active FROM ${TABLES.User} WHERE username = '${user.username}' OR email = '${user.email}'`;
		const userData = await db.getResultSet(userQuery, false, true);

		if(userData.success === true && userData.rows.hasOwnProperty("id") && userData.rows.id > 0){
			if(userData.rows.username === user.username) {
				return `Username is taken.`;
			}

			if(userData.rows.email === user.email) {
				return `Email is already in use.`;
			}

			return `Account already exists.`;
		}

		const insertUserQuery = `INSERT INTO ${TABLES.User} (name,email,username,password,active) VALUES('${user.name}', '${user.email}', '${user.username}', '${user.password}', '1')`;
		const result = db.getResultSet(insertUserQuery);

		if(result.success === false){
			return `Unable to create a new user account`;
		}

		return true;
	},

	getTables(){
		return TABLES;
	}
}

module.exports = dbUtils;