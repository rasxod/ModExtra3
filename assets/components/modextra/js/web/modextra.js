/**
 * 
 * @example send()
 * modextra.send(send_data, 'add_company')
 * 
 * 
*/
console.log('-modextra.js-')

function debounce(f, t) {
	return function (args) {
		let previousCall = this.lastCall;
		this.lastCall = Date.now();
		if (previousCall && ((this.lastCall-previousCall) <= t)) {
			clearTimeout(this.lastCallTimer);
		}
		this.lastCallTimer = setTimeout(() => f(args), t);
	}
}
var modextra = {
	config: {
		act_url: '/assets/components/modextra/connector_web.php',
	},
	send: function(_data, _token) {
		if (!_data.action) {
			console.log('err modextra ==> No action');
			return false;
		}
		_data.token = _token;
		$.ajax({
			url:  modextra.config.act_url,
			type: 'POST',
			data: _data,
			cache: false,
			dataType: 'json',
			success: function(response){
				console.log(response)
				$(document).trigger('modextra_compl', [response, _token]);
				/**
				 * @example
				 $(document).on('modextra_compl', function(event, _data, _token){
				 	console.log(_data) 
				 	console.log(_token) 
				 })
				 * 
				*/
			},
			error: function( jqXHR, textStatus, errorThrown ){
				console.log('ОШИБКИ AJAX запроса: ' + textStatus );
			}
		});
	},
}