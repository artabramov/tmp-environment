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

    // construct *
    public function __construct( \Illuminate\Database\Capsule\Manager $db ) {
        $this->db = $db;

        $this->error = '';
        $this->clear();
    }

    // set *
    public function __set( string $key, $value ) {}

    // get *
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

    // is data empty
    private function is_empty( string $key ): bool {
        return empty( $this->$key );
    }

    // is data correct
    private function is_correct( string $key ) : bool {

        if ( $key == 'id' and is_int( $this->id ) and $this->id > 0 and ceil( log10( $this->id )) <= 20 ) {
            return true;

        } elseif ( $key == 'user_status' and in_array( $this->user_status, ['pending', 'approved', 'trash']) ) {
            return true;

        } elseif ( $key == 'user_token' and is_string( $this->user_token ) and mb_strlen( $this->user_token, 'utf-8' ) == 80 ) {
            return true;

        } elseif ( $key == 'user_email' and is_string( $this->user_email ) and mb_strlen( $this->user_email, 'utf-8' ) <= 255 and preg_match("/^[a-z0-9._-]{1,80}@(([a-z0-9-]+\.)+(com|net|org|mil|"."edu|gov|arpa|info|biz|inc|name|[a-z]{2})|[0-9]{1,3}\.[0-9]{1,3}\.[0-"."9]{1,3}\.[0-9]{1,3})$/", $this->user_email ) ) {
            return true;

        } elseif ( $key == 'user_pass' and is_string( $this->user_pass )) {
            return true;

        } elseif ( $key == 'user_hash' and is_string( $this->user_hash ) and mb_strlen( $this->user_hash, 'utf-8' ) == 40 ) {
            return true;
        }

        return false;
    }

    // is user exists
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

    // generate token
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
    private function insert() : bool {
        
        $this->id = $this->db
        ->table('users')
        ->insertGetId([
            'date'        => $this->db::raw('now()'),
            'user_status' => $this->user_status,
            'user_token'  => $this->user_token,
            'user_email'  => $this->user_email,
            'user_hash'   => $this->user_hash,
            'hash_date'   => $this->hash_date
        ]);

        return empty( $this->id ) ? false : true;
    }

    // select user
    private function select( string $key ) : bool {
  
        $user = $this->db
            ->table('users')
            ->select(['*'])
            ->where([ [ $key, '=', $this->$key ] ])
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

    // user update
    private function update( array $keys ) : bool {

        $data = [];
        foreach( $keys as $key ) {
            $data[ $key ] = $this->$key;
        }        
        
        $affected_rows = $this->db
            ->table('users')
            ->where([[ 'id', '=', $this->id ]])
            ->update( $data );

        return is_int( $affected_rows ) ? true : false;
    }

    // user register *
    public function register( string $user_email ) : bool {

        $this->error = '';
        $this->clear();

        $this->user_email = $user_email;

        if( $this->is_empty( 'user_email' )) {
            $this->error = 'user_email is empty';
        
        } elseif( !$this->is_correct( 'user_email' )) {
            $this->error = 'user_email is incorrect';
        
        } elseif( $this->is_exists( [['user_email', '=', $this->user_email]] )) {
            $this->error = 'user_email is exists';
        
        } else {
            $this->user_status = 'pending';
            $this->user_token  = $this->token_create();
            $this->user_pass   = '';
            $this->user_hash   = '';
            $this->hash_date   = '0000-00-00 00:00:00';

            if( !$this->insert() ) {
                $this->error = 'user insert error';
            }
        }

        if( $this->is_error() ) {
            $this->clear();
            return false;
        }

        return true;
    }

    // user restore *
    public function restore( string $user_email, int $pass_length = 6, int $restore_delay = 30 ) : bool {

        $this->error = '';
        $this->clear();

        $this->user_email = $user_email;

        if( $this->is_empty( 'user_email' )) {
            $this->error = 'user_email is empty';
        
        } elseif( !$this->is_correct( 'user_email' )) {
            $this->error = 'user_email is incorrect';
        
        } elseif( !$this->is_exists( [['user_email', '=', $this->user_email], ['user_status', '<>', 'trash']] )) {
	        $this->error = 'user_email is not exists';

        } else {

            if( !$this->select( 'user_email' )) {
                $this->error = 'user select error';

            } elseif( $restore_delay > 0 and strtotime($this->time()) - strtotime($this->hash_date) < $restore_delay ) {
                $this->error = 'user delay error';

            } else {

                $this->user_pass = $this->pass_create( $pass_length );
                $this->user_hash = $this->hash_create( $this->user_pass );
                $this->hash_date = $this->db::raw('now()');

                if( !$this->update(['user_hash', 'hash_date']) ) {
                    $this->error = 'user update error';
                }
            }
        }

        if( $this->is_error() ) {
            $this->clear();
            return false;
        }

        return true;
    }
    
    // user signin *
    public function signin( string $user_email, string $user_pass, int $pass_expires = 120 ) : bool {

        $this->error = '';
        $this->clear();

        $this->user_email = $user_email;
        $this->user_pass  = $user_pass;
        $this->user_hash  = $this->hash_create( $this->user_pass );

        if( $this->is_empty( 'user_email' )) {
            $this->error = 'user_email is empty';
        
        } elseif( !$this->is_correct( 'user_email' )) {
            $this->error = 'user_email is incorrect';

        } elseif( $this->is_empty( 'user_pass' )) {
            $this->error = 'user_pass is empty';
    
        } elseif( !$this->is_correct( 'user_pass' )) {
            $this->error = 'user_pass is incorrect';

        } elseif( !$this->is_exists( [['user_email', '=', $this->user_email], ['user_hash', '=', $this->user_hash], ['user_status', '<>', 'trash']] )) {
            $this->error = 'user_pass is wrong';
        
        } elseif( !$this->select( 'user_email' )) {
            $this->error = 'user select error';
        
        } elseif( strtotime( $this->time() ) - strtotime( $this->hash_date ) > $pass_expires ) {
            $this->error = 'user_pass is expired';

        } else {

            $this->user_status = 'approved';
            $this->user_hash   = '';
            $this->hash_date   = '0000-00-00 00:00:00';

            if( !$this->update(['user_status', 'user_hash', 'hash_date']) ) {
                $this->error = 'user update error';
            }
        }

        if( $this->is_error() ) {
            $this->clear();
            return false;
        }

        return true;
    }

    // user auth *
    public function auth( string $user_token ) : bool {

        $this->error = '';
        $this->clear();
        
        $this->user_token = $user_token;

        if( $this->is_empty( 'user_token' )) {
            $this->error = 'user_token is empty';
        
        } elseif( !$this->is_correct( 'user_token' )) {
            $this->error = 'user_token is incorrect';
        
        } elseif( !$this->is_exists( [['user_token', '=', $this->user_token], ['user_status', '=', 'approved']] )) {
            $this->error = 'user_token not found';
        
        } elseif( !$this->select( 'user_token' )) {
            $this->error = 'user_token select error';
        }

        if( $this->is_error() ) {
            $this->clear();
            return false;
        }

        return true;
    }

    // user change *
    public function change( int $user_id, string $user_email ) : bool {

        $this->error = '';
        $this->clear();
        
        $this->id         = $user_id;
        $this->user_email = $user_email;

        if( $this->is_empty( 'id' )) {
            $this->error = 'user_id is empty';
        
        } elseif( !$this->is_correct( 'id' )) {
            $this->error = 'user_id is incorrect';

        } elseif( $this->is_empty( 'user_email' )) {
            $this->error = 'user_email is empty';
        
        } elseif( !$this->is_correct( 'user_email' )) {
            $this->error = 'user_email is incorrect';
        
        } elseif( $this->is_exists( [['user_email', '=', $this->user_email]] )) {
            $this->error = 'user_email is exists';

        } else {

            $this->user_status = 'pending';
            $this->user_token  = $this->token_create();
            $this->user_pass   = '';
            $this->user_hash   = '';
            $this->hash_date   = '0000-00-00 00:00:00';

            if( !$this->update(['user_status', 'user_token', 'user_email', 'user_hash', 'hash_date']) ) {
                $this->error = 'user update error';
            }
        }

        if( $this->is_error() ) {
            $this->clear();
            return false;
        }

        return true;
    }

    // user signout *
    public function signout( int $user_id ) : bool {

        $this->error = '';
        $this->clear();
        
        $this->id = $user_id;

        if( $this->is_empty( 'id' )) {
            $this->error = 'user_id is empty';
        
        } elseif( !$this->is_correct( 'id' )) {
            $this->error = 'user_id is incorrect';

        } else {
            $this->user_token = $this->token_create();

            if( !$this->update(['user_token']) ) {
                $this->error = 'user update error';
            }
        }

        if( $this->is_error() ) {
            $this->clear();
            return false;
        }

        return true;
    }

    // get user *
    public function get( int $user_id ) : bool {

        $this->error = '';
        $this->clear();
        
        $this->id = $user_id;

        if( $this->is_empty( 'id' )) {
            $this->error = 'user_id is empty';
        
        } elseif( !$this->is_correct( 'id' )) {
            $this->error = 'user_id is incorrect';

        } elseif( !$this->is_exists( [['id', '=', $this->id], ['user_status', '=', 'approved']] )) {
            $this->error = 'user_id not found';

        } elseif( !$this->select( 'id' ) ) {
            $this->error = 'user select error';
        }

        if( $this->is_error() ) {
            $this->clear();
            return false;
        }

        return true;
    }
    

}
