<?php
/**
 * Created by PhpStorm.
 * User: ideapeople
 * Date: 2016-08-24
 * Time: 오후 11:36
 */

namespace ideapeople\util\http;

use ideapeople\util\common\Utils;

class Request {
	const METHOD_GET = 'GET';
	const METHOD_POST = 'POST';
	const METHOD_REQUEST = 'REQUEST';

	/**
	 * @param        $key
	 * @param null $defaultValue
	 * @param string $method
	 *
	 * @return bool|null|mixed
	 */
	static function get_parameter( $key, $defaultValue = null, $method = self::METHOD_REQUEST ) {
		if ( $method == self::METHOD_REQUEST ) {
			return Utils::get_value( $_REQUEST, $key, $defaultValue );
		}

		if ( $method == self::METHOD_POST ) {
			return Utils::get_value( $_POST, $key, $defaultValue );
		}

		if ( $method == self::METHOD_GET ) {
			return Utils::get_value( $_GET, $key, $defaultValue );
		}

		return false;
	}

	static function get_parameter_post( $key, $defaultValue = null ) {
		return self::get_parameter( $key, $defaultValue, self::METHOD_POST );
	}

	static function get_parameter_get( $key, $defaultValue = null ) {
		return self::get_parameter( $key, $defaultValue, self::METHOD_GET );
	}

	static function get_files( $name, $single = false, $include_empty = true ) {
		$keys   = array( 'name', 'type', 'tmp_name', 'error', 'size' );
		$result = array();

		if ( ! is_array( @ $_FILES[ $name ][ 'name' ] ) ) {
			$v = array();

			foreach ( $keys as $key ) {
				$v[ $key ] = @$_FILES[ $name ][ $key ];
			}

			if ( empty( $v[ 'name' ] ) && ! $include_empty ) {

			} else {
				$result[] = $v;
			}
		} else {
			$count = count( @$_FILES[ $name ][ 'name' ] );

			for ( $i = 0; $i < $count; $i ++ ) {
				$v = array();

				foreach ( $keys as $key ) {
					$v[ $key ] = $_FILES[ $name ][ $key ][ $i ];
				}

				if ( empty( $v[ 'name' ] ) && ! $include_empty ) {

				} else {
					$result[] = $v;
				}
			}
		}

		if ( $single && ! empty( $result ) ) {
			return $result[ 0 ];
		}

		return $result;
	}

	static function get_request_method() {
		return $_SERVER[ 'REQUEST_METHOD' ];
	}

	static function is_get() {
		return self::get_request_method() == 'GET';
	}

	static function is_post() {
		return self::get_request_method() == 'POST';
	}

	static function is_put() {
		return self::get_request_method() == 'PUT';
	}

	static function is_delete() {
		return self::get_request_method() == 'DELETE';
	}

	static function is_connect() {
		return self::get_request_method() == 'CONNECT';
	}

	static function parse_host( $url ) {
		$arr = parse_url( $url );

		$port = @$arr[ 'port' ];

		return "{$arr['scheme']}:{$port}//{$arr['host']}{$arr['path']}";
	}


	static function get_url_prefix( $url ) {
		if ( strpos( $url, '?' ) === false ) {
			return '?';
		} else {
			return '&';
		}
	}


	static function get_current_url() {
		$url = '';

		if ( isset( $_SERVER[ 'HTTPS' ] ) && filter_var( $_SERVER[ 'HTTPS' ], FILTER_VALIDATE_BOOLEAN ) ) {
			$url .= 'https';
		} else {
			$url .= 'http';
		}

		$url .= '://';

		if ( isset( $_SERVER[ 'HTTP_HOST' ] ) ) {
			$url .= $_SERVER[ 'HTTP_HOST' ];
		} elseif ( isset( $_SERVER[ 'SERVER_NAME' ] ) ) {
			$url .= $_SERVER[ 'SERVER_NAME' ];
		} else {
			trigger_error( 'Could not get URL from $_SERVER vars' );
		}

		if ( $_SERVER[ 'SERVER_PORT' ] != '80' ) {
			$url .= ':' . $_SERVER[ "SERVER_PORT" ];
		}

		if ( isset( $_SERVER[ 'REQUEST_URI' ] ) ) {
			$url .= $_SERVER[ 'REQUEST_URI' ];
		} elseif ( isset( $_SERVER[ 'PHP_SELF' ] ) ) {
			$url .= $_SERVER[ 'PHP_SELF' ];
		} elseif ( isset( $_SERVER[ 'REDIRECT_URL' ] ) ) {
			$url .= $_SERVER[ 'REDIRECT_URL' ];
		} else {
			trigger_error( 'Could not get URL from $_SERVER vars' );
		}

		return $url;
	}
}