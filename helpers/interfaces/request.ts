import * as core from "express-serve-static-core";

export interface Request extends core.Request {
	user?: {
		id: number,
		guid: string,
		name: string,
		email: string,
		username: string,
		password?: string | null,
		image: string | null,
		power: number
	};
	lang?: string
}

export interface String {
	format(str: string | any[]): string;
}