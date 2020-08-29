const jwt = require('jsonwebtoken');
const privateKey = process.env.JWT_SECRET;

const Helper = {
	generate_token(data = {}) {
		const expiresAt = (Math.floor(Date.now() / 1000) + 7200);
		const tokenData = {
			algorithm: "HS256",
			issuer: "stella-food-tracker",
			iat: Math.floor(Date.now() / 1000),
			expiresIn: expiresAt
		}
		Object.assign(tokenData, {user: data});
		return { token: jwt.sign(data, privateKey), expires: expiresAt};
	},
	verify_token(token = "") {
		return jwt.verify(token, process.env.JWT_SECRET);
	},
	decode_token(token = "") {
		return jwt.decode(token);
	},
	invalid_response(message){
		return {
			"success": false,
			"rows": [],
			"error" : { message }
		}
	}
}


module.exports = Helper