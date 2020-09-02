const fs = require('fs');

const language = "english";
const translationValues = JSON.parse(fs.readFileSync(`languages/${language}.json`, 'utf8'));

module.exports = function translate(key = "", capitalizeFirstLetter = true) {
	if(key.trim().length === 0){
		return "";
	}

	if(translationValues.hasOwnProperty(key) === false) {
		return `***no translation(${key},${language})***`;
	}

	return (capitalizeFirstLetter === false) ? translationValues[key] : translationValues[key].replace(/^\w/, c => c.toUpperCase());
}
