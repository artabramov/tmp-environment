<?php

namespace artabramov\Echidna;

class User
{
    private $db;
    private $data;

    // create the object
    public function __construct( \Illuminate\Database\Capsule\Manager $db ) {

        $this->db    = $db;
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
        if( array_key_exists( $key, $this->data ) ) {
            return $this->data[ $key ];
        }
        return null;
    }

    // check data is not empty
    public function has( string $key ) : bool {
        if( $key == 'error' and !empty( $this->error ) ) {
            return true;
        } elseif( !empty( $this->data[ $key ] ) ) {
            return true;
        }
        return false;
    }

    // clear data and error
    public function clear() : bool {
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
        return true;
    }

    // data validation
    public function is_correct( string $key ) : bool {

        if ( $key == 'id' and is_int( $this->data['id'] ) and $this->data['id'] > 0 and ceil( log10( $this->data['id'] )) <= 20 ) {
            return true;

        } elseif ( $key == 'user_status' and in_array( $this->data['user_status'], [ 'pending', 'approved', 'trash' ] )) {
            return true;

        } elseif ( $key == 'user_token' and is_string( $this->data['user_token'] ) and mb_strlen( $this->data['user_token'], 'utf-8' ) == 80 ) {
            return true;

        } elseif ( $key == 'user_email' and is_string( $this->data['user_email'] ) and mb_strlen( $this->data['user_email'], 'utf-8' ) < 256 and preg_match("/^[a-z0-9._-]{1,80}@(([a-z0-9-]+\.)+(com|net|org|mil|"."edu|gov|arpa|info|biz|inc|name|[a-z]{2})|[0-9]{1,3}\.[0-9]{1,3}\.[0-"."9]{1,3}\.[0-9]{1,3})$/", $this->data['user_email'] ) ) {
            return true;

        } elseif ( $key == 'user_pass' and is_string( $this->data['user_pass'] ) and mb_strlen( $this->data['user_pass'], 'utf-8' ) >= 4 ) {
            return true;

        } elseif ( $key == 'user_hash' and is_string( $this->data['user_hash'] ) and mb_strlen( $this->data['user_hash'], 'utf-8' ) == 40 ) {
            return true;
        }

        return false;
    }


    // check that the user exists
    public function is_exists( array $args ) : bool {
        $user = $this->db
        ->table('users')
        ->select('id');
        foreach( $args as $where ) {
            $user = $user->where( $where[0], $where[1], $where[2] );
        }
        $user = $user->first();
        return empty( $user->id ) ? false : true;
    }


    // generate unique random token
    public function create_token() : bool {
        do {
            $user_token = bin2hex( random_bytes( 40 ));
            if( $this->is_exists( [['user_token', '=', $user_token]] )) {
                $repeat = true;
            } else {
                $repeat = false;
            }
        } while( $repeat );
        $this->data['user_token'] = $user_token;
        return true;
    }


    // generate random password
    public function create_pass() : bool {
        $user_pass = '';
        for( $i = 0; $i < 8; $i++ ) {
            $user_pass .= mt_rand( 0,9 );
        }
        $this->data['user_pass'] = $user_pass;
        return true;
    }


    // return a hash
    public function create_hash() : bool {
        $this->data['user_hash'] = sha1( $this->data['user_pass'] );
        return true;
    }


    // create a new user by user_email
    public function insert() : bool {

        $this->create_token();
        
        $this->data['id'] = $this->db
        ->table('users')
        ->insertGetId([
            'date'        => $this->db::raw('now()'),
            'user_status' => 'pending',
            'user_token'  => $this->data['user_token'],
            'user_email'  => strtolower( $this->data['user_email'] ),
            'user_hash'   => ''
        ]);

        return empty( $this->data['id'] ) ? false : true;
    }


    // restore user_pass by user_email
    public function restore() : bool {

        $this->create_pass();
        $this->create_hash();

        $affected_rows = $this->db
        ->table('users')
        ->where([[ 'user_email', '=', $this->data['user_email'] ]])
        ->update(['user_hash' => $this->data['user_hash']]);

        return $affected_rows > 0 ? true : false;
    }


    // signin by user_email
    public function signin() : bool {

        $affected_rows = $this->db
        ->table('users')
        ->where([ ['user_email', '=', $this->data['user_email']], ['user_hash', '=', $this->data['user_hash']] ])
        ->update([ 'user_status' => 'approved', 'user_hash' => '' ]);

        if( $affected_rows > 0 ) {
            $user = $this->db
            ->table('users')
            ->select(['*'])
            ->where([ 'user_email', '=', $this->data['user_email'] ])
            ->first();

            if( !empty( $user->id )) {
                $this->data['id'] = $user->id;
                $this->data['date'] = $user->date;
                $this->data['user_status'] = $user->user_status;
                $this->data['user_token'] = $user->user_token;
                $this->data['user_email'] = $user->user_email;
                $this->data['user_hash'] = $user->user_hash;
            }
        }

        return $affected_rows > 0 and !empty( $user->id ) ? true : false;
    }

    // get user_id by user_token
    public function auth() : bool {

        $user_token = (string) $this->data['user_token'];

        if( empty( $user_token )) {
            $this->error = 'user_token is empty';

        } elseif( mb_strlen( $user_token, 'utf-8' ) < 80 ) {
            $this->error = 'user_token is too short';

        } elseif( mb_strlen( $user_token, 'utf-8' ) > 80 ) {
            $this->error = 'user_token is too long';

        } else {
            
            $user = $this->db
            ->table('users')
            ->select('*')
            ->where([ ['user_token', '=', $user_token], ['user_status', '=', 'approved'] ])
            ->first();

            if( empty( $user->id )) {
                $this->error = 'user is not available';

            } else {
                $this->data['id'] = $user->id;
                $this->data['date'] = $user->date;
                $this->data['user_status'] = 'approved';
                $this->data['user_token'] = $user->user_token;
                $this->data['user_email'] = $user->user_email;
                $this->data['user_hash'] = $user->user_hash;
            }
        }

        return empty( $this->error ) ? true : false;
    }

    // update user_email (also token and status) by user_id
    public function update() : bool {

        $user_id = (int) $this->data['id'];
        $user_email = (string) strtolower( $this->data['user_email'] );

        if( empty( $user_id )) {
            $this->error = 'user_id is empty';

        } elseif( strlen( strval( $user_id )) > 20 ) {
            $this->error = 'user_id is too long';

        } elseif( empty( $user_email )) {
            $this->error = 'user_email is empty';

        } elseif( mb_strlen( $user_email, 'utf-8' ) > 255 ) {
            $this->error = 'user_email is too long';

        } elseif( !preg_match("/^[a-z0-9._-]{1,80}@(([a-z0-9-]+\.)+(com|net|org|mil|"."edu|gov|arpa|info|biz|inc|name|[a-z]{2})|[0-9]{1,3}\.[0-9]{1,3}\.[0-"."9]{1,3}\.[0-9]{1,3})$/", $user_email )) {
            $this->error = 'user_email is incorrect';

        } elseif( $this->is_exists( [['user_email', '=', $user_email]] )) {
            $this->error = 'user_email is occupied';

        } else {

            $this->create_token();

            $affected_rows = $this->db
            ->table('users')
            ->where([ ['id', '=', $user_id] ])
            ->update([
                'user_status' => 'pending',
                'user_email' => $this->data['user_email'],
                'user_token' => $this->data['user_token']]);
                
            if( $affected_rows == 0 ) {
                $this->error = 'user update error';
            }
        }

        return empty( $this->error ) ? true : false;
    }

    // signout by user_id
    public function signout() : bool {

        $user_id = (int) $this->data['id'];

        if( empty( $user_id )) {
            $this->error = 'user_id is empty';

        } elseif( strlen( strval( $user_id )) > 20 ) {
            $this->error = 'user_id is too long';

        } else {

            $this->create_token();

            $affected = $this->db
            ->table('users')
            ->where([ ['id', '=', $user_id] ])
            ->update(['user_token' => $this->data['user_token']]);
                
            if( $affected == 0 ) {
                $this->error = 'user update error';
            }
        }

        return empty( $this->error ) ? true : false;
    }

    // select user by user_id
    public function select() : bool {
  
        $user_id = (int) $this->data['id'];

        if( empty( $user_id )) {
            $this->error = 'user_id is empty';

        } elseif( strlen( strval( $user_id )) > 20 ) {
            $this->error = 'user_id is too long';

        } else {

            $user = $this->db
            ->table('users')
            ->select(['*'])
            ->where([ ['id', '=', $user_id] ])
            ->first();

            if( empty( $user->id )) {
                $this->error = 'user is not available';

            } else {
                $this->data['id'] = $user->id;
                $this->data['date'] = $user->date;
                $this->data['user_status'] = 'approved';
                $this->data['user_token'] = $user->user_token;
                $this->data['user_email'] = $user->user_email;
                $this->data['user_hash'] = $user->user_hash;
            }
        }

        return empty( $this->error ) ? true : false;
    }

    // trash the user by user_id
    public function delete() : bool {

        $user_id = (int) $this->data['id'];

        if( empty( $user_id )) {
            $this->error = 'user_id is empty';

        } elseif( strlen( strval( $user_id )) > 20 ) {
            $this->error = 'user_id is too long';

        } else {

            $affected = $this->db
            ->table('users')
            ->where([ ['id', '=', $user_id] ])
            ->update([
                'user_status' => 'trash']);
                
            if( $affected == 0 ) {
                $this->error = 'user delete error';
            }
        }

        return empty( $this->error ) ? true : false;
    }

}