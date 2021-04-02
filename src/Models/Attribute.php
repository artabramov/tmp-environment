<?php
namespace artabramov\Echidna\Models;
use \artabramov\Echidna\Utilities\Filter;

/**
 * @implements Sequenceable
 */
class Attribute extends \artabramov\Echidna\Models\Echidna implements \artabramov\Echidna\Interfaces\Sequenceable
{
    private $error;
    private $id;
    private $date;
    private $user_id;
    private $attribute_key;
    private $attribute_value;

    public function __get( string $key ) {
        if( property_exists( $this, $key )) {
            return $this->$key;
        }
        return false;
    }

    public function __isset( string $key ) {
        if( property_exists( $this, $key )) {
            return !empty( $this->$key );
        }
        return false;
    }

    public function clear() {
        $this->e = null;
        $this->error = null;
        $this->id = null;
        $this->date = null;
        $this->user_id = null;
        $this->attribute_key = null;
        $this->attribute_value = null;
    }

    /**
     * @param int $user_id
     * @param string $attribute_key
     * @param mixed $attribute_value
     * @param int $min_length
     * @param int $max_length
     * @return bool
     */
    public function set( int $user_id, string $attribute_key, mixed $attribute_value, int $min_length, int $max_length ) : bool {

        if( Filter::is_empty( $user_id )) {
            $this->error = 'user_id is empty';

        } elseif( !Filter::is_int( $user_id )) {
            $this->error = 'user_id is incorrect';

        } elseif( Filter::is_empty( $attribute_key )) {
            $this->error = 'attribute_key is empty';

        } elseif( !Filter::is_key( $attribute_key, 20 )) {
            $this->error = 'attribute_key is incorrect';

        } elseif( Filter::is_empty( $attribute_value )) {
            $this->error = 'attribute_value is empty';

        } elseif( !Filter::is_string( $attribute_value, $min_length, $max_length )) {
            $this->error = 'attribute_value is incorrect';

        } elseif( $this->count( 'user_attributes', [['user_id', '=', $user_id], ['attribute_key', '=', $attribute_key]] ) > 0 ) {
            $this->error = 'attribute is occupied';

        } else {
            $this->clear();
            $this->user_id = $user_id;
            $this->attribute_key = $attribute_key;
            $this->attribute_value = $attribute_value;

            $data = [
                'user_id'         => $this->user_id,
                'attribute_key'   => $this->attribute_key,
                'attribute_value' => $this->attribute_value
            ];

            $this->id = $this->insert( 'user_attributes', $data );

            if( empty( $this->id )) {
                $this->error = 'attribute insert error';
            }   
        }

        return empty( $this->error );
    }

    /**
     * @param int $user_id
     * @param string $attribute_key
     * @param mixed $attribute_value
     * @return bool
     */
    public function put( int $user_id, string $attribute_key, mixed $attribute_value, int $min_length, int $max_length ) : bool {

        if( Filter::is_empty( $user_id )) {
            $this->error = 'user_id is empty';

        } elseif( !Filter::is_int( $user_id )) {
            $this->error = 'user_id is incorrect';

        } elseif( Filter::is_empty( $attribute_key )) {
            $this->error = 'attribute_key is empty';

        } elseif( !Filter::is_key( $attribute_key, 20 )) {
            $this->error = 'attribute_key is incorrect';

        } elseif( Filter::is_empty( $attribute_value )) {
            $this->error = 'attribute_value is empty';

        } elseif( !Filter::is_string( $attribute_value, $min_length, $max_length )) {
            $this->error = 'attribute_value is incorrect';

        } elseif( $this->count( 'user_attributes', [['user_id', '=', $user_id], ['attribute_key', '=', $attribute_key]] ) == 0 ) {
            $this->error = 'attribute not found';

        } else {
            $this->clear();
            $this->user_id = $user_id;
            $this->attribute_key = $attribute_key;
            $this->attribute_value = $attribute_value;

            $args = [ ['user_id', '=', $this->user_id], ['attribute_key', '=', $this->attribute_key] ];
            $data = [ 'attribute_value' => $this->attribute_value ];

            if( !$this->update( 'user_attributes', $args, $data )) {
                $this->error = 'attribute update error';
            }
        }

        return empty( $this->error );
    }

    /**
     * @param int $user_id
     * @param string $attribute_key
     * @return bool
     */
    public function unset( int $user_id, string $attribute_key ) : bool {

        if( Filter::is_empty( $user_id )) {
            $this->error = 'user_id is empty';

        } elseif( !Filter::is_int( $user_id )) {
            $this->error = 'user_id is incorrect';

        } elseif( Filter::is_empty( $attribute_key )) {
            $this->error = 'attribute_key is empty';

        } elseif( !Filter::is_key( $attribute_key, 20 )) {
            $this->error = 'attribute_key is incorrect';

        } elseif( $this->count( 'user_attributes', [['user_id', '=', $user_id], ['attribute_key', '=', $attribute_key]] ) == 0 ) {
            $this->error = 'attribute not found';

        } else {
            $this->clear();
            $this->user_id = $user_id;
            $this->attribute_key = $attribute_key;

            $args = [ ['user_id', '=', $this->user_id], ['attribute_key', '=', $this->attribute_key] ];

            if( !$this->delete( 'user_attributes', $args )) {
                $this->error = 'attribute delete error';
            }
        }

        return empty( $this->error );
    }

    /**
     * @param int $user_id
     * @param string $attribute_key
     * @return bool
     */
    public function get( int $user_id, string $attribute_key ) : bool {

        if( Filter::is_empty( $user_id )) {
            $this->error = 'user_id is empty';

        } elseif( !Filter::is_int( $user_id )) {
            $this->error = 'user_id is incorrect';

        } elseif( Filter::is_empty( $attribute_key )) {
            $this->error = 'attribute_key is empty';

        } elseif( !Filter::is_key( $attribute_key, 20 )) {
            $this->error = 'attribute_key is incorrect';

        } elseif( $this->count( 'user_attributes', [['user_id', '=', $user_id], ['attribute_key', '=', $attribute_key]] ) == 0 ) {
            $this->error = 'attribute not found';

        } else {
            $this->clear();
            $this->user_id = $user_id;
            $this->attribute_key = $attribute_key;

            $rows = $this->select( '*', 'user_attributes', [['user_id', '=', $user_id], ['attribute_key', '=', $attribute_key]], 1, 0 );

            if( empty( $rows[0] )) {
                $this->error = 'attribute select error';

            } else {
                foreach( $rows[0] as $key=>$value ) {
                    $this->$key = $value;
                }
            }
        }

        return empty( $this->error );
    }

    /**
     * This is a part of the Sequence interface. Get the element by id.
     * @param mixed $attribute_id
     * @return bool
     */
    public function getone( mixed $attribute_id ) : bool {

        if( Filter::is_empty( $attribute_id )) {
            $this->error = 'attribute_id is empty';

        } elseif( !Filter::is_int( $attribute_id )) {
            $this->error = 'attribute_id is incorrect';

        } elseif( $this->count( 'user_attributes', [['attribute_id', '=', $attribute_id]] ) == 0 ) {
            $this->error = 'attribute not found';

        } else {
            $this->clear();
            $this->attribute_id = $attribute_id;

            $rows = $this->select( '*', 'user_attributes', [['attribute_id', '=', $attribute_id]], 1, 0 );

            if( empty( $rows[0] )) {
                $this->error = 'attribute select error';

            } else {
                foreach( $rows[0] as $key=>$value ) {
                    $this->$key = $value;
                }
            }
        }

        return empty( $this->error );
    }

}
