<?php

use Apility\OpenGraph\OpenGraph;

$og = new OpenGraph();

// Add OpenGraph properties here
$og->addProperty('title', get_meta_title());

echo($og->toMetaTags());
