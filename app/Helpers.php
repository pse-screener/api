<?php

/* This file is a custom made re-usable accross the application.
	You can more here.
*/

function generateRandomString($length = 30) {
	$x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    return substr(str_shuffle(str_repeat($x , ceil($length / strlen($x)) )), 1, $length);
}