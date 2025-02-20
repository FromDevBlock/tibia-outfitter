<?php
include('Outfit.class.php');
header('Content-type: image/png');

$outfit = new Outfit(array(
    'query' => true,
    'hexmount' => true,
    'queries' => array(
        'looktype' => 'id',
        'addons' => 'addons',
        'head' => 'head',
        'body' => 'body',
        'legs' => 'legs',
        'feet' => 'feet',
        'mount' => 'mount',
        'direction' => 'direction',
        'movement' => 'movement'
    )
));

$outfit->render();
?>
