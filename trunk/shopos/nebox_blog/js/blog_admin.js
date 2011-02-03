function checkboxes(wert){
	var my = document.leiste;
	var len = my.length;
	
	for (var i = 0; i < len; i++) {
		var e = my.elements[i];
		if (e.name == "status[]") {
			e.checked = wert;
		}
	}
}