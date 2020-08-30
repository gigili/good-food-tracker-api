const Validation = {
	validate(value, label, params = [], singleError = false) {
		this.value = value;
		this.label = label;
		this.params = params;
		this.singleError = singleError;

		const errors = [];
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
		return (this.singleError === true) ? ((errors.length > 0) ? errors[0] : []) : errors;
	},

	required() {
		if (typeof this.value === "undefined" || this.value === null || this.value.length === 0) {
			return `${this.label} field is required.`;
		}

		return true;
	},

	min_length(length = 3) {
		if (!this.value || this.value.length < length) {
			return `Minimum length for the ${this.label} field is ${length} characters.`;
		}

		return true;
	},

	max_length(length = 3) {
		if (!this.value || this.value.length > length) {
			return `Maximum length for the ${this.label} field is ${length} characters.`;
		}

		return true;
	},

	valid_email() {
		const emailRegex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
		if (!this.value || this.value.match(emailRegex) === null) {
			return `Invalid email address.`;
		}

		return true;
	},

	is_number(){
		if(isNaN(this.value)){
			return `Value for the field ${this.label} must be a number.`;
		}
	},
}

module.exports = Validation;