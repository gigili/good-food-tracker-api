const jwt = require("jsonwebtoken");
const privateKey = process.env.JWT_SECRET;
const translate = require("./translation");

const Helper = {
    invalid_response(message = "", data = null) {
        return {
            "success": false,
            "data": data || [],
            "message": message
        };
    },
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
    authenticateToken(req, res, next) {
        // Gather the jwt access token from the request header
        const authHeader = req.headers["authorization"];
        const token = authHeader && authHeader.split(" ")[1];
        if (token == null) return res.status(401).send({"success": false, "message": translate("invalid_token")});

        jwt.verify(token, process.env.JWT_SECRET, (err, user) => {
            if (err) return res.status(401).send({"success": false, "message": translate("invalid_token")});
            req.user = user;
            next(); // pass the execution off to whatever request the client intended
        });
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