<?php

Bd_Init::init('openapi');

$id = Base_Common::alphaId(trim($argv[1]), true);

$str = Base_Common::alphaId($id);

echo "{$argv[1]}=> {$id}::{$str}\n";


