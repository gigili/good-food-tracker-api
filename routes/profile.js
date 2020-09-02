const express = require("express");
const router = express.Router();
const userModel = require("../helpers/database/models/user");
const helper = require("../helpers/helper");
const translate = require("../helpers/translation");
const validation = require("../helpers/validation");

router.get("/:userID", async (req, res, _) => {
    const user = await userModel.get(req.params["userID"] || "");

    if (user.rows.length === 0) {
        return res.status(404).send(helper.invalid_response(translate("account_doesnt_exist")));
    }

    res.send({
        "success": user.success,
        "data": user.rows,
        "message": ""
    });
});

router.patch("/:userID", async (req, res, _) => {
    const {name, email} = req.body;
    const data = {name, email};

    Object.assign(data, {"userID": req.params["userID"]});

    const validationResult = validation.validate([
        [name, translate("name"), ["required", {"min_length": 5}]],
        [email, translate("email"), ["required", "valid_email"]]
    ], true);

    if (validationResult.length > 0) {
        return res.status(400).send(helper.invalid_response(validationResult));
    }

    const result = await userModel.update(data);

    res.send({
        "success": result.success,
        "data": [],
        "message": result.success === true ? translate("user_profile_update_success") : translate("unable_to_update_profile")
    });
});

module.exports = router;