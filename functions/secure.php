<?php
/**
 * Including private and security related functions that will be used whole site
 * These function are for development only, use more secure algorithms on live site!
 */

/**
 * Hash plaintext passwords
 *
 * @param string $plain
 * @return string
 */
function cr_encrypt_password($plain){
	return md5($plain);
}

/**
 * Validate a plaintext password against hash
 *
 * @param mixed $plain
 * @param mixed $encrypted
 * @return bool
 */
function cr_validate_password($plain, $encrypted){
	if(md5($plain) == $encrypted)
		return true;
	else
		return false;
}

/**
 * Encode plaintext
 *
 * @param $str
 * @return string
 */
function cr_encrypt($str){
	return base64_encode($str);
}

/**
 * Decode encrypted string
 *
 * @param $str
 * @return string
 */
function cr_decrypt($str){
	return base64_decode($str);
}