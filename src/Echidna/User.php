<?php
namespace artabramov\Echidna\Echidna;

class User extends \artabramov\Echidna\Echidna
{

    /**
     * Create 40-signs unique token.
     * @return string
     */
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

    /**
     * Generate non-unique one-time password.
     * @param int $pass_len
     * @param string $pass_symbs
     * @return string
     */
    private function create_pass( int $pass_len, string $pass_symbs ) : string {

        $user_pass = '';
        $symbs_len = mb_strlen( $pass_symbs, 'utf-8' ) - 1;

        for( $i = 0; $i < $pass_len; $i++ ) {
            $user_pass .= $pass_symbs[ random_int( 0, $symbs_len ) ];
        }

        return $user_pass;
    }

    /**
     * Get hash (sha1) of the password.
     * @param string $user_pass
     * @return string
     */
    private function get_hash( string $user_pass ) : string {
        return sha1( $user_pass );
    }

    /**
     * Register a new user.
     * @param int $user_email
     * @return bool
     */
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

            if( !$this->insert( 'users', $data ) ) {
                $this->error = 'user insert error';
            }            
        }
        return empty( $this->error );
    }

    /**
     * Restore a user.
     * @param string $user_email
     * @param int $pass_len
     * @param string $pass_symbs
     * @return bool
     */
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

            if( !$this->update( 'users', $args, $data )) {
                $this->error = 'user update error';
            }
        }
        return empty( $this->error );
    }

    /**
     * Signin.
     * @param string $user_email
     * @param string $user_pass
     * @return bool
     */
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

            if( !$this->update( 'users', $args, $data )) {
                $this->error = 'user update error';
            }
        }
        return empty( $this->error );
    }

    /**
     * Signout.
     * @param int $user_id
     * @return bool
     */
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

            if( !$this->update( 'users', $args, $data )) {
                $this->error = 'user update error';
            }
        }

        return empty( $this->error );
    }

    /**
     * User auth.
     * @param string $user_token
     * @return bool
     * 
     */
    public function auth( string $user_token ) : bool {

        $this->clear();

        if( $this->is_empty( $user_token )) {
            $this->error = 'user_token is empty';

        } elseif( !$this->is_token( $user_token )) {
            $this->error = 'user_token is incorrect';

        } else {

            $args = [['user_token', '=', $user_token], ['user_status', '=', 'approved']];
            $rows = $this->select( 'users', $args );

            if( !empty( $rows[0] )) {
                $this->rows = $rows;

            } else {
                $this->error = 'user auth error';
            }

        }

        return empty( $this->error );
    }

    /**
     * Select a user.
     * @param int $user_id
     * @return bool
     */
    public function get( int $user_id ) : bool {

        $this->clear();

        if( $this->is_empty( $user_id )) {
            $this->error = 'user_id is empty';

        } elseif( !$this->is_id( $user_id )) {
            $this->error = 'user_id is incorrect';

        } else {

            $args = [['id', '=', $user_id]];
            $rows = $this->select( 'users', $args );

            if( !empty( $rows[0] )) {
                $this->rows = $rows;

            } else {
                $this->error = 'user select error';
            }
        }

        return empty( $this->error );
    }

}
