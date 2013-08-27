<?php namespace Elegant;
class Helper {

	/**
	 * Convert a value to studly caps case (StudlyCapCase).
	 *
	 * @param  string  $value
	 * @return string
	 */
	public static function studlyCase($value)
	{
		$value = ucwords(str_replace(array('-', '_'), ' ', $value));

		return str_replace(' ', '', $value);
	}

	/**
	 * Convert a value to camel case (camelCase).
	 *
	 * @param  string  $value
	 * @return string
	 */
	public static function camelCase($value)
	{
		return lcfirst(static::studlyCase($value));
	}

}