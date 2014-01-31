<?php

function generate_names() {
	$t = file_get_contents("names.txt");
	$t = explode("\n", trim($t));
	foreach( $t as $k => $v ) {
		$t[$k] = trim($v);
	}
	$t = array_reverse(array_unique($t));
	return( $t );
}

function get_olympic_data($file) {
	$ret = array(
		"header" => array(),
		"names" => array(
			"Chamonix 1924",
			"St. Moritz 1928",
			"Lake Placid 1932",
			"Garmisch-Partenkirchen 1936",
			"St. Moritz 1948",
			"Oslo 1952",
			"Cortina d'Ampezzo 1956",
			"Squaw Valley 1960",
			"Innsbruck 1964",
			"Grenoble 1968",
			"Sapporo 1972",
			"Innsbruck 1976",
			"Lake Placid 1980",
			"Sarajevo 1984",
			"Calgary 1988",
			"Albertville 1992",
			"Lillehammer 1994",
			"Nagano 1998",
			"Salt Lake City 2002",
			"Turin 2006",
			"Vancouver 2010"
		),
		"range" => array(),
		"data" => array()
	);

	$all_values = array();
	
	$fp = fopen($file, "rt");
	$ret["header"] = fgetcsv( $fp );
	unset( $ret["header"][24] );	// TOTAL
	
	while( $r = fgetcsv( $fp ) ) {
		$p = array();
		
		$p['country'] = array_shift($r);
		$p['ioc'] = array_shift($r);
		$p['flag'] = array_shift($r);
		
		if( $p['country'] == 'TOTAL' ) continue;
		
		array_pop( $r );	# total
		#echo "<PRE>"; print_r( $r ); die;
		#array_pop( $r );
		#unset( $r[23] ); # total column
		
		foreach( $r as $k => $v ) {
			$r[$k] = intval($v);
			$all_values[] = $r[$k];
		}
		
		$p['medals'] = $r;
		
		$ret["data"][] = $p;
		unset( $p );
	}
	
	$ret['range']['min'] = min($all_values);
	$ret['range']['max'] = max($all_values);
	
	return( $ret );
}
