<?php
$im  = imagecreatetruecolor(300, 100);


$c = imagecolorallocate($im, 0, 0, 0);

imageline($im, 0, 0, 100, 100, $c);


header("Content-Type: image/png");

imagepng($im);
?>
