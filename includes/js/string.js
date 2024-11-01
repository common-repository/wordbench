String.prototype.replaceAll = function(pattern, replacement) {
	var tokens = this.split(pattern);
	
	if (tokens.length > 0) {
		var newString = tokens[0];
		
		for (var i = 1, n = tokens.length; i < n; i++) {
			newString = newString.concat(replacement, tokens[i]);
		}
		
		return newString;
	}
	
	return this;
};

String.prototype.toFirstCase = function() {
	if (this.length >= 1) {
		return this.substr(0, 1).toUpperCase()
		     + this.substr(1).toLowerCase()
	}
	
	return this;
};

String.prototype.toWordsCase = function() {
	var tokens = this.split(/[\s]+/);
	
	if (tokens.length > 0) {
		var newString = tokens[0].toFirstCase();
		
		for (i = 1, n = tokens.length; i < n; i++) {
			newString = newString.concat(' ', tokens[i].toFirstCase());
		}
		
		return newString;
	}
	
	return this;
};