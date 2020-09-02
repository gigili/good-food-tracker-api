const translate = require("./translation");

const Validation = {
	validate(validationFields = [], singleError = false) {
		const errors = [];

		for (const [value, label, params] of validationFields) {
			this.value = value;
			this.label = label;
			this.params = params;

			this.singleError = singleError;
			this.params.forEach((rule) => {
				if (typeof rule === "string") {
					const result = this[rule]();
					if (result !== true) {
						errors.push(result);
					}
				} else if (typeof rule === "object") {
					const method = Object.keys(rule)[0];
					const result = this[method](rule[method]);

					if (result !== true) {
						errors.push(result);
					}
				}
			});
		}

		//TODO: Refactor so it doesn't run all validations first if only the first error should be returned
		return (this.singleError === true) ? ((errors.length > 0) ? errors[0] : []) : errors;
	},

	required() {
		if (typeof this.value === "undefined" || this.value === null || this.value.length === 0) {
			return translate('validation_error_required_field').format(this.label);
		}

		return true;
	},

	min_length(length = 3) {
		if (!this.value || this.value.length < length) {
			return translate("validation_error_minimum_length").format([this.label, length]);
		}

		return true;
	},

	max_length(length = 3) {
		if (!this.value || this.value.length > length) {
			return translate("validation_error_max_length").format([this.label, length]);
		}

		return true;
	},

	valid_email() {
		const emailRegex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
		if (!this.value || this.value.match(emailRegex) === null) {
			return translate("validation_error_email");
		}

		return true;
	},

	is_number() {
		if (isNaN(this.value)) {
			return translate("validation_error_not_a_number").format(this.label);
		}

		return true;
	},
}

module.exports = Validation;