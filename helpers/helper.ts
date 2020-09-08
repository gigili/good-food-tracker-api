import {VerifyErrors} from "jsonwebtoken";
import {NextFunction, Request, Response} from "express";

const jwt = require("jsonwebtoken");
const privateKey = process.env.JWT_SECRET;
const translate = require("./translation");

const Helper = {
	invalid_response(message = "", data = null): object {
		return {
			"success": false,
			"data": data || [],
			"message": message
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

		jwt.verify(token, process.env.JWT_SECRET, (err: VerifyErrors | null, user?: { power: number }) => {
			if (err) return res.status(401).send({"success": false, "message": translate("invalid_token")});

			if (requiredPower !== null && user) {
				if (requiredPower > user.power) {
					return res.status(401).send({
						"success": false,
						"message": translate("not_authorized")
					});
				}
			}

			req["user"] = user;
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
	format(c: string, arg: string | string []): string {
		let str = c.toString();
		if (arg.length) {
			const t = typeof arg[0];
			const args = ("string" === t || "number" === t) ? Array.prototype.slice.call(arguments) : arguments[0];

			for (let key in args) {
				if (args.hasOwnProperty(key)) {
					const replaceValue = args[key];
					str = str.replace(new RegExp("\\{" + key + "\\}", "gi"), replaceValue.toString());
				}
			}
		}

		return str;
	}
};


module.exports = Helper;