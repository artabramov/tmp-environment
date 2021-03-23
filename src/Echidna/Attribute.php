<?php
namespace artabramov\Echidna\Echidna;

class Attribute extends \artabramov\Echidna\Echidna
{
    const SELECT_LIMIT = 40;

    /**
     * Insert an attribute for the user.
     * @param int $user_id
     * @param string $attribute_key
     * @param int|string $attribute_value
     * @return bool
     */
    public function set( int $user_id, string $attribute_key, int|string $attribute_value ) : bool {

        if( $this->is_empty( $user_id )) {
            $this->error = 'user_id is empty';

        } elseif( !$this->is_id( $user_id )) {
            $this->error = 'user_id is incorrect';

        } elseif( $this->is_empty( $attribute_key )) {
            $this->error = 'attribute_key is empty';

        } elseif( !$this->is_key( $attribute_key )) {
            $this->error = 'attribute_key is incorrect';

        } elseif( $this->is_empty( $attribute_value )) {
            $this->error = 'attribute_value is empty';

        } elseif( !$this->is_value( $attribute_value )) {
            $this->error = 'attribute_value is incorrect';

        } elseif( $this->is_exists( 'user_attributes', [['user_id', '=', $user_id], ['attribute_key', '=', $attribute_key]] )) {
            $this->error = 'attribute is occupied';

        } else {
            $data = [
                'user_id'         => $user_id,
                'attribute_key'   => $attribute_key,
                'attribute_value' => $attribute_value
            ];

            if( !$this->insert( 'user_attributes', $data )) {
                $this->error = 'attribute insert error';
            }
        }

        return empty( $this->error );
    }

    /**
     * Update the attribure of the user.
     * @param int $user_id
     * @param string $attribute_key
     * @param int|string $attribute_value
     * @return bool
     */
    public function put( int $user_id, string $attribute_key, int|string $attribute_value ) : bool {

        if( $this->is_empty( $user_id )) {
            $this->error = 'user_id is empty';

        } elseif( !$this->is_id( $user_id )) {
            $this->error = 'user_id is incorrect';

        } elseif( $this->is_empty( $attribute_key )) {
            $this->error = 'attribute_key is empty';

        } elseif( !$this->is_key( $attribute_key )) {
            $this->error = 'attribute_key is incorrect';

        } elseif( $this->is_empty( $attribute_value )) {
            $this->error = 'attribute_value is empty';

        } elseif( !$this->is_value( $attribute_value )) {
            $this->error = 'attribute_value is incorrect';

        } elseif( !$this->is_exists( 'user_attributes', [['user_id', '=', $user_id], ['attribute_key', '=', $attribute_key]] )) {
            $this->error = 'attribute not found';

        } else {
            $args = [ ['user_id', '=', $user_id], ['attribute_key', '=', $attribute_key] ];
            $data = [ 'attribute_value' => $attribute_value ];

            if( !$this->update( 'user_attributes', $args, $data )) {
                $this->error = 'attribute update error';
            }
        }

        return empty( $this->error );
    }

    /**
     * Delete the attribute of the user.
     * @param int $user_id
     * @param string $attribute_key
     * @return bool
     */
    public function unset( int $user_id, string $attribute_key ) : bool {

        if( $this->is_empty( $user_id )) {
            $this->error = 'user_id is empty';

        } elseif( !$this->is_id( $user_id )) {
            $this->error = 'user_id is incorrect';

        } elseif( $this->is_empty( $attribute_key )) {
            $this->error = 'attribute_key is empty';

        } elseif( !$this->is_key( $attribute_key )) {
            $this->error = 'attribute_key is incorrect';

        } elseif( !$this->is_exists( 'user_attributes', [['user_id', '=', $user_id], ['attribute_key', '=', $attribute_key]] )) {
            $this->error = 'attribute not found';

        } else {
            $args = [ ['user_id', '=', $user_id], ['attribute_key', '=', $attribute_key] ];

            if( !$this->delete( 'user_attributes', $args )) {
                $this->error = 'attribute delete error';
            }
        }

        return empty( $this->error );
    }

    /**
     * Select the attribute of the user.
     * @param int $user_id
     * @param string $attribute_key
     * @return bool
     */
    public function get( int $user_id, string $attribute_key ) : bool {

        $this->clear();

        if( $this->is_empty( $user_id )) {
            $this->error = 'user_id is empty';

        } elseif( !$this->is_id( $user_id )) {
            $this->error = 'user_id is incorrect';

        } elseif( $this->is_empty( $attribute_key )) {
            $this->error = 'attribute_key is empty';

        } elseif( !$this->is_key( $attribute_key )) {
            $this->error = 'attribute_key is incorrect';

        } elseif( !$this->is_exists( 'user_attributes', [['user_id', '=', $user_id], ['attribute_key', '=', $attribute_key]] )) {
            $this->error = 'attribute not found';

        } else {

            $rows = $this->select( 'user_attributes', [['user_id', '=', $user_id], ['attribute_key', '=', $attribute_key]] );

            if( !empty( $rows[0] )) {
                $this->rows = $rows;

            } else {
                $this->error = 'attribute select error';
            }
        }

        return empty( $this->error );
    }

    /**
     * Select all attributes of the user.
     * @param int $user_id
     * @return bool
     */
    public function all( int $user_id ) : bool {

        $this->clear();

        if( $this->is_empty( $user_id )) {
            $this->error = 'user_id is empty';

        } elseif( !$this->is_id( $user_id )) {
            $this->error = 'user_id is incorrect';

        } else {
            $rows = $this->select( 'user_attributes', [['user_id', '=', $user_id]], self::SELECT_LIMIT );

            if( !empty( $rows[0] )) {
                $this->rows = $rows;

            } else {
                $this->error = 'attribute select error';
            }
        }

        return empty( $this->error );
    }

}
