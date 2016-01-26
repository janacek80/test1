<?php

//var_dump( $_GET );

$parameter = $_GET['ids'];
$parameters = explode( ',', $parameter );

$data = array();

function distance($lat1, $lon1, $lat2, $lon2, $unit) {

	$theta = $lon1 - $lon2;
	$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
	$dist = acos($dist);
	$dist = rad2deg($dist);
	$miles = $dist * 60 * 1.1515;
	$unit = strtoupper($unit);

	if ($unit == "K") {
		return ($miles * 1.609344);
	} else if ($unit == "N") {
		return ($miles * 0.8684);
	} else {
		return $miles;
	}
}

//@TODO: 
$pragueLat = 50.078537;
$pragueLon = 14.445019;

foreach ( $parameters as $facebookId )
{
	$graph_url = 'https://graph.facebook.com/v2.1/'.$facebookId.'/?access_token=CAACEdEose0cBACUEDrp6ZA1Bwbw38ZBniT7LtGLilp8ZB9Q56kHm56yyYeaxvHlj8m6k6RIj2j8ZAtS5cuM7v762r9hCZAZCe1MCkXZAK12NP3Ao456GJCFspZAdvhvow8XMUeqbL517YcLd3f5j8kil6Nx3HY4iTHzx0hVKP5viV0v920MrxpgYC9hGBilNxT3W6zO72N0ELQRsMA4CDsV1';
	
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $graph_url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_POST, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

	$output = curl_exec($ch);

	curl_close($ch);

	$output = json_decode( $output );
	
	$distance = -1;
	if ( property_exists( $output, 'location' ) )
		if ( property_exists( $output->location, 'latitude' ) && property_exists( $output->location, 'longitude' ) )
			$distance = distance( $output->location->latitude, $output->location->longitude, $pragueLat, $pragueLon, 'K' );
	
	$data[] = array(
		'url' => (string) $output->name,
		'city' => ( property_exists( $output, 'location' ) ? (string) $output->location->city : '' ),
		'likes' => (int) $output->likes,
		'checkin' => (int) $output->checkins,
		'prague_distance' => $distance
	);
}

function distanceSort( $item1, $item2 )
{
    if ( $item1['prague_distance'] == $item2['prague_distance'] ) return 0;
    return ( $item1['prague_distance'] < $item2['prague_distance'] ) ? 1 : -1;
}

usort( $data,'distanceSort');

?>

<table>
	<tr>
		<th>URL</th>
		<th>City</th>
		<th>Likes</th>
		<th>Checkin</th>
		<th>Distance</th>
	</tr>
<?php foreach ( $data as $item ) { ?>
	<tr>
		<td><?php echo $item['url']; ?></td>
		<td><?php echo $item['city']; ?></td>
		<td><?php echo $item['likes']; ?></td>
		<td><?php echo $item['checkin']; ?></td>
		<td><?php echo ( $item['prague_distance'] > -1 ? round( $item['prague_distance'].' KM' ) : 'UNKNOWN' ); ?></td>
	</tr>
<?php } ?>
</table>
