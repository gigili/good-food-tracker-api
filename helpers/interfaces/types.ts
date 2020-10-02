export type TokenData = {
	access_token: string,
	refresh_token: string,
	expires: number
}

export type RefreshTokenData = {
	token: string,
	userID?: number,
	created_at?: string
}

export type DefaultDBResponse = {
	fieldCount: number,
	affectedRows: number,
	insertId: number,
	serverStatus: number,
	warningCount: number,
	message: string,
	protocol41: boolean,
	changedRows: number
}