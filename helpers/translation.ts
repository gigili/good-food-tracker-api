import {Globals} from "./globals";

const fs = require('fs');
const translationValues: { [key: string]: string } = {};

module.exports = function translate(key: string = "", capitalizeFirstLetter: boolean = true): string {
	const language: string = Globals.getInstance().language;

	if (!translationValues.hasOwnProperty("welcome")) {
		const values = JSON.parse(fs.readFileSync(`languages/${language}.json`, 'utf8'));
		Object.assign(translationValues, values);
	}

	if (key.trim().length === 0) {
		return "";
	}

	if (!translationValues.hasOwnProperty(key)) {
		return `***no translation(${key},${language})***`;
	}

	return !capitalizeFirstLetter ? translationValues[key] : translationValues[key].replace(/^\w/, (c: string) => c.toUpperCase());
}
