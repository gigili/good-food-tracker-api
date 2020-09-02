const jwt = require("jsonwebtoken");
const privateKey = process.env.JWT_SECRET;

const Helper = {
    generate_token(data = {}) {
        const expiresAt = (Math.floor(Date.now() / 1000) + 7200);
        const tokenData = {
            algorithm: "HS256",
            issuer: "good-food-tracker",
            iat: Math.floor(Date.now() / 1000),
            expiresIn: expiresAt
        };
        Object.assign(tokenData, {user: data});
        return {token: jwt.sign(data, privateKey), expires: expiresAt};
    },
    verify_token(token = "") {
        return jwt.verify(token, process.env.JWT_SECRET);
    },
    decode_token(token = "") {
        return jwt.decode(token);
    },
    invalid_response(message = "", data = null) {
        return {
            "success": false,
            "data": data || [],
            "message": message
        };
    },
    rtrim(str, chr) {
        const rgxtrim = (!chr) ? new RegExp("\\s+$") : new RegExp(chr + "+$");
        return str.replace(rgxtrim, "");
    },
    ltrim(str, chr) {
        const rgxtrim = (!chr) ? new RegExp("^\\s+") : new RegExp("^" + chr + "+");
        return str.replace(rgxtrim, "");
    }
};


module.exports = Helper;