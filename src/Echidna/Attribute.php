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
    public function is_exists( int $user_id, string $attribute_key ) : bool {
    }


}
