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

    // get token +
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

    // generate pass +
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

    // user register +
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

            if( !$this->insert( 'users', $data )) {
                $this->error = 'user insert error';
            }
        }

        return empty( $this->error );
    }

    // user restore
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

            if( $this->updated( 'users', [[ 'user_email', '=', $user_email ]], [ 'user_hash' => $user_hash ] )) {
                $this->user_pass = $user_pass;
                $this->user_hash = $user_hash;

            } else {
                $this->error = 'user update error';
            }
        }
    }

    // user signin

    // user signout

    // user auth

    // user select

    // user update

    // user trash

}
