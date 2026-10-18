<?php
if(function_exists('imagecreatefrompng')) {
    echo "GD is enabled in Apache!";
} else {
    echo "GD is NOT enabled in Apache!";
}
?>
