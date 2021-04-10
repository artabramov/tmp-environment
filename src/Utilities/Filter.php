<?php
namespace artabramov\Echidna\Utilities;

class Filter
{
    /**
     * Check is value empty string or zero.
     * @param mixed $value
     * @return bool
     */
    public static function is_empty( mixed $value ) : bool {
        return empty( is_string( $value ) ? trim( $value ) : $value );
    }

    /**
     * Check is value integer and not zero.
     * @param mixed $value
     * @return bool
     */
    public static function is_int( mixed $value, int $min_value = 0, int $max_value = 9223372036854775807 ) : bool {
        return ( is_int( $value ) and $value >= $min_value and $value <= $max_value ) or ( is_string( $value ) and ctype_digit( $value ) and intval( $value ) >= $min_value and intval( $value ) <= $max_value );
    }

    /**
     * Check is value a correct key string (a-z0-9_-).
     * @param mixed $value
     * @param int $length
     * @return bool
     */
    public static function is_key( mixed $value, int $max_length = 20 ) : bool {
        return is_string( $value ) and preg_match("/^[a-z0-9_-]{1," . $max_length . "}$/", $value );
    }

    /**
     * Check is value a correct status.
     * @param mixed $value
     * @param string $key
     * @return bool
     */
    public static function is_status( mixed $value, string $key ) : bool {

        if( $key == 'user' ) {
            return in_array( $value, [ 'pending', 'approved', 'trash' ] );

        } elseif( $key == 'hub' ) {
            return in_array( $value, [ 'custom', 'trash' ] );

        } elseif( $key == 'post' ) {
            return in_array( $value, [ 'inherit', 'draft', 'todo', 'doing', 'done', 'trash' ] );
        }
        
        return false;
    }

    /**
     * Check is value a correct type.
     * @param mixed $value
     * @param string $key
     * @return bool
     */
    public static function is_type( mixed $value, string $key ) : bool {

        if( $key == 'post' ) {
            return in_array( $value, [ 'document', 'comment' ] );
        }
        
        return false;
    }


    /**
     * Check is value a correct role.
     * @param mixed $value
     * @return bool
     */
    public static function is_role( mixed $value ) : bool {
        return in_array( $value, [ 'admin', 'editor', 'reader', 'invited' ] );
    }

    /**
     * Check is value a string.
     * @param mixed $value
     * @param int $min_length
     * @param int $max_length
     * @return bool
     */
    public static function is_string( mixed $value, int $min_length = 1, int $max_length = 255 ) : bool {
        $length = mb_strlen( $value, 'UTF-8' );
        return is_string( $value ) and $length >= $min_length and $length <= $max_length;
    }

    /**
     * Check is value a correct hex string.
     * @param mixed $value
     * @param int $length
     * @return bool
     */
    public static function is_hex( mixed $value, int $length ) : bool {
        return is_string( $value ) and preg_match("/^[a-f0-9]{" . $length . "}$/", $value );
    }
    
    /**
     * Check is value a correct datetime string.
     * @param mixed $value
     * @return bool
     */
    public static function is_datetime( mixed $value ) : bool {
        if( !is_string( $value ) or !preg_match("/^\d{4}-((0[0-9])|(1[0-2]))-(([0-2][0-9])|(3[0-1])) (([0-1][0-9])|(2[0-3])):[0-5][0-9]:[0-5][0-9]$/", $value )) {
            return false;
        }
        return checkdate( substr( $value, 5, 2 ), substr( $value, 8, 2 ), substr( $value, 0, 4 ));
    }

    /**
     * Check is value a correct email string.
     * @param mixed $value
     * @return bool
     */
    public static function is_email( mixed $value ) : bool {
        return is_string( $value ) and preg_match("/^[a-z0-9._-]{2,80}@(([a-z0-9_-]+\.)+(com|net|org|mil|"."edu|gov|arpa|info|biz|inc|name|[a-z]{2})|[0-9]{1,3}\.[0-9]{1,3}\.[0-"."9]{1,3}\.[0-9]{1,3})$/", $value );
    }

}
