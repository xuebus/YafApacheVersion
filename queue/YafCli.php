<?php
Bd_Init::init('openapi')->bootstrap();

//id::100000002
$id = $argv[1];

$daoShortUrl = new Dao_ShortUrl();
$url = $daoShortUrl->getLongUrlById($id);

var_dump($url);


