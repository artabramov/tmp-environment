<?php
namespace artabramov\Echidna\Utils;

class Validator
{
    /**
     * Check is value empty.
     * @param int|string $value
     * @return bool
     */
    public static function is_empty( string $value ) {
        return !empty( is_string( $value ) ? trim( $value ) : $value );
    }

    /**
     * Check is value numeric up to 20 signs.
     * @param int|string $value
     * @return bool
     */
    public static function is_id( int|string $value ) : bool {
        return is_int( $value ) and $value >= 0;
    }

    /**
     * Check is value a correct key string up to 20 signs.
     * @param int|string $value
     * @return bool
     */
    public static function is_keyable( int|string $value, int $length = 20 ) : bool {
        return is_string( $value ) and preg_match("/^[a-z0-9_-]{1,20}$/", $value );
    }

    /**
     * Check is value a correct data string up to 255 signs.
     * @param int|string $value
     * @return bool
     */
    public static function is_varchar( int|string $value, int $length = 255 ) : bool {
        return is_string( $value ) and mb_strlen( $value, 'UTF-8' ) <= 255;
    }

    /**
     * Check is value a correct datetime string.
     * @param int|string $value
     * @return bool
     */
    public static function is_datetime( int|string $value ) : bool {
        if( !is_string( $value ) or !preg_match("/^\d{4}-((0[0-9])|(1[0-2]))-(([0-2][0-9])|(3[0-1])) (([0-1][0-9])|(2[0-3])):[0-5][0-9]:[0-5][0-9]$/", $value )) {
            return false;
        }
        return checkdate( substr( $value, 5, 2 ), substr( $value, 8, 2 ), substr( $value, 0, 4 ));
    }

    /**
     * Check is value a correct token string.
     * @param int|string $value
     * @return bool
     */
    public static function is_token( int|string $value ) : bool {
        return is_string( $value ) and preg_match("/^[a-f0-9]{80}$/", $value );
    }

    /**
     * Check is value a correct hash string.
     * @param int|string $value
     * @return bool
     */
    public static function is_hash( int|string $value ) : bool {
        return is_string( $value ) and preg_match("/^[a-f0-9]{40}$/", $value );
    }

    /**
     * Check is value a correct email string.
     * @param int|string $value
     * @return bool
     */
    public static function is_email( int|string $value ) : bool {
        return is_string( $value ) and preg_match("/^[a-z0-9._-]{2,80}@(([a-z0-9_-]+\.)+(com|net|org|mil|"."edu|gov|arpa|info|biz|inc|name|[a-z]{2})|[0-9]{1,3}\.[0-9]{1,3}\.[0-"."9]{1,3}\.[0-9]{1,3})$/", $value );
    }

}
