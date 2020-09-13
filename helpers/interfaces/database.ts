export interface ResultSet {
	success: boolean,
	data: any[] | object,
	total?: number,
	message?: string,
	error?: {
		code?: number,
		stack?: string
	}
}