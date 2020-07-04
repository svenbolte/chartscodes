jQuery(function($){

	function createPie( id ) {
		var data = window[id];
		var percent = data.percent;
		var id = data.canvas_id;
		var label = data.label ;
		var color = data.color;
		var radius = data.radius;
		var circle = data.circle;
		var fontfamily = data.fontfamily;
		var fontstyle = data.fontstyle;
		var myModal = new PieBuilder({
		    percentage: percent,//data in percentage
		    dataLabelList: label,//respective label for data
		    datathresholdDistance: [10, 10, 10, 10, 10, 10, 10, 10, 10, 10],//this must be adjust according to data label length
		    colorList: color,//respective data pie color
		    radiusList: radius,//respective data radius
		    focusPie: 100,//keep the focus pie with the highest radius 
		    whiteCircle : circle,// width of white circle in pie chart 
		    fontSize: '18',//px 
		    fontVarient: fontstyle,
		    fontFamily: fontfamily,
		    canvasID: id,
		    labelColor:'#666',
		    percentageColor: '#000',
		    percentageFontFamily: fontfamily,
		    percentageFontSize:'12',
		    percentageFontVarient:'bold'
		});     
	} 

	$('.tp-piebuilderWrapper').each( function(){
		var id = $(this).data('id');
		createPie(id);
	});

});