export interface DbResultSet {
	success: boolean,
	rows?: any[],
	data?: any[],
	total?: number,
	message?: string,
	error?: {
		code?: number,
		stack?: string
	}
}