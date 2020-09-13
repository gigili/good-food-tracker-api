import {UploadedFile} from "express-fileupload";

const translate = require("./translation");
const utilities = require("./utilities");

const Validation = {
	value: "",
	label: "",
	params: [],
	allErrors: false,
	validate(validationFields: any[], allErrors: boolean = false): string | string[] {
		const errors: string[] = [];

		for (const [value, label, params] of validationFields) {
			this.value = value;
			this.label = label;
			this.params = params;

			this.allErrors = allErrors;
			this.params.forEach((rule: any) => {
				if (typeof rule === "string") {
					const result = (this as any)[rule]();
					if (result !== true) {
						errors.push(result);
					}
				} else if (typeof rule === "object") {
					console.log(rule);
					const method = Object.keys(rule)[0] as string;
					const result = (this as any)[method](rule[method]);

					if (result !== true) {
						errors.push(result);
					}
				}
			});
		}

		//TODO: Refactor so it doesn't run all validations first if only the first error should be returned
		return !this.allErrors ? ((errors.length > 0) ? errors[0] : []) : errors;
	},

	required(): string | boolean {
		if (typeof this.value === "undefined" || this.value === null || this.value.length === 0) {
			return utilities.format(translate('validation_error_required_field'), this.label);
		}

		return true;
	},

	min_length(length = 3): string | boolean {
		if (!this.value || this.value.length < length) {
			return utilities.format(translate("validation_error_minimum_length"), [this.label, length]);
		}

		return true;
	},

	max_length(length = 3): string | boolean {
		if (!this.value || this.value.length > length) {
			return utilities.format(translate("validation_error_max_length"), [this.label, length]);
		}

		return true;
	},

	valid_email(): string | boolean {
		const emailRegex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
		if (!this.value || this.value.match(emailRegex) === null) {
			return translate("validation_error_email");
		}

		return true;
	},

	is_number(): string | boolean {
		if (isNaN(Number(this.value))) {
			return utilities.format(translate("validation_error_not_a_number"), this.label);
		}

		return true;
	},

	allowed_file_type(types: string[]) {
		if (typeof this.value === "undefined") {
			return true;
		}

		let files = (this.value as any) as UploadedFile[];

		if (!Array.isArray(files)) {
			files = [files];
		}

		for (const image of files) {
			const extension = image.name.substring(image.name.lastIndexOf(".") + 1, image.name.length);
			if (!types.includes(extension)) {
				return utilities.format(translate("validation_error_invalid_file_extension"), extension);
			}
		}

		return true;
	}
}

module.exports = Validation;