const fs = require('fs');

const language = "english";
const translationValues = JSON.parse(fs.readFileSync(`languages/${language}.json`, 'utf8'));

module.exports = function translate(key: string = "", capitalizeFirstLetter: boolean = true): string {
	if (key.trim().length === 0) {
		return "";
	}

	if (!translationValues.hasOwnProperty(key)) {
		return `***no translation(${key},${language})***`;
	}

	return !capitalizeFirstLetter ? translationValues[key] : translationValues[key].replace(/^\w/, (c: string) => c.toUpperCase());
}
