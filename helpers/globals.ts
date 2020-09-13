import {User} from "./database/models/user";

export class Globals {
	private static instance: Globals;
	private _language: string = "english";
	private _user: User = {
		email: "",
		guid: "",
		id: 0,
		image: "",
		name: "",
		password: undefined,
		power: 0,
		username: ""
	};

	public static getInstance(): Globals {
		if (Globals.instance === undefined) {
			return Globals.instance = new Globals();
		} else {
			return Globals.instance;
		}
	}

	public get language(): string {
		return this._language;
	}

	public set language(language: string) {
		this._language = language;
	}

	public get user(): User {
		return this._user;
	}

	public set user(user: User) {
		this._user = user;
	}
}

