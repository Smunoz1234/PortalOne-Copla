<?php 
function GenerarColor() {
  $letters = '0123456789ABCDEF';
  $arr = str_split($letters);
  $color = '#';
  for($i=0;$i<6;$i++){
    $color.= $arr[rand(0,15)];
  }
  return $color;
}

echo GenerarColor();
?>