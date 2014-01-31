<?php
require("data.php");
#$data = get_olympic_data("olympics.csv");
$data = get_olympic_data("Medals - Sheet1(1).csv");
// echo "<PRE>"; print_r( $data ); die;

// info		998 px wide		countries = 45, 15 high = 810 px + 14 px = 
// cell		332 x 54		alt colors: #dfe9f3 and #d2e2f2		lines: #3654eb 	tooltip: #ff007f
// border: lines: 1 pixel outside of cells. background?	#cccccc
// flags: test/flags 48 x 30

?>

<script src="jquery.min.js"></script>
<script src="raphael-min.js"></script>

<div id="d_olympics" style="width:998px; height:824px; background-color:#cccccc"></div>

<script type="text/javascript">

/*
 *  for styles, see http://raphaeljs.com/reference.html#Element.attr
 *  the "Possible parameters" subsection
*/
var dimensions = {
	width: 998,
	height: 824
}

var victories = {
	styles: {
		both: { 'stroke-width': 0 },
		odd: { fill: '#E0EAF4'/*, 'fill-opacity':0.8*/ },
		even: { fill: '#D1E2F2'/*, 'fill-opacity':0.6*/ },
		label: {
			'text-anchor': 'start',
			'font-family': 'Verdana',
			'font-size': 14.5,
			'fill': '#434343'
		},
		trendline: { 'stroke-width': 6, 'stroke': '#3654EB', 'stroke-opacity': 0.5 },
		invisible_interactive_element: {
			opacity: 0,
			fill: '#ffffff',
			'stroke-width': 0
		},
		pointer: {
			fill: '#ff007f',
			'stroke-width': 0,
			radius: 6,
			opacity:0.5
			//'fill-opacity': 0.1,
			//stroke: 'yellow'
			//opacity:0.3
			//radius: 8
		},
		tooltip: { fill: '#ff007f', 'stroke-width': 0 },
		tooltip_text: {
			'text-anchor': 'start',
			'font-family': 'Verdana',
			'font-size': 12,
			'fill': '#ffffff'
		},
		tooltip_text2: {
			'text-anchor': 'start',
			'font-family': 'Verdana',
			'font-size': 10,
			'fill': '#ffffff'
		},
		tooltip_line: { 'stroke': '#ff007f', 'stroke-width': 0.2 }
	},
	offsets: {
		flag: { x: 12.5, y: 12.5 },
		label: { x: 70.5, y: 26.5 },
		graph: {
			x: 125, y: 7,
			w: 195, h: 40
		}
	},
	
	// debug: adjust interactive elements
	draw_graph_border: false,
	draw_targets: false,
	
	missing_label: "- ??? -",
	missing_flag: "unknown.jpg",
	flag_url_prefix: "flags/",
	countries: [],
	targets: [],
	d_paper: Raphael("d_olympics", dimensions.width, dimensions.height),
	_tooltip: {},
	ready: function() {
		this._tooltip["rect"] = this.d_paper.rect( 0, 0, 210, 40 );
		this._tooltip["rect"].attr( victories.styles.tooltip ).hide();
		
		this._tooltip["text"] = this.d_paper.text( 0, 0, "" );
		this._tooltip["text"].attr( victories.styles.tooltip_text ).hide();
		
		this._tooltip["text2"] = this.d_paper.text( 0, 0, "" );
		this._tooltip["text2"].attr( victories.styles.tooltip_text2 ).hide();
		
		this._tooltip["line"] = this.d_paper.rect( 0, 0, 1, 40 );
		this._tooltip["line"].attr( victories.styles.tooltip_line ).hide();

		for( i = 0; i < victories.targets.length; i++ ) {
			victories.targets[i].toFront();
			if( victories.draw_targets == true ) {
				victories.targets[i].attr( { opacity: 0.5, 'stroke-width': 0.5 } );
			}
		}
		
	},
	tooltip: function(x, y, msg, gx, gy) {
		//this._tooltip["text"].attr( { text: msg[0] } );
		//var rect_width = parseInt(this._tooltip["text"].getBBox().width) + 20;

		var ox = 0;
		var oy = 0;
		var oy2 = 0;
		if( gx == 2 ) ox = -209;
		if( gy < 2 ) { oy = 120; oy2 = 50; }
		
		this._tooltip["text"].attr({ x: x + ox + 10, y: y - 60 + oy } ).show();
		this._tooltip["text2"].attr( { x: x + ox + 10, y: y - 40 + oy, text: msg[1] } ).show();
		this._tooltip["line"].attr( { x: x, y: y - 40 + oy2 } ).show();
		this._tooltip["text"].attr( { text: msg[0] } )
		
		//var rect_width = this._tooltip["text"].getBBox();
		//var correct_x = 0;
		
		//if( gx == 2 ) { ox = 0; correct_x = -rect_width.width - 18; }
		// console.info( rect_width );
		
		this._tooltip["rect"].attr({
			x: x + ox,// + correct_x,
			y: y - 70 + oy,
			text: msg[0]
			//width: rect_width.width
			//width: rect_width.width + 20
		}).show();
	},
	hide_tooltip: function() {
		this._tooltip["rect"].hide();
		this._tooltip["text"].hide();
		this._tooltip["text2"].hide();
		this._tooltip["line"].hide();
	},
	olympics: <?php echo json_encode($data) ?>
}

/*
 *  individual cell prototype
*/
function OlympicCountry(index, init) {
	this.index = index;
	this.data = init;
	this.even = false;
	this.odd = false;
	
	this.w = 332;
	this.h = 54;
	this.x = 0;
	this.y = 0;
	
	this.gx = 0;	// grid location (0, 1, 2)
	this.gy = 0;	// grid location (0, 1, 2, ...)
	
	this._e = {}; // raphaeljs references
	
	this.determine_position();
	this.draw();
	this.start_events();
}

/*
 *  given a creation order number, determine staggered x / y positions
*/
OlympicCountry.prototype.determine_position = function() {
	var w = this.w * this.index;
	var r = parseInt(w / 996);
	var b = r * 996;
	var x = w - b;
	
	this.gx = x / this.w;
	this.gy = r;
	
	if( this.index % 2) {
		this.even = true;
	} else {
		this.even = false;
	}
	this.odd = !this.even;
	
	this.x = (x) + 0.5 + this.gx;
	this.y = (r * this.h) + 0.5 + this.gy;
}

/*
 *  draw country rectangle (alternating colors, etc)
*/
OlympicCountry.prototype.draw_rect = function() {
	this._e["rect"] = victories.d_paper.rect( this.x, this.y, this.w, this.h );
	
	this._e["rect"].attr( victories.styles.both );
	if( this.even == true ) {
		this._e["rect"].attr( victories.styles.even );
	} else {
		this._e["rect"].attr( victories.styles.odd );
	}
}

/*
 *  draw flag
*/
OlympicCountry.prototype.draw_flag = function() {
	var flag_url = "";
	
	if( typeof this.data.flag == 'undefined' || this.data.flag == "" ) {
		var flag_url = victories.flag_url_prefix + victories.missing_flag;
	} else {
		flag_url = victories.flag_url_prefix + this.data.flag;
	}
	
	this._e["flag"] = victories.d_paper.image(
		flag_url,
		this.x + victories.offsets.flag.x,
		this.y + victories.offsets.flag.y,
		48,
		30
	);
}

/*
 *  draw country label (ioc, ex: AUS, USA)
*/
OlympicCountry.prototype.draw_label = function() {
	var text = this.data.ioc;
	
	if( typeof this.data.ioc == "undefined" || this.data.ioc == "" ) {
		text = victories.missing_label;
	}
	
	this._e["label"] = victories.d_paper.text(
		this.x + victories.offsets.label.x,
		this.y + victories.offsets.label.y,
		text
	);
	
	this._e["label"].attr( victories.styles.label );
}

/*
 *  given a data point, figure out x / y position of graph dot (path)
*/
OlympicCountry.prototype.get_dx = function(i) {
	return( victories.offsets.graph.w / this.data.medals.length );
}

/*
 *  given a data point, figure out x / y position of graph dot (path)
*/
OlympicCountry.prototype.coordinate = function(i, absolute) {

	var x = this.get_dx() * i;
	var yp = (this.data.medals[i] * victories.offsets.graph.h) / victories.olympics.range.max;
	var iyp = victories.offsets.graph.h - yp;
	
	if( absolute == true ) {
		return( [this.x + victories.offsets.graph.x + x, this.y + victories.offsets.graph.y + iyp] );
	} else {
	
	}
	return( [x, iyp] );
}

/*
 *  mouseover cursor
*/
OlympicCountry.prototype.draw_point = function() {
	this._e["point"] = victories.d_paper.circle(
		this.x + victories.offsets.graph.x,
		this.y + victories.offsets.graph.h / 2,
		victories.styles.pointer.radius
	);
	
	// if( this.index == 0 ) console.info( victories.styles.pointer.radius );
	this._e["point"].attr( victories.styles.pointer ).hide();
	// this._e["point"].attr( { opacity: 0.3 } );
}

/*
 *  debug routine, draws border around line graph
*/
OlympicCountry.prototype.draw_trendline_border = function() {
	victories.d_paper.rect(
		this.x + victories.offsets.graph.x, 
		this.y + victories.offsets.graph.y, 
		victories.offsets.graph.w, 
		victories.offsets.graph.h
	).attr( { opacity: 0.1 } );
}

/*
 *  draw squiggly graph line
*/
OlympicCountry.prototype.draw_trendline = function() {
	
	if( victories.draw_graph_border == true ) this.draw_trendline_border();
	
	var draw_instructions = [
		"M", 
		this.x + victories.offsets.graph.x,
		this.y + victories.offsets.graph.y
	];

	for( i = 0; i < this.data.medals.length; i++ ) {
		var xy = this.coordinate(i, true);
		//if( this.index == 0 ) console.info( xy );
		
		// relative coordinates, so first instruction is move
		draw_instructions.push( (i == 0 ? "M" : "L" ) );
		
		draw_instructions.push( xy[0] );
		draw_instructions.push( xy[1] );
	}
	
	//if( this.index == 0 ) console.info( draw_instructions );
	this._e["graph"] = victories.d_paper.path( draw_instructions );
	this._e["graph"].attr( victories.styles.trendline );
}

/*
 *  move cursor over a slice
*/
OlympicCountry.prototype.highlight = function(selected_i, x) {

	// xy returns [186.13636363636363, 3.2398753894080983]
	var xy = this.coordinate(selected_i, false);
	
	var tx = victories.offsets.graph.x + this.x + xy[0];
	var ty = victories.offsets.graph.y + this.y + xy[1];
	
	this._e["point"].show();
	this._e["point"].attr({
		cx: tx, cy: ty
	});
	
	victories.tooltip(
		tx, ty,
		[victories.olympics.names[selected_i], this.data.medals[selected_i] + " medals"],
		this.gx, this.gy
	);

/*
	this._e["point"].stop().animate({
		cx: victories.offsets.graph.x + this.x + xy[0],
		cy: victories.offsets.graph.y + this.y + xy[1]
	}, 300, "<>");
*/
}

/*
 *  invisible rectangles trigger user events
*/
OlympicCountry.prototype.start_events = function() {
	
	var y = this.y + victories.offsets.graph.y;
	var w = this.get_dx();
	var h = victories.offsets.graph.h;
	var that = this;
	
	for( i = 0; i < this.data.medals.length; i++ )(function(i) {
		var x = that.x + victories.offsets.graph.x + (i * w);
		var t = victories.d_paper.rect(
			//x, y, w, h
			x - (w/2), y, w, h
		).attr(
			victories.styles.invisible_interactive_element
		).mouseover(function() {
			that.highlight(i, x);
		}).mouseout(function() {
			that._e["point"].hide();
			victories.hide_tooltip();
		});
		
		victories.targets.push( t );
	})(i);
}

/*
 *  draw initial box
*/
OlympicCountry.prototype.draw = function() {
	this.draw_rect();
	this.draw_label();
	this.draw_flag();
	this.draw_trendline();
	this.draw_point();
}

/*
 *  instantiate all sports, start application
*/
$(victories.olympics.data).each( function(i, e) {
	victories.countries.push( new OlympicCountry(i, e) );
});
victories.ready();

</script>
