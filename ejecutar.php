<?php

$host= $_SERVER["HTTP_HOST"];
$url= $_SERVER["REQUEST_URI"];
$path = "http://" . $host . $url;
echo $path;
echo "<br>";
echo basename($path);
echo "<br>";
echo dirname($path);
?>