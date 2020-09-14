const uploadHelper = {
	AllowedExtensions: {
		images: ["jpg", "jpeg", "png", "bmp"],
		documents: ["doc", "docx", "xls", "xlsx", "pdf"]
	},
	UploadPaths: {
		review: {
			upload: "./public/images/reviews/",
			save: "/images/reviews/"
		},
		user: {
			upload: "./public/images/user/",
			save: "/images/user/"
		}
	}
}

module.exports = uploadHelper;