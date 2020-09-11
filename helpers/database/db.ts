import {Connection} from "mysql";
import {DbResultSet} from "../interfaces/database";

const mysql = require("mysql");

let con: Connection;

const DB = {
	TABLES: {
		City: "city",
		Country: "country",
		Restaurant: "restaurant",
		Review: "review",
		ReviewImage: "review_image",
		Role: "role",
		User: "user"
	},
	connect() {
		con = mysql.createConnection({
			host: process.env.MYSQL_HOST,
			port: process.env.MYSQL_PORT,
			user: process.env.MYSQL_USER,
			password: process.env.MYSQL_PASSWORD,
			database: process.env.MYSQL_DATABASE
		});
	},

	execute(sql: string, params: any[] | null = null) {
		if (typeof con === "undefined") {
			this.connect();
		}

		return new Promise((resolve, reject) => {
			if (params !== null) {
				con.query(sql, params, (err, result) => err ? reject(err) : resolve(result));
			} else {
				con.query(sql, (err, result) => err ? reject(err) : resolve(result));
			}
		});
	},

	getResultSet(sql: string, params: any[] | null, isProcedure: boolean = false, returnSingleRecord: boolean = false): Promise<DbResultSet> {
		if (isProcedure) {
			sql = `call ${sql}`;
		}

		const exec = params !== null ? this.execute(sql, params) : this.execute(sql);
		return exec.then((result: [] | any) => {
			let res = JSON.parse(JSON.stringify(result));
			res = returnSingleRecord ? res[0] : res;
			res = (typeof res === "undefined") ? [] : res;

			return {
				"success": true,
				"rows": res,
				"total": result.length
			};
		}).catch((error: object | any) => {
			return {
				"success": false,
				"rows": [],
				"total": 0,
				"message": error.message,
				"error": {
					"code": error.code,
					"stack": error.stack
				}
			};
		});
	},
};

module.exports = DB;