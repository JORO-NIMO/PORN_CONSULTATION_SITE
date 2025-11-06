<?php

function createSlug($string) {
    $string = preg_replace('/[^\p{L}0-9\s-]/u', '', $string); // Remove special chars
    $string = str_replace(' ', '-', $string); // Replace spaces with -
    $string = preg_replace('/-+/', '-', $string); // Replace multiple - with single -
    return strtolower($string);
}

?>