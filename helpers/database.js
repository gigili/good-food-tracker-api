const mysql = require('mysql');

const TABLES = {
	"User": "user"
};

let con = undefined;
const DB = {
	connect() {
		con = mysql.createConnection({
			host: process.env.MYSQL_HOST,
			port: process.env.MYSQL_PORT,
			user: process.env.MYSQL_USER,
			password: process.env.MYSQL_PASSWORD,
			database: process.env.MYSQL_DATABASE
		});
	},

	execute(sql) {
		if (typeof con === "undefined") {
			this.connect();
		}

		return new Promise((resolve, reject) => {
			con.query(sql, function (err, result) {
				if (err) {
					return reject(err);
				}

				return resolve(result);
			});
		});
	},
	async getResultSet(sql, isProcedure = false, returnSingleRecord = false) {
		if (isProcedure) {
			sql = `call ${sql}`;
		}

		return await this.execute(sql).then(result => {
			let res = JSON.parse(JSON.stringify(result));
			res = (returnSingleRecord === true) ? res[0] : res;
			res = (typeof res === "undefined") ? [] : res;

			return {
				"success": true,
				"rows": res,
				"total": result.length
			}
		}).catch(error => {
			return {
				"success": false,
				"rows": [],
				"total": 0,
				"error": {
					"message": error.message,
					"code": error.code,
					"stack": error.stack
				}
			}
		});
	},
	async getUserData(userID) {
		const query = `SELECT id, name, email, username, active FROM ${TABLES.User} WHERE id = ${userID}`;
		return await this.getResultSet(query, false, true)
	},
	async login(username = "", password = "") {
		const query = `SELECT id FROM ${TABLES.User} WHERE username = '${username}' AND password = '${password}'`
		return await this.getResultSet(query, false, true);
	}
}

module.exports = DB;