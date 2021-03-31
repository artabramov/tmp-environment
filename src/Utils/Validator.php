<?php
namespace artabramov\Echidna\Utils;

class Validator
{
    /**
     * Check is value empty string or zero.
     * @param int|string $value
     * @return bool
     */
    public function isEmpty( int|string $value ) : bool {
        return empty( is_string( $value ) ? trim( $value ) : $value );
    }

    /**
     * Check is value integer and not zero.
     * @param int|string $value
     * @return bool
     */
    public static function isId( int|string $value ) : bool {
        return is_int( $value ) and $value >= 0;
    }

    /**
     * Check is value a correct key string (a-z0-9_-).
     * @param int|string $value
     * @param int $length
     * @return bool
     */
    public static function isKey( int|string $value, int $length ) : bool {
        return is_string( $value ) and preg_match("/^[a-z0-9_-]{1," . $length . "}$/", $value );
    }

    /**
     * Check is value a string.
     * @param int|string $value
     * @param int $length
     * @return bool
     */
    public static function isString( int|string $value, int $length ) : bool {
        return is_string( $value ) and mb_strlen( $value, 'UTF-8' ) <= 255;
    }

    /**
     * Check is value a correct hex string.
     * @param int|string $value
     * @param int $length
     * @return bool
     */
    public static function isHex( int|string $value, int $length ) : bool {
        return is_string( $value ) and preg_match("/^[a-f0-9]{" . $length . "}$/", $value );
    }
    
    /**
     * Check is value a correct datetime string.
     * @param int|string $value
     * @return bool
     */
    public static function isDatetime( int|string $value ) : bool {
        if( !is_string( $value ) or !preg_match("/^\d{4}-((0[0-9])|(1[0-2]))-(([0-2][0-9])|(3[0-1])) (([0-1][0-9])|(2[0-3])):[0-5][0-9]:[0-5][0-9]$/", $value )) {
            return false;
        }
        return checkdate( substr( $value, 5, 2 ), substr( $value, 8, 2 ), substr( $value, 0, 4 ));
    }

    /**
     * Check is value a correct email string.
     * @param int|string $value
     * @param int $length
     * @return bool
     */
    public static function isEmail( int|string $value, int $length ) : bool {
        return is_string( $value ) and mb_strlen( $value ) <= $length and preg_match("/^[a-z0-9._-]{2,80}@(([a-z0-9_-]+\.)+(com|net|org|mil|"."edu|gov|arpa|info|biz|inc|name|[a-z]{2})|[0-9]{1,3}\.[0-9]{1,3}\.[0-"."9]{1,3}\.[0-9]{1,3})$/", $value );
    }

}
