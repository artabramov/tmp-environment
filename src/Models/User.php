<?php
namespace artabramov\Echidna\Models;
use \artabramov\Echidna\Services\Filter;

class User extends \artabramov\Echidna\Models\Echidna implements \artabramov\Echidna\Interfaces\Sequenceable
{
    private $error;
    private $id;
    private $date;
    private $user_status;
    private $user_token;
    private $user_email;
    private $user_pass;
    private $user_hash;

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
        $this->user_status = null;
        $this->user_token = null;
        $this->user_email = null;
        $this->user_pass = null;
        $this->user_hash = null;
    }
    
    /**
     * Set 40-signs unique user_token.
     */
    private function set_token() {

        do {
            $user_token = bin2hex( random_bytes( 40 ));

            if( $this->count( 'users', [['user_token', '=', $user_token]] ) > 0 ) {
                $repeat = true;
                
            } else {
                $repeat = false;
            }
        } while( $repeat );

        $this->user_token = $user_token;
    }

    /**
     * Set non-unique one-time password.
     * @param int $pass_len
     * @param string $pass_symbs
     */
    private function set_pass( string $symbols, int $length ) {

        $user_pass = '';
        $symbols_length = mb_strlen( $symbols, 'utf-8' ) - 1;

        for( $i = 0; $i < $length; $i++ ) {
            $user_pass .= $symbols[ random_int( 0, $symbols_length ) ];
        }

        $this->user_pass = $user_pass;
    }

    /**
     * Set hash (sha1) of the password.
     */
    private function set_hash() {
        $this->user_hash = sha1( $this->user_pass );
    }

    /**
     * Register a new user.
     * @param string $user_email
     * @return bool
     */
    public function register( string $user_email ) : bool {

        $this->clear();

        if( Filter::is_empty( $user_email )) {
            $this->error = 'user_email is empty';

        } elseif( !Filter::is_email( $user_email )) {
            $this->error = 'user_email is incorrect';

        } elseif( $this->count( 'users', [[ 'user_email', '=', $user_email ]] ) > 0 ) {
            $this->error = 'user_email is occupied';

        } else {
            $this->set_token();

            $data = [
                'user_status' => 'pending',
                'user_token'  => $this->user_token,
                'user_email'  => $user_email,
                'user_hash'   => ''
            ];

            $user_id = $this->insert( 'users', $data );

            if( !empty( $user_id )) {
                $this->id = $user_id;

            } else {
                $this->error = 'user insert error';
            }            
        }
        return empty( $this->error );
    }

    public function getone( $user_id ) {
        $this->id = $user_id;
    }

}
