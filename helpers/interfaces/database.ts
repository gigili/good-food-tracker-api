export interface ResultSet<T> {
	success: boolean,
	data: T[] | T,
	total?: number,
	message?: string,
	error?: {
		code?: number,
		stack?: string
	}
}