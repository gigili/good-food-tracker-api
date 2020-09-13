import * as core from "express-serve-static-core";
import {User} from "../database/models/user";

export interface Request extends core.Request {
	user?: User;
	lang?: string
}