<?php

namespace artabramov\Echidna;

class User
{
    private $db;
    private $error;
    private $id;
    private $date;
    private $user_status;
    private $user_token;
    private $user_email;
    private $user_hash;

    public function __construct( \Illuminate\Database\Capsule\Manager $db ) {
        $this->db          = $db;
        $this->error       = '';
        $this->id          = 0;
        $this->date        = '0000-00-00 00:00:00';
        $this->user_status = '';
        $this->user_token  = '';
        $this->user_email  = '';
        $this->user_hash   = '';
    }

    public function __set( string $key, $value ) {
        if( isset( $this->$key )) {
            $this->$key = $value;
        }
    }


    public function __get( string $key ) {
        if( isset( $this->$key )) {
            return $this->$key;
        } else {
            return null;
        }
    }

    private function clear() {
        $this->error       = '';
        $this->id          = 0;
        $this->date        = '0000-00-00 00:00:00';
        $this->user_status = '';
        $this->user_token  = '';
        $this->user_email  = '';
        $this->user_hash   = '';
    }

    private function create_token() : string {
        do {
            $user_token = bin2hex( random_bytes( 40 ));
            if( $this->is_exists( [['user_token', '=', $user_token]] )) {
                $repeat = true;
            } else {
                $repeat = false;
            }
        } while( $repeat );
        return $user_token;
    }

    public function create_pass( int $pass_length ) : string {
        $user_pass = '';
        for( $i = 0; $i < $pass_length; $i++ ) {
            $user_pass .= mt_rand( 0,9 );
        }
        return $user_pass;
    }

    public function create_hash( string $user_pass ) : string {
        return sha1( $user_pass );
    }

    private function is_exists( array $args ) : bool {
            
            $user = $this->db->table('users')->select('id');
            foreach( $args as $where ) {
                $user = $user->where( $where[0], $where[1], $where[2] );
            }
            $user = $user->first();

        return empty( $user->id ) ? false : true;
    }

    /*
    Create a new user.
    */
    public function create( string $user_email, string $user_hash ) : bool {

        $this->clear();

        if( empty( $user_email )) {
            $this->error = 'user_email is empty';

        } elseif( mb_strlen( $user_email, 'utf-8' ) > 255 ) {
            $this->error = 'user_email is too long';

        } elseif( !preg_match("/^[a-z0-9._-]{1,80}@(([a-z0-9-]+\.)+(com|net|org|mil|"."edu|gov|arpa|info|biz|inc|name|[a-z]{2})|[0-9]{1,3}\.[0-9]{1,3}\.[0-"."9]{1,3}\.[0-9]{1,3})$/", $user_email )) {
            $this->error = 'user_email is incorrect';

        } elseif( $this->is_exists( [['user_email', '=', $user_email]] )) {
            $this->error = 'user_email is occupied';

        } elseif( empty( $user_hash )) {
            $this->error = 'user_hash is empty';

        } elseif( mb_strlen( $user_hash, 'utf-8' ) < 40 ) {
            $this->error = 'user_hash is too short';

        } elseif( mb_strlen( $user_hash, 'utf-8' ) > 40 ) {
            $this->error = 'user_hash is too long';

        } else {

            $user_id = $this->db->table('users')->insertGetId([
                'date'        => $this->db::raw('now()'),
                'user_status' => 'pending',
                'user_token'  => $this->create_token(),
                'user_email'  => $user_email,
                'user_hash'   => $user_hash
            ]);

            if( empty( $user_id ) ) {
                $this->error = 'user creation error';
            }
        }

        return empty( $user_id ) ? false : true;
    }
    
    /*
    Get user by id.
    */
    public function select( int $user_id ) : bool {
  
        $this->clear();

        if( empty( $user_id )) {
            $this->error = 'user_id is empty';

        } elseif( strlen( strval( $user_id )) > 20 ) {
            $this->error = 'user_id is too long';

        } else {

            $user = $this->db
            ->table( 'users' )
            ->where([[ 'id', '=', $user_id ]])
            ->select( '*' )
            ->first();

            if( isset( $user->id )) {
                $this->id = $user->id;
                $this->date = $user->date;
                $this->user_status = $user->user_status;
                $this->user_token = $user->user_token;
                $this->user_email = $user->user_email;
                $this->user_hash = $user->user_hash;

            } else {
                $this->error = 'user not found';
            }
        }

        return empty( $user->id ) ? false : true;
    }

    /*
    Get user by token.
    */
    public function auth( string $user_token ) : bool {

        $this->clear();

        if( empty( $user_token )) {
            $this->error = 'user_token is empty';

        } elseif( mb_strlen( $user_token, 'utf-8' ) < 80 ) {
            $this->error = 'user_token is too short';

        } elseif( mb_strlen( $user_token, 'utf-8' ) > 80 ) {
            $this->error = 'user_token is too long';

        } elseif( !$this->is_exists( [['user_token', '=', $user_token], ['user_status', '=', 'approved']] )) {
            $this->error = 'user_token is incorrect';

        } else {
            
            $user = $this->db
            ->table('users')
            ->select('*')
            ->where([ ['user_token', '=', $user_token] ])
            ->first();

            if( !empty( $user->id )) {
                $this->id          = $user->id;
                $this->date        = $user->date;
                $this->user_status = $user->user_status;
                $this->user_token  = $user->user_token;
                $this->user_email  = $user->user_email;
                $this->user_hash   = $user->user_hash;
            }
        }

        return empty( $this->id ) ? false : true;
    }

    /*
    Get user by email and hash.
    */
    public function signin( string $user_email, string $user_hash ) : bool {

        $this->clear();

        if( empty( $user_email )) {
            $this->error = 'user_email is empty';

        } elseif( mb_strlen( $user_email, 'utf-8' ) > 255 ) {
            $this->error = 'user_email is is too long';

        } elseif( !preg_match("/^[a-z0-9._-]{1,80}@(([a-z0-9-]+\.)+(com|net|org|mil|"."edu|gov|arpa|info|biz|inc|name|[a-z]{2})|[0-9]{1,3}\.[0-9]{1,3}\.[0-"."9]{1,3}\.[0-9]{1,3})$/", $user_email )) {
            $this->error = 'user_email is incorrect';

        } elseif( empty( $user_hash )) {
            $this->error = 'user_hash is empty';

        } elseif( mb_strlen( $user_hash, 'utf-8' ) < 40 ) {
            $this->error = 'user_hash is too short';

        } elseif( mb_strlen( $user_hash, 'utf-8' ) > 40 ) {
            $this->error = 'user_hash is too long';

        } else {
            $user = $this->db
            ->table('users')
            ->select('*')
            ->where([ ['user_email', '=', $user_email], ['user_hash', '=', $user_hash] ])
            ->first();

            if( !empty( $user->id )) {

                $this->id          = $user->id;
                $this->date        = $user->date;
                $this->user_status = $user->user_status;
                $this->user_token  = $user->user_token;
                $this->user_email  = $user->user_email;
                $this->user_hash   = $user->user_hash;

                $this->db
                ->table('users')
                ->where([[ 'id', '=', $user->id ]])
                ->update([ 'user_hash' => '' ]);

                if( $user->user_status == 'pending' ) {
                    $this->db
                    ->table('users')
                    ->where([[ 'id', '=', $user->id ]])
                    ->update([ 'user_status' => 'approved' ]);

                    $this->user_status = 'approved';
                }

            } else {
                $this->error = 'user not found';
            }
        }

        return empty( $this->id ) ? false : true;
    }

    /*
    Logout.
    */
    public function signout( string $user_token ) : bool {

        $this->clear();

        if( empty( $user_token )) {
            $this->error = 'user_token is empty';

        } elseif( mb_strlen( $user_token, 'utf-8' ) < 80 ) {
            $this->error = 'user_token is too short';

        } elseif( mb_strlen( $user_token, 'utf-8' ) > 80 ) {
            $this->error = 'user_token is too long';

        } elseif( !$this->is_exists( [['user_token', '=', $user_token], ['user_status', '=', 'approved']] )) {
            $this->error = 'user_token is incorrect';

        } else {

            $result = $this->db
            ->table('users')
            ->where([ ['user_token', '=', $user_token] ])
            ->update([ 'user_token' => $this->create_token() ]);
        }

        return empty( $result ) ? false : true;
    }

    /*
    Update user email (also token and status).
    */
    public function update( string $user_token, string $user_email ) : bool {

        $this->clear();

        if( empty( $user_token )) {
            $this->error = 'user_token is empty';

        } elseif( mb_strlen( $user_token, 'utf-8' ) < 80 ) {
            $this->error = 'user_token is too short';

        } elseif( mb_strlen( $user_token, 'utf-8' ) > 80 ) {
            $this->error = 'user_token is too long';

        } elseif( !$this->is_exists( [['user_token', '=', $user_token], ['user_status', '=', 'approved']] )) {
            $this->error = 'user_token is incorrect';

        } elseif( empty( $user_email )) {
            $this->error = 'user_email is empty';

        } elseif( mb_strlen( $user_email, 'utf-8' ) > 255 ) {
            $this->error = 'user_email is too long';

        } elseif( !preg_match("/^[a-z0-9._-]{1,80}@(([a-z0-9-]+\.)+(com|net|org|mil|"."edu|gov|arpa|info|biz|inc|name|[a-z]{2})|[0-9]{1,3}\.[0-9]{1,3}\.[0-"."9]{1,3}\.[0-9]{1,3})$/", $user_email )) {
            $this->error = 'user_email is incorrect';

        } elseif( $this->is_exists( [['user_email', '=', $user_email]] )) {
            $this->error = 'user_email is occupied';

        } else {

            $result = $this->db
            ->table('users')
            ->where([ ['user_token', '=', $user_token] ])
            ->update([ 'user_status' => 'pending' ])
            ->update([ 'user_token' => $this->create_token() ])
            ->update([ 'user_email' => $user_email ]);
        }

        return empty( $result ) ? false : true;
    }

}