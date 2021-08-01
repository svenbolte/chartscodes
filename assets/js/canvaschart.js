var CanvasChart = function () {
    var ctx;
    var margin = { top: 40, left: 75, right: 0, bottom: 80 };
    var chartHeight, chartWidth, yMax, xMax, data;
    var maxYValue = 0;
    var ratio = 0;
    var renderType = { lines: 'lines', points: 'points' };

    var render = function(canvasId, dataObj) {
        data = dataObj;
        getMaxDataYValue();
        var canvas = document.getElementById(canvasId);
        chartHeight = canvas.getAttribute('height');
        chartWidth = canvas.getAttribute('width');
        xMax = chartWidth - (margin.left + margin.right);
        yMax = chartHeight - (margin.top + margin.bottom);
        ratio = yMax / maxYValue;
        ctx = canvas.getContext("2d");
        renderChart();
    };

    var renderChart = function () {
        renderBackground();
        renderText();
        renderLinesAndLabels();

        //render data based upon type of renderType(s) that client supplies
        if (data.renderTypes == undefined || data.renderTypes == null) data.renderTypes = [renderType.lines];
        for (var i = 0; i < data.renderTypes.length; i++) {
            renderData(data.renderTypes[i]);
        }
    };

    var getMaxDataYValue = function () {
        for (var i = 0; i < data.dataPoints.length; i++) {
            if (data.dataPoints[i].y > maxYValue) maxYValue = data.dataPoints[i].y;
        }
    };

    var renderBackground = function() {
        ctx.fillStyle = '#fff';
        ctx.fillRect(margin.left, margin.top, xMax - margin.left, yMax - margin.top);
        ctx.fillStyle = 'black';
    };

    var renderText = function() {
        var labelFont = (data.labelFont != null) ? data.labelFont : '10px Arial';
        ctx.font = labelFont;
        ctx.textAlign = "center";

        //Title
        var txtSize = ctx.measureText(data.title);
        ctx.fillText(data.title, (chartWidth / 2), (margin.top / 2));

        //X-axis text
        txtSize = ctx.measureText(data.xLabel);
        ctx.fillText(data.xLabel, margin.left + (xMax / 2) - (txtSize.width / 2), yMax + 17 );

        //Y-axis text
        ctx.save();
        ctx.rotate(-Math.PI / 2);
        ctx.font = labelFont;
        ctx.fillText(data.yLabel, (yMax / 2) * -1, 10 );
        ctx.restore();
    };

    var renderLinesAndLabels = function () {
        //Vertical guide lines
        var yInc = yMax / data.dataPoints.length;
		var yPos = 0;
        var yLabelInc = (maxYValue * ratio) / data.dataPoints.length;
        var xInc = getXInc();
        var xPos = margin.left;
		ctx.textAlign = "left";
        for (var i = 0; i < data.dataPoints.length; i++) {
            yPos += (i == 0) ? margin.top : yInc;
            if ( i % (Math.round(data.dataPoints.length / 10 )) == 0  && Math.round(maxYValue - ((i == 0) ? 0 : yPos / ratio)) > 0 ) {
				//Draw horizontal lines
				drawLine(margin.left, yPos, xMax +12, yPos, '#E8E8E8');
				//y axis labels
				ctx.font = (data.dataPointFont != null) ? data.dataPointFont : '14px Arial';
				var txt = Math.round(maxYValue - ((i == 0) ? 0 : yPos / ratio));
				var txtSize = ctx.measureText(txt);
				ctx.fillText(txt, margin.left - ((txtSize.width >= 14) ? txtSize.width : 10) - 7, yPos + 4); 
			}
            //Draw vertical lines
            drawLine(xPos, yMax, xPos, margin.top, '#E8E8E8');
            if ( Math.round( xMax / data.dataPoints.length ) < 10 ) { xsteps = 2; } else { xsteps = 1; }
			if ( (i % xsteps) == 0 ) {
				//x axis labels
				txt = data.dataPoints[i].x;
				txtSize = ctx.measureText(txt);
				ctx.save();
				ctx.translate(xPos, yMax + (margin.bottom / 3));
				ctx.rotate(90 * Math.PI / 180);
				ctx.translate(-xPos, -(yMax + (margin.bottom / 3)));
				ctx.fillText(txt, xPos, yMax + (margin.bottom / 3));
				ctx.restore();	
			}	
            xPos += xInc;
        }
        //Vertical line
        drawLine(margin.left, margin.top, margin.left, yMax, 'black');
        //Horizontal Line
        drawLine(margin.left, yMax, xMax+10, yMax, 'black');
    };

    var renderData = function(type) {
        var colorstring = data.dataColors;
        var xInc = getXInc();
        var prevX = 0, 
            prevY = 0;

        for (var i = 0; i < data.dataPoints.length; i++) {
            var pt = data.dataPoints[i];
            var ptY = (maxYValue - pt.y) * ratio;
            if (ptY < margin.top) ptY = margin.top;
            var ptX = (i * xInc) + margin.left;

            if (i > 0 && type == renderType.lines) {
                //Draw connecting lines
                drawLine(ptX, ptY, prevX, prevY, colorstring[i], 2);
            }

            if (type == renderType.points) {
                ctx.beginPath();
                ctx.fillStyle = colorstring[i];   // '#ff0000';
                //Render circle
                ctx.arc(ptX, ptY, 5, 0, 2 * Math.PI, false)
                ctx.fill();
                ctx.lineWidth = 1;
                ctx.strokeStyle = colorstring[i];   // '#000'
                ctx.stroke();
                ctx.closePath();

				//x axis values
				txt = data.dataPoints[i].y;
				txtSize = ctx.measureText(txt);
				ctx.fillStyle = '#000';
				ctx.fillText(txt, ptX + 10, ptY - 15,);
            }
            prevX = ptX;
            prevY = ptY;
        }
    };

    var getXInc = function() {
        return Math.round(xMax / data.dataPoints.length) - 1;
    };

    var drawLine = function(startX, startY, endX, endY, strokeStyle, lineWidth) {
        if (strokeStyle != null) ctx.strokeStyle = strokeStyle;
        if (lineWidth != null) ctx.lineWidth = lineWidth;
        ctx.beginPath();
        ctx.moveTo(startX, startY);
        ctx.lineTo(endX, endY);
        ctx.stroke();
        ctx.closePath();
    };

    return {
        renderType: renderType,
        render: render
    };
} ();
