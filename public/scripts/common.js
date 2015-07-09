$('document').ready(function() {
	init();
});

function init() {
	
}

function refresh_rates() {
	$.post('/index/refreshrates', {

	}, function(data) {
		console.log(data);
		var $table = $('#currency-rates');
		for (var i=0; i<data.length; i++) {
			var code = data[i].code;
			var rate = data[i].rate;
			var $tr = $table.find('tr#' + code);
			var td_rate = $tr.find('td').get(2);
			$(td_rate).html(rate);
		}
	});
}
