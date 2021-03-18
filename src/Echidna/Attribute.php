<?php
namespace artabramov\Echidna\Echidna;

class Attribute extends \artabramov\Echidna\Echidna
{
    protected $error;
    protected $id;
    protected $date;
    protected $user_id;
    protected $attribute_key;
    protected $attribute_value;

    /**
     * Insert attribute for the user.
     */
    public function insert( int $user_id, string $attribute_key, int|string $attribute_value ) : bool {

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

        } else {
            $data = [
                'user_id'         => $user_id,
                'attribute_key'   => $attribute_key,
                'attribute_value' => $attribute_value
            ];

            if( !$this->inserted( 'user_attributes', $data )) {
                $this->error = 'attribute insert error';
            }
        }

        return empty( $this->error );
    }

    /**
     * Update attribure of the user.
     */
    public function update( int $user_id, string $attribute_key, int|string $attribute_value ) : bool {
    }

    /**
     * Select attribute of the user.
     */
    public function select( int $user_id, string $attribute_key ) : array|bool {
    }

    /**
     * Select all attributes of the user.
     */
    public function select_all( int $user_id ) : arra {
    }

    /**
     * Delete attribute of the user.
     */
    public function delete( int $user_id, string $attribute_key ) : bool {
    }

    /**
     * Check attribute of the user is exists.
     */
    public function exists( int $user_id, string $attribute_key ) : bool {
    }


}
