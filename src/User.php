<?php

namespace artabramov\Echidna;

class User
{
    private $dbh;
    private $error;
    private $data;

    // __construct
    public function __construct( \PDO $dbh ) {
        $this->dbh = $dbh;
        $this->clear();
    }

    // __get
    public function __get( string $key ) {
        return isset( $this->$key ) ? $this->$key : null;
    }

    // __set
    public function __set( string $key, $value ) {}

    // is empty
    private function is_empty( string $key, int|string $value ) : bool {

        $this->clear();

        if( is_string( $value )) {
            $value = trim( $value );
        }

        if( empty( $value )) {
            $this->error = $key . ' is empty';
        }

        return empty( $this->error ) ? false : true;
    }

    // is correct
    private function is_correct( string $key, int|string $value ) : bool {

        $this->clear();

        if( !array_key_exists( $key, $this->data )) {
            $this->error = 'key is incorrect';

        } elseif( $key == 'id' and !is_int( $value )) {
            $this->error = 'id is incorrect';

        } elseif( $key == 'user_status' and !in_array( $value, ['pending', 'approved', 'trash']) ) {
            $this->error = 'user_status is incorrect';

        } elseif( $key == 'user_email' and ( !is_string( $value ) or !preg_match("/^[a-z0-9._-]{2,80}@(([a-z0-9-]+\.)+(com|net|org|mil|"."edu|gov|arpa|info|biz|inc|name|[a-z]{2})|[0-9]{1,3}\.[0-9]{1,3}\.[0-"."9]{1,3}\.[0-9]{1,3})$/", $value ))) {
            $this->error = 'user_email is incorrect';
        }

        return empty( $this->error ) ? true : false;
    }

    // is exists
    private function is_exists( array $args ) : bool {

        $this->clear();

        $user = $this->dbh
            ->table('users')
            ->select('id')
            ->where( $args )
            ->first();

        if( empty( $user->id )) {
            $this->error = 'user does not exist';
        }

        return empty( $this->error ) ? true : false;
    }

    // is insert
    private function is_insert( array $data ) : bool {
        
        $this->clear();

        $user_id = $this->dbh
            ->table('users')
            ->insertGetId( $data );

        if( !empty( $user_id )) {
            foreach( $data as $key=>$value ) {
                $this->data[$key] = $value;
            }
            $this->data['id'] = $user_id;            

        } else {
            $this->error = 'user insert error';
        }

        return empty( $this->error ) ? true : false;
    }

    // is update
    private function is_update( array $where, array $update ) : bool {

        $this->clear();

        $affected_rows = $this->dbh
            ->table('users')
            ->where( $where )
            ->update( $update );

        if( is_int( $affected_rows ) ) {
            foreach( $where as $value ) {
                $this->data[$value[0]] = $value[2];
            }

            foreach( $update as $key=>$value ) {
                $this->data[$key] = $value;
            }

        } else {
            $this->error = 'user update error';
        }

        return empty( $this->error ) ? true : false;
    }

    // is select
    private function is_select( array $where ) : bool {
  
        $this->clear();

        $user = $this->dbh
            ->table( 'users' )
            ->where( $where )
            ->select( '*' )
            ->first();

        if( !empty( $user->id )) {
            $this->id          = $user->id;
            $this->date        = $user->date;
            $this->user_status = $user->user_status;
            $this->user_token  = $user->user_token;
            $this->user_email  = $user->user_email;
            $this->user_hash   = $user->user_hash;
            $this->hash_date   = $user->hash_date;

        } else {
            $this->error = 'user select error';
        }

        return empty( $this->error ) ? true : false;
    }

    // get time
    private function get_time() : string {
        $time = $this->dbh::select( 'select NOW() as time' );

        if( isset( $time[0]->time )) {
            return $time[0]->time;
        }

        return '';
    }

    // get token
    private function get_token() : string {

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

    // get user pass
    private function get_pass( $length, $signs = '0123456789' ) : string {

        $user_pass = '';
        $signs_len = mb_strlen( $signs, 'utf-8' ) - 1;

        for( $i = 0; $i < $length; $i++ ) {
            $user_pass .= $signs[ random_int( 0, $signs_len ) ];
        }

        return $user_pass;
    }

    // get hash
    private function get_hash( $user_pass ) : string {
        return sha1( $user_pass );
    }



    // clear all user data
    public function clear() {
        $this->error = '';
        $this->data = [
            'id'          => 0,
            'date'        => '0000-00-00 00:00:00',
            'user_status' => '',
            'user_token'  => '',
            'user_email'  => '',
            'user_pass'   => '',
            'user_hash'   => '',
            'hash_date'   => '0000-00-00 00:00:00'
        ];
    }

    // is error exists
    public function fails() {
        return !empty( $this->error );
    }

    // is user attribute not empty *
    public function filled( string $key ) : bool {
        return !empty( $this->data[$key] );
    }

    // user register *
    public function register( string $user_email ) : bool {

        if( $this->is_empty( 'user_email', $user_email )) {
            return false;

        } elseif( !$this->is_correct( 'user_email', $user_email )) {
            return false;
        
        } elseif( $this->is_exists( [['user_email', '=', $user_email]] )) {
            return false;
        
        } else {

            $data = [
                'user_status' => 'pending',
                'user_token'  => $this->get_token(),
                'user_email'  => $user_email,
                'user_hash'   => '',
                'hash_date'   => '0000-00-00 00:00:00' ];

            if( !$this->insert( $data ) ) {
                return false;
            }
        }

        return true;
    }












    // user restore *
    public function restore( string $user_email, int $pass_length = 4, int $restore_delay = 30 ) : bool {

        $this->error = '';
        $this->clear();
        
        if( !$this->is_correct( 'user_email', $user_email )) {
            $this->error = 'user_email is incorrect';
        
        } elseif( !$this->select( [['user_email', '=', $user_email], ['user_status', '<>', 'trash']] )) {
            $this->error = 'user not found';

        } elseif( strtotime( $this->time() ) - strtotime( $this->hash_date ) < $restore_delay ) {
            $this->error = 'restore delay is too long';

        } else {

            $this->user_pass = $this->pass_create( $pass_length );

            $where = [['user_email', '=', $user_email]];

            $update = [
                'user_hash' => $this->hash_create( $this->user_pass ),
                'hash_date' => $this->dbh::raw('now()') ];

            if( !$this->update( $where, $update ) ) {
                $this->error = 'user update error';
            }
        }

        if( $this->is_error() ) {
            $this->clear();
        }

        return $this->is_error() ? false : true;
    }
    
    // user signin *
    public function signin( string $user_email, string $user_pass, int $pass_expires = 120 ) : bool {

        $this->error = '';
        $this->clear();

        $user_hash  = $this->hash_create( $user_pass );
        
        if( !$this->is_correct( 'user_email', $user_email )) {
            $this->error = 'user_email is incorrect';
    
        } elseif( !$this->is_correct( 'user_pass', $user_pass )) {
            $this->error = 'user_pass is incorrect';
        
        } elseif( !$this->select( [['user_email', '=', $user_email], ['user_hash', '=', $user_hash], ['user_status', '<>', 'trash']] )) {
            $this->error = 'user not found';
        
        } elseif( strtotime( $this->time() ) - strtotime( $this->hash_date ) > $pass_expires ) {
            $this->error = 'user_pass is expired';

        } else {

            $where = [['user_email', '=', $user_email]];

            $update = [
                'user_status' => 'approved',
                'user_hash'   => '',
                'hash_date'   => '0000-00-00 00:00:00'];

            if( !$this->update( $where, $update ) ) {
                $this->error = 'user update error';
            }
        }

        if( $this->is_error() ) {
            $this->clear();
        }

        return $this->is_error() ? false : true;
    }

    // user auth *
    public function auth( string $user_token ) : bool {

        $this->error = '';
        $this->clear();
        
        $this->user_token = $user_token;
        
        if( !$this->is_correct( 'user_token', $user_token )) {
            $this->error = 'user_token is incorrect';
        
        } elseif( !$this->select( [[ 'user_token', '=', $user_token ], ['user_status', '=', 'approved']] )) {
            $this->error = 'user not found';
        }

        if( $this->is_error() ) { 
            $this->clear();
        }

        return $this->is_error() ? false : true;
    }

    // user change *
    public function change( int $user_id, string $user_email ) : bool {

        $this->error = '';
        $this->clear();
        
        if( !$this->is_correct( 'id', $user_id )) {
            $this->error = 'user_id is incorrect';

        } elseif( !$this->is_correct( 'user_email', $user_email )) {
            $this->error = 'user_email is incorrect';

        } elseif( !$this->is_exists( [['id', '=', $user_id], ['user_status', '=', 'approved']] )) {
            $this->error = 'user not found';
        
        } elseif( $this->is_exists( [['user_email', '=', $user_email]] )) {
            $this->error = 'user_email is occupied';

        } else {

            $where = [['id', '=', $user_id]];

            $update = [
                'user_status' => 'pending',
                'user_token'  => $this->token_create(),
                'user_pass'   => '',
                'user_hash'   => '',
                'hash_date'   => '0000-00-00 00:00:00' ];

            if( !$this->update( $where, $update )) {
                $this->error = 'user update error';
            }
        }

        if( $this->is_error() ) { 
            $this->clear();
        }

        return $this->is_error() ? false : true;
    }

    // user signout *
    public function signout( int $user_id ) : bool {

        $this->error = '';
        $this->clear();
        
        if( !$this->is_correct( 'id', $user_id )) {
            $this->error = 'user_id is incorrect';

        } elseif( !$this->is_exists( [['id', '=', $user_id], ['user_status', '=', 'approved']] )) {
            $this->error = 'user_id not found';

        } else {
            $where = [['id', '=', $user_id]];

            $update = [ 'user_token' => $this->token_create() ];

            if( !$this->update( $where, $update ) ) {
                $this->error = 'user update error';
            }
        }

        if( $this->is_error() ) { 
            $this->clear();
        }

        return $this->is_error() ? false : true;
    }

    // get user *
    public function get( int $user_id, string $user_status ) : bool {

        $this->error = '';
        $this->clear();
        
        if( !$this->is_correct( 'id', $user_id )) {
            $this->error = 'user_id is incorrect';

        } elseif( !$this->select( [['id', '=', $user_id]] ) ) {
            $this->error = 'user not found';
        }

        if( $this->is_error() ) { 
            $this->clear();
        }

        return $this->is_error() ? false : true;
    }
}
