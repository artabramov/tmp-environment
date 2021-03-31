<?php
namespace artabramov\Echidna\Core;

class User extends \artabramov\Echidna\Echidna
{
    private $id;
    private $date;
    private $user_status;
    private $user_token;
    private $user_email;
    private $user_hash;
    
    /**
     * Generate and set 40-signs unique user_token.
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

}
