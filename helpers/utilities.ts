import {VerifyErrors} from "jsonwebtoken";
import {NextFunction, Response} from "express";
import {Request} from "./interfaces/request";
import {ResultSet} from "./interfaces/database";
import {Globals} from "./globals";
import {User} from "./database/models/user";

const jwt = require("jsonwebtoken");
const privateKey = process.env.JWT_SECRET;
const translate = require("./translation");

const Utilities = {
	invalid_response(message = "", options?: {
		data: any[] | null,
		errorCode: number,
		stack?: string
	}): ResultSet<any> {

		if (typeof options === "undefined") {
			options = {
				data: null,
				errorCode: 500,
				stack: ""
			};
		}

		return {
			"success": false,
			"data": options.data || [],
			"message": message,
			"error": {
				"stack": options.stack || "",
				"code": options.errorCode || 500
			}
		};
	},
	generate_token(data: object = {}): object {
		const expiresAt = (Math.floor(Date.now() / 1000) + 7200);
		const tokenData = {
			algorithm: "HS256",
			issuer: "good-food-tracker",
			iat: Math.floor(Date.now() / 1000),
			expiresIn: expiresAt,
			exp: expiresAt
		};
		Object.assign(tokenData, {user: data});
		return {token: jwt.sign(data, privateKey), expires: expiresAt};
	},

	authenticateToken(req: Request, res: Response, next: NextFunction, requiredPower: number | null = null) {
		const authHeader = req.headers["authorization"];
		const token = authHeader && authHeader.split(" ")[1];

		if (token == null) {
			return res.status(401).send({"success": false, "message": translate("invalid_token")});
		}

		jwt.verify(token, process.env.JWT_SECRET, (err: VerifyErrors | null, user?: User) => {
			if (err) {
				return res.status(401).send({"success": false, "message": translate("invalid_token")});
			}

			if (requiredPower !== null && user) {
				if (requiredPower > user.power) {
					return res.status(401).send({
						"success": false,
						"message": translate("not_authorized")
					});
				}
			}

			if (typeof user !== "undefined") {
				Globals.getInstance().user = user;
				Object.assign(req, {user});
			}
			next(); // pass the execution off to whatever request the client intended
		});
	},

	rtrim(str: string, chr: string): string {
		const rgxTrim = (!chr) ? new RegExp("\\s+$") : new RegExp(chr + "+$");
		return str.replace(rgxTrim, "");
	},

	ltrim(str: string, chr: string): string {
		const rgxTrim = (!chr) ? new RegExp("^\\s+") : new RegExp("^" + chr + "+");
		return str.replace(rgxTrim, "");
	},
	format(c: string, arg: string | string[]): string {
		let str = c.toString();
		if (arg.length > 0) {
			const t = typeof arg[0];
			const args = ("string" === t || "number" === t) ? [arg] : arg;

			for (let key in args as string[]) {
				if (args.hasOwnProperty(key)) {
					const replaceValue = args[key];
					str = str.replace(new RegExp("\\{" + key + "\\}", "gi"), replaceValue.toString());
				}
			}
		}

		return str;
	}
};


module.exports = Utilities;