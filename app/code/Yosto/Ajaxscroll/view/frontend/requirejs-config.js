var config = {
		"map":{
			"*": {
				"ias":"Yosto_Ajaxscroll/js/ajaxscroll/jquery-ias.min",
				"totop": "Yosto_Ajaxscroll/js/ajaxscroll/jquery.ui.totop.min",
				"easing": "Yosto_Ajaxscroll/js/ajaxscroll/easing",
				'lazyload': "Yosto_Ajaxscroll/js/ajaxscroll/jquery.lazyload.min"
			}
		},
		"shim": {

			"Yosto_Ajaxscroll/js/ajaxscroll/jquery.ui.totop.min": ["jquery", "easing"],
			"Yosto_Ajaxscroll/js/ajaxscroll/easing": ["jquery"],
			"Yosto_Ajaxscroll/js/ajaxscroll/jquery-ias.min": ["jquery"],
			"Yosto_Ajaxscroll/js/ajaxscroll/jquery.lazyload.min": ["jquery"]
		}
		
};