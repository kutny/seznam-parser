<?php
/**
 * Skript pro získávání výsledků z nového vyhledávání Seznamu
 * Inspirováno knihovnou https://github.com/seznam/JAK/blob/master/util/frpc.js
 *
 * @licence MIT
 * @author Jiří Koutný, tvůrce SEO nástroje Collabim (www.collabim.cz)
 * @version 1.0 beta
 */
require 'bootstrap.php';

$query = 'letenky usa';
$from = 0;

$serpParser = getSerpParser('http://www.seznam.sk/ajax');
$results = $serpParser->getResults($query, $from);

// ---------------------------------------------------------------
header('Content-Type: text/html; charset=utf-8');

echo '<pre>';
var_dump($results);
echo '</pre>';
