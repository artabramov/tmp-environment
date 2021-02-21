<?php

namespace artabramov\Echidna;

class User
{
    private $db;
    private $error;
    private $data;

    // create the object
    public function __construct( \Illuminate\Database\Capsule\Manager $db ) {

        $this->db    = $db;
        $this->error = '';
        $this->data  = [
            'id'          => 0,
            'date'        => '',
            'user_status' => '',
            'user_token'  => '',
            'user_email'  => '',
            'user_pass'   => '',
            'user_hash'   => ''
        ];
    }

    // set the data
    public function __set( string $key, $value ) {
        if( array_key_exists( $key, $this->data ) ) {
            $this->data[ $key ] = $value;
        }
    }

    // get the data
    public function __get( string $key ) {
        if( $key == 'error' ) {
            return $this->error;
        } elseif( array_key_exists( $key, $this->data ) ) {
            return $this->data[ $key ];
        }
        return null;
    }

    // check is key has a value
    public function has( string $key ) : bool {
        if( $key == 'error' and !empty( $this->error ) ) {
            return true;
        } elseif( !empty( $this->data[ $key ] ) ) {
            return true;
        }
        return false;
    }

    // clear error and data
    public function clear() {
        $this->error = '';
        $this->data  = [
            'id'          => 0,
            'date'        => '',
            'user_status' => '',
            'user_token'  => '',
            'user_email'  => '',
            'user_pass'   => '',
            'user_hash'   => ''
        ];
    }

    // generate unique random token
    private function create_token() {

        do {
            $user_token = bin2hex( random_bytes( 40 ));
            if( $this->is_exists( [['user_token', '=', $user_token]] )) {
                $repeat = true;
            } else {
                $repeat = false;
            }
        } while( $repeat );

        $this->data['user_token'] = $user_token;
    }

    // generate random password
    private function create_pass() {

        $user_pass = '';
        for( $i = 0; $i < 8; $i++ ) {
            $user_pass .= mt_rand( 0,9 );
        }

        $this->data['user_pass'] = $user_pass;
    }

    // return a hash
    private function create_hash() {

        $this->data['user_hash'] = sha1( $this->data['user_pass'] );
    }

    // check that the user exists
    private function is_exists( array $args ) : bool {
            
            $user = $this->db
            ->table('users')
            ->select('id');

            foreach( $args as $where ) {
                $user = $user->where( $where[0], $where[1], $where[2] );
            }

            $user = $user->first();

        return empty( $user->id ) ? false : true;
    }

    // create a new user by user_email
    public function insert() : bool {

        $user_email = $this->data[ 'user_email' ];

        if( empty( $user_email )) {
            $this->error = 'user_email is empty';

        } elseif( !is_string( $user_email ) ) {
            $this->error = 'user_email is not a string';

        } elseif( mb_strlen( $user_email, 'utf-8' ) > 255 ) {
            $this->error = 'user_email is too long';

        } elseif( !preg_match("/^[a-z0-9._-]{1,80}@(([a-z0-9-]+\.)+(com|net|org|mil|"."edu|gov|arpa|info|biz|inc|name|[a-z]{2})|[0-9]{1,3}\.[0-9]{1,3}\.[0-"."9]{1,3}\.[0-9]{1,3})$/", $user_email )) {
            $this->error = 'user_email is incorrect';

        } elseif( $this->is_exists( [['user_email', '=', $user_email]] )) {
            $this->error = 'user_email is occupied';

        } else {

            $this->create_token();

            $this->data['id'] = $this->db
            ->table('users')
            ->insertGetId([
                'date'        => $this->db::raw('now()'),
                'user_status' => 'pending',
                'user_token'  => $this->data['user_token'],
                'user_email'  => $this->data['user_email'],
                'user_hash'   => ''
            ]);

            if( empty( $this->data['id'] )) {
                $this->error = 'user insertion error';
            }
        }

        return empty( $this->error ) ? true : false;
    }

    // restore user_pass by user_email
    public function restore() : bool {

        $user_email = $this->data['user_email'];

        if( empty( $user_email )) {
            $this->error = 'user_email is empty';

        } elseif( !is_string( $user_email ) ) {
            $this->error = 'user_email is not a string';

        } elseif( mb_strlen( $user_email, 'utf-8' ) > 255 ) {
            $this->error = 'user_email is too long';

        } elseif( !preg_match("/^[a-z0-9._-]{1,80}@(([a-z0-9-]+\.)+(com|net|org|mil|"."edu|gov|arpa|info|biz|inc|name|[a-z]{2})|[0-9]{1,3}\.[0-9]{1,3}\.[0-"."9]{1,3}\.[0-9]{1,3})$/", $user_email )) {
            $this->error = 'user_email is incorrect';

        } elseif( !$this->is_exists( [['user_email', '=', $user_email], ['user_status', '<>', 'trash']] )) {
            $this->error = 'user_email is not available';

        } else {

            $this->create_pass();
            $this->create_hash();

            $affected = $this->db
            ->table('users')
            ->where([ ['user_email', '=', $user_email] ])
            ->update(['user_hash' => $this->data['user_hash']]);
                
            if( $affected == 0 ) {
                $this->error = 'user restore error';
            }
        }

        return empty( $this->error ) ? true : false;
    }

    // signin by user_email and user_pass
    public function signin() : bool {

        $user_email = $this->data['user_email'];
        $user_pass = $this->data['user_pass'];

        if( empty( $user_email )) {
            $this->error = 'user_email is empty';

        } elseif( !is_string( $user_email ) ) {
            $this->error = 'user_email is not a string';

        } elseif( mb_strlen( $user_email, 'utf-8' ) > 255 ) {
            $this->error = 'user_email is is too long';

        } elseif( !preg_match("/^[a-z0-9._-]{1,80}@(([a-z0-9-]+\.)+(com|net|org|mil|"."edu|gov|arpa|info|biz|inc|name|[a-z]{2})|[0-9]{1,3}\.[0-9]{1,3}\.[0-"."9]{1,3}\.[0-9]{1,3})$/", $user_email )) {
            $this->error = 'user_email is incorrect';

        } elseif( empty( $user_pass )) {
            $this->error = 'user_pass is empty';

        } elseif( !is_string( $user_pass ) ) {
            $this->error = 'user_pass is not a string';

        } else {
            $this->create_hash();
            $user_hash = $this->data['user_hash'];

            $user = $this->db
            ->table('users')
            ->select(['*'])
            ->where([ ['user_email', '=', $user_email], ['user_hash', '=', $user_hash], ['user_status', '<>', 'trash'] ])
            ->first();

            if( empty( $user->id )) {
                $this->error = 'user is not available';

            } else {
                $update_fields = [ 'user_hash' => '' ];

                if( $user->user_status == 'pending' ) {
                    $update_fields['user_status'] = 'approved';
                }

                $affected_rows = $this->db
                ->table('users')
                ->where([[ 'id', '=', $user->id ]])
                ->update( $update_fields );

                if( $affected_rows == 0 ) {
                    $this->error = 'user update error';

                } else {
                    $this->data['id'] = $user->id;
                    $this->data['date'] = $user->date;
                    $this->data['user_status'] = 'approved';
                    $this->data['user_token'] = $user->user_token;
                    $this->data['user_email'] = $user->user_email;
                    $this->data['user_hash'] = $user->user_hash;
                }
            }
        }

        return empty( $this->error ) ? true : false;
    }

    // TODO

    // get user_id by user_token
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

            if( isset( $user->id )) {
                $this->data = (array) $user;
            } else {
                $this->error = 'user selection error';
            }
        }

        return empty( $this->error ) ? true : false;
    }

    // logout
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

    // select user by id
    public function select( int $user_id ) : bool {
  
        $this->data();

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
                $this->data = (array) $user;

            } else {
                $this->error = 'user not found';
            }
        }

        return empty( $user->data['id'] ) ? false : true;
    }

    // update user email (also token and status)
    public function update( int $user_id, string $user_email ) : bool {

        $this->clear();

        if( empty( $user_id )) {
            $this->error = 'user_id is empty';

        } elseif( strlen( strval( $user_id )) > 20 ) {
            $this->error = 'user_id is too long';

        } elseif( !$this->is_exists( [[ 'id', '=', $user_id ]] )) {
            $this->error = 'user_id not found';

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
            ->where([ ['id', '=', $user_id] ])
            ->update([ 
                'user_status' => 'pending', 
                'user_email' => $user_email, 
                'user_token' => $this->create_token() 
                ]);
        }

        return empty( $result ) ? false : true;
    }

    // trash the user
    public function delete( string $user_id ) : bool {

        $this->clear();

        if( empty( $user_id )) {
            $this->error = 'user_id is empty';

        } elseif( strlen( strval( $user_id )) > 20 ) {
            $this->error = 'user_id is too long';

        } elseif( !$this->is_exists( [[ 'id', '=', $user_id ]] )) {
            $this->error = 'user_id not found';

        } elseif( !$this->is_exists( [[ 'user_status', '<>', 'trash' ]] )) {
            $this->error = 'user_id deleted';

        } else {

            $result = $this->db
            ->table('users')
            ->where([ ['id', '=', $user_id] ])
            ->update([ 'user_status' => 'trash' ]);
        }

        return empty( $result ) ? false : true;
    }

}