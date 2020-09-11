export class Globals {
	private static instance: Globals;
	private _language: string = "english";

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
}

