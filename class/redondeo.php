<?php
$num = floatval("20.20351599");
echo "original: " . $num  . "<br>";
echo "redondeado: " . (round($num,5,PHP_ROUND_HALF_UP) . "<br>");
?>