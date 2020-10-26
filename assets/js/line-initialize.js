jQuery(function($){
	function createLine( id ) {
		var data = window[id];
		var id = data.canvas_id;
		var color = data.color;
		var radius = data.radius;
		var datapts = data.datapts;
		var xaxis = data.xaxis;
		var yaxis = data.yaxis;
		// Linechart erzeugen
		var dataDef = {
			title: '', 
			xLabel: xaxis, 
			yLabel: yaxis,
			labelFont: '1.2em Arial', 
			dataPointFont: '1.2em Arial',
			dataColors : color,
			renderTypes: [CanvasChart.renderType.lines, CanvasChart.renderType.points],
			dataPoints: eval(datapts)
		};
		CanvasChart.render(id, dataDef);
	} 
	$('.tp-linebuilderWrapper').each(function(){
		var id = $(this).data('id');
		createLine(id);
	});
});
