<?php
	
$a = [ 'one' => 'alpha', 'two' => 'beta' ];

$a += [ 'three' => 'gamma', 'one' => 'beta' ];

echo '<pre>';
var_dump($a);
echo '</pre>';
?>