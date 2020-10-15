import {VerifyErrors} from "jsonwebtoken";
import {NextFunction, Response} from "express";
import {Request} from "./interfaces/request";
import {ResultSet} from "./interfaces/database";
import {Globals} from "./globals";
import {User} from "./database/models/user";
import {TokenData} from "./interfaces/types";

const userModel = require("./database/models/user");

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

	async generate_token(data: User, generateRefreshToken: Boolean = true): Promise<TokenData> {
		let refreshToken = null;
		let currentDate = new Date();
		const expiresAt = (currentDate.setHours(currentDate.getHours() + 2));
		const tokenData = {
			algorithm: "HS256",
			issuer: "good-food-tracker",
			iat: Math.floor(Date.now() / 1000),
			user: data
		};

		if (generateRefreshToken) {
			const refreshTokenResult = await userModel.getRefreshToken(data.id);
			refreshToken = refreshTokenResult.data.token;

			if (!refreshTokenResult.success || !refreshTokenResult.data.token) {
				refreshToken = this.generate_refresh_token(tokenData, data.id);
			}
		}

		Object.assign(tokenData, {expiresIn: expiresAt});
		const accessToken = jwt.sign(tokenData, privateKey);

		return {
			access_token: accessToken,
			refresh_token: refreshToken,
			expires: expiresAt
		};
	},

	generate_refresh_token(tokenData: object, userID: number): string {
		const refreshToken = jwt.sign(tokenData, privateKey);
		userModel.addRefreshToken(userID, refreshToken).then((result: any) => console.log(result));
		return refreshToken;
	},

	authenticate_token(requiredPower: number | null = null) {
		return (req: Request, res: Response, next: NextFunction) => {
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
				} else {
					return res.status(401).send({"success": false, "message": translate("invalid_token")});
				}

				next(); // pass the execution off to whatever request the client intended
			});
		}
	},

	verify_token(token: string, isRefreshToken: Boolean = false): User | Boolean {
		if (!token || token.length < 1) {
			return false;
		}

		return jwt.verify(token, process.env.JWT_SECRET, async (err: VerifyErrors | null, tokenData: { user: User }) => {
			if (err) {
				return false;
			} else if (typeof tokenData === "undefined") {
				return false;
			}

			if (isRefreshToken) {
				const result = await userModel.getRefreshToken(tokenData.user.id);
				if (
					!result.success ||
					!result.data.hasOwnProperty("token") ||
					!result.data.token ||
					result.data.token !== token
				) {
					return false;
				}
			}

			return tokenData.user;
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