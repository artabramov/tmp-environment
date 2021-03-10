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
    private $user_pass;
    private $user_hash;
    private $hash_date;

    public function __construct( \Illuminate\Database\Capsule\Manager $db ) {
        $this->db = $db;

        $this->error = '';
        $this->clear();
    }

    public function __set( string $key, $value ) {
    }

    public function __get( string $key ) {
        return isset( $this->$key ) ? $this->$key : null;
    }

    // clear data
    private function clear() {

        $this->id          = 0;
        $this->date        = '0000-00-00 00:00:00';
        $this->user_status = '';
        $this->user_token  = '';
        $this->user_email  = '';
        $this->user_pass   = '';
        $this->user_hash   = '';
        $this->hash_date   = '0000-00-00 00:00:00';
    }

    // is error exists *
    public function is_error() {
        return !empty( $this->error );
    }

    // is correct
    private function is_correct( string $key, $value ) : bool {

        if ( $key == 'id' and !empty( $value ) and is_int( $value ) ) {
            return true;

        } elseif ( $key == 'user_status' and in_array( $value, ['pending', 'approved', 'trash']) ) {
            return true;

        } elseif ( $key == 'user_token' and is_string( $value ) and mb_strlen( $value, 'utf-8' ) == 80 ) {
            return true;

        } elseif ( $key == 'user_email' and is_string( $value ) and mb_strlen( $value, 'utf-8' ) <= 255 and preg_match("/^[a-z0-9._-]{1,80}@(([a-z0-9-]+\.)+(com|net|org|mil|"."edu|gov|arpa|info|biz|inc|name|[a-z]{2})|[0-9]{1,3}\.[0-9]{1,3}\.[0-"."9]{1,3}\.[0-9]{1,3})$/", $value ) ) {
            return true;

        } elseif ( $key == 'user_pass' and !empty( $value ) and is_string( $value ) ) {
            return true;

        } elseif ( $key == 'user_hash' and is_string( $value ) and mb_strlen( $value, 'utf-8' ) == 40 ) {
            return true;
        }

        return false;
    }

    // is exists
    private function is_exists( array $args ) : bool {

        $role = $this->db
            ->table('users')
            ->select('id')
            ->where( $args )
            ->first();

        return empty( $role->id ) ? false : true;
    }

    // create token
    private function token_create() : string {

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

    // generate password
    private function pass_create( int $pass_length ) : string {

        $user_pass = '';
        for( $i = 0; $i < $pass_length; $i++ ) {
            $user_pass .= mt_rand( 0,9 );
        }

        return $user_pass;
    }

    // get hash
    private function hash_create( $user_pass ) : string {
        return sha1( $this->user_pass );
    }

    // get db time
    private function time() : string {
        $time = $this->db::select( 'select NOW() as time' );
        return $time[0]->time;
    }

    // insert user
    private function insert( array $data ) : bool {
        
        $data['date'] = $this->db::raw('now()');

        $user_id = $this->db
            ->table('users')
            ->insertGetId( $data );

        if( !empty( $user_id ) ) {
            $this->id = $user_id;

            foreach( $data as $key=>$value ) {
                $this->$key = $value;
            }
        }

        return empty( $user_id ) ? false : true;
    }

    // user update
    private function update( array $where, array $update ) : bool {

        $affected_rows = $this->db
            ->table('users')
            ->where( $where )
            ->update( $update );

        if( is_int( $affected_rows ) ) {
            foreach( $where as $value ) {
                $key = $value[0];
                $this->$key = $value[2];
            }

            foreach( $update as $key=>$value ) {
                $this->$key = $value;
            }
        }

        return is_int( $affected_rows ) ? true : false;
    }

    // select user
    private function select( array $where ) : bool {
  
        $user = $this->db
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
        }

        return empty( $user->id ) ? false : true;
    }

    // user register *
    public function register( string $user_email ) : bool {

        $this->error = '';
        $this->clear();
        
        if( !$this->is_correct( 'user_email', $user_email )) {
            $this->error = 'user_email is incorrect';
        
        } elseif( $this->is_exists( [['user_email', '=', $user_email]] )) {
            $this->error = 'user_email is exists';
        
        } else {

            $data = [
                'user_status' => 'pending',
                'user_token'  => $this->token_create(),
                'user_email'  => $user_email,
                'user_hash'   => '',
                'hash_date'   => '0000-00-00 00:00:00' ];

            if( !$this->insert( $data ) ) {
                $this->error = 'user insert error';
            }
        }

        if( $this->is_error() ) {
            $this->clear();
        }

        return $this->is_error() ? false : true;
    }

    // user restore *
    public function restore( string $user_email, int $pass_length = 4, int $restore_delay = 30 ) : bool {

        $this->error = '';
        $this->clear();
        
        if( !$this->is_correct( 'user_email', $user_email )) {
            $this->error = 'user_email is incorrect';
        
        } elseif( !$this->is_exists( [['user_email', '=', $this->user_email], ['user_status', '<>', 'trash']] )) {
	        $this->error = 'user_email is not exists or trashed';

        } else {

            if( !$this->select( [['user_email', '=', $user_email]] )) {
                $this->error = 'user select error';

            } elseif( strtotime( $this->time() ) - strtotime( $this->hash_date ) < $restore_delay ) {
                $this->error = 'restore delay is too long';

            } else {

                $this->user_pass = $this->pass_create( $pass_length );

                $where = [['user_email', '=', $user_email]];

                $update = [
                    'user_hash' => $this->hash_create( $this->user_pass ),
                    'hash_date' => $this->db::raw('now()') ];

                if( !$this->update( $where, $update ) ) {
                    $this->error = 'user update error';
                }
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

        } elseif( !$this->is_exists( [['user_email', '=', $user_email], ['user_hash', '=', $user_hash], ['user_status', '<>', 'trash']] )) {
            $this->error = 'user_pass is wrong or user is not exists or trashed';
        
        } elseif( !$this->select( 'user_email', $user_email )) {
            $this->error = 'user select error';
        
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
        
        } elseif( !$this->is_exists( [['user_token', '=', $user_token], ['user_status', '=', 'approved']] )) {
            $this->error = 'user not found';
        
        } elseif( !$this->select( 'user_token', $user_token )) {
            $this->error = 'user select error';
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

        } elseif( !$this->is_exists( [['id', '=', $user_id], ['user_status', '=', 'approved']] )) {
            $this->error = 'user_id not found';
        
        } elseif( !$this->is_correct( 'user_email', $user_email )) {
            $this->error = 'user_email is incorrect';
        
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
    public function get( int $user_id ) : bool {

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
