<?php

namespace PushNotify;

class Functions {
	/**
	 * Checks if predicate returns truthy for all elements of array
	 *
	 * @param array $callback
	 * @param array $array
	 *
	 * @return bool
	 */
	public static function array_every( array $callback, array $array ): bool {
		foreach ( $array as $item ) {
			if ( ! call_user_func( $callback, $item ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Checks if predicate returns truthy for some elements of array
	 *
	 * @param callable $callback
	 * @param array    $array
	 *
	 * @return bool
	 */
	public static function array_some( callable $callback, array $array ): bool {
		foreach ( $array as $item ) {
			if ( call_user_func( $callback, $item ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Compare value with operator
	 *
	 * @param string $operator
	 * @param $value1
	 * @param $value2
	 *
	 * @return bool
	 */
	public static function operators( string $operator, $value1, $value2 ): bool {
		switch ( $operator ) {
			case 'is_equal_to':
				return $value1 == $value2;
			case 'is_not_equal_to':
				return $value1 != $value2;
			case 'is_empty':
				return empty( $value2 );
			case 'is_not_empty':
				return ! empty( $value2 );
			case 'contains':
				return str_contains( $value1, $value2 );
			case 'does_not_contain':
				return ! str_contains( $value1, $value2 );
			case 'match_regular_expressions':
				return preg_match( $value2, $value1 ) == 1;
			case 'is_less_than':
				return $value1 < $value2;
			case 'is_less_or_equal_to':
				return $value1 <= $value2;
			case 'is_greater_than':
				return $value1 > $value2;
			case 'is_greater_or_equal_to':
				return $value1 >= $value2;
			default:
				return false;
		}
	}

	/**
	 * Pre tokens arrays
	 *
	 * @param $tokens
	 *
	 * @return array
	 */
	public static function preTokens( $tokens ): array {
		$data = array();

		if ( is_array( $tokens ) ) {

			foreach ( $tokens as $value ) {
				$data[] = $value->token;
			}
		}

		return $data;
	}

	/**
	 * Check if a string is a valid JSON
	 *
	 * @param string $srt The string to check.
	 * @return bool
	 */
	public static function is_valid_json( $srt ) {
		return json_decode( $srt ) !== null;
	}

	/**
	 * Check is regex pattern
	 *
	 * @param string $pattern The pattern to check.
	 *
	 * @return bool
	 */
	public static function is_regex_pattern( string $pattern ): bool {
		return preg_match( '/^\/.*\/[a-zA-Z]*$/', $pattern ) === 1;
	}

	/**
	 * Get regex matches
	 *
	 * @param string $pattern The pattern to search for, as a string.
	 * @param string $subject The subject to check.
	 *
	 * @return array
	 */
	public static function get_regex_matches( string $pattern, string $subject ): array {
		preg_match( $pattern, $subject, $matches );
		return $matches;
	}
}
