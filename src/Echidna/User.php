<?php
namespace artabramov\Echidna\Echidna;

class User extends \artabramov\Echidna\Echidna
{
    protected $error;
    protected $id;
    protected $date;
    protected $user_status;
    protected $user_token;
    protected $user_email;
    protected $user_pass;
    protected $user_hash;

    // create token
    private function create_token() : string {

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

    // create pass
    private function create_pass( int $pass_len, string $pass_symbs ) : string {

        $user_pass = '';
        $symbs_len = mb_strlen( $pass_symbs, 'utf-8' ) - 1;

        for( $i = 0; $i < $pass_len; $i++ ) {
            $user_pass .= $pass_symbs[ random_int( 0, $symbs_len ) ];
        }

        return $user_pass;
    }

    // get hash
    private function get_hash( string $user_pass ) : string {
        return sha1( $user_pass );
    }

    // register
    public function register( string $user_email ) : bool {

        if( $this->is_empty( $user_email )) {
            $this->error = 'user_email is empty';

        } elseif( !$this->is_email( $user_email )) {
            $this->error = 'user_email is incorrect';

        } elseif( $this->is_exists( 'users', [[ 'user_email', '=', $user_email ]] )) {
            $this->error = 'user_email is occupied';

        } else {
            $data = [
                'user_status' => 'pending',
                'user_token'  => $this->create_token(),
                'user_email'  => $user_email,
                'user_hash'   => ''
            ];
            $user_id = $this->insert( 'users', $data );

            if( !empty( $user_id )) {
                $this->id = $user_id;
                $this->user_status = $data['user_status'];
                $this->user_token = $data['user_token'];
                $this->user_email = $data['user_email'];
                $this->user_hash = '';

            } else {
                $this->error = 'user insert error';
            }
        }
        return empty( $this->error );
    }

    // restore
    public function restore( string $user_email, int $pass_len = 4, string $pass_symbs = '0123456789' ) : bool {

        if( $this->is_empty( $user_email )) {
            $this->error = 'user_email is empty';

        } elseif( !$this->is_email( $user_email )) {
            $this->error = 'user_email is incorrect';

        } elseif( !$this->is_exists( 'users', [[ 'user_email', '=', $user_email ], ['user_status', '<>', 'trash']] )) {
            $this->error = 'user_email not found';

        } else {
            $user_pass = $this->create_pass( $pass_len, $pass_symbs );
            $user_hash = $this->get_hash( $user_pass );

            $args = [[ 'user_email', '=', $user_email ]];
            $data = [ 'user_hash' => $user_hash ];

            if( $this->update( 'users', $args, $data )) {
                $this->user_pass = $user_pass;
                $this->user_hash = $user_hash;

            } else {
                $this->error = 'user update error';
            }
        }
        return empty( $this->error );
    }

    // signin
    public function signin( string $user_email, string $user_pass ) : bool {

        $user_hash = $this->get_hash( $user_pass );

        if( $this->is_empty( $user_email )) {
            $this->error = 'user_email is empty';

        } elseif( !$this->is_email( $user_email )) {
            $this->error = 'user_email is incorrect';

        } elseif( $this->is_empty( $user_pass )) {
            $this->error = 'user_pass is empty';

        } elseif( !$this->is_exists( 'users', [[ 'user_email', '=', $user_email ], [ 'user_hash', '=', $user_hash ], [ 'user_status', '<>', 'trash' ]] )) {
            $this->error = 'user not found';

        } else {

            $args = [[ 'user_email', '=', $user_email ]];

            $data = [
                'user_status' => 'approved',
                'user_hash'   => ''
            ];

            if( $this->update( 'users', $args, $data )) {
                $this->user_status = $data['user_status'];
                $this->user_hash = $data['user_hash'];

            } else {
                $this->error = 'user update error';
            }
        }
        return empty( $this->error );
    }

    // signout +
    public function signout( int $user_id ) : bool {

        if( $this->is_empty( $user_id )) {
            $this->error = 'user_id is empty';

        } elseif( !$this->is_id( $user_id )) {
            $this->error = 'user_id is incorrect';

        } elseif( !$this->is_exists( 'users', [['id', '=', $user_id], ['user_status', '=', 'approved']] )) {
            $this->error = 'user not found';

        } else {
            $user_token = $this->create_token();

            $args = [[ 'id', '=', $user_id ]];
            $data = [ 'user_token' => $user_token ];

            if( $this->update( 'users', $args, $data )) {
                $this->user_token = $data['user_token'];
                
            } else {
                $this->error = 'user update error';
            }
        }
        return empty( $this->error );
    }

    // auth

    // get

    // some

    // set

    // unset

    // clear
    public function clear() {
        $this->error = null;
        $this->id = null;
        $this->date = null;
        $this->user_status = null;
        $this->user_token = null;
        $this->user_email = null;
        $this->user_pass = null;
        $this->user_hash = null;
    }

}
