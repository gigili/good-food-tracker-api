declare namespace Express {
	interface Request {
		user?: { power: number };
		lang?: string
	}
}

declare interface String {
	format(str: string | any[]): string;
}