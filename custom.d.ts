declare namespace Express {
	interface Request {
		user?: { power: number };
	}
}

declare interface String {
	format(str: string | any[]): string;
}