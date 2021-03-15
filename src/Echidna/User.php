<?php
namespace artabramov\Echidna\Echidna;

class User extends \artabramov\Echidna\Echidna
{
    private $error;
    private $id;
    private $date;
    private $user_status;
    private $user_token;
    private $user_email;
    private $user_pass;
    private $user_hash;

    // get token +
    private function get_token() : string {

        do {
            $user_token = bin2hex( random_bytes( 40 ));

            if( $this->is_exists( 'users', [['user_token', '=', $user_token]] )) {
                $repeat = true;
                
            } else {
                $repeat = false;
            }
        } while( $repeat );

        return $user_token;
    }

    // user register +
    public function register( string $user_email ) : bool {

        //return false;

        if( $this->is_empty( $user_email )) {
            $this->error = 'user_email is empty';

        } elseif( !$this->is_email( $user_email )) {
            $this->error = 'user_email is incorrect';

        } elseif( $this->is_exists( 'users', [[ 'user_email', '=', $user_email ]] )) {
            $this->error = 'user_email is occupied';

        } else {
            $data = [
                'user_status' => 'pending',
                'user_token'  => $this->get_token(),
                'user_email'  => $user_email,
                'user_hash'   => ''
            ];

            if( !$this->is_insert( 'users', $data )) {
                $this->error = 'user insert error';
            }
        }

        return empty( $this->error );
    }

}
