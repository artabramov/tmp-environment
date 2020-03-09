<?php

namespace Artabramov\Echidna;

class User
{
    private $db = null;

    public function __construct($db) {
        $this->db = $db;
    }

    // Check user exist
    private function exist(array $args) {

        if(empty($args)) {
            return false;
        }

        $user = $this
            ->db
            ->table('users')
            ->select('id');

        foreach($args as $where) {
            $user = $user->where($where[0], $where[1], $where[2]);
        }

        $user = $user->first();

        if(!empty($user->id)) {
            return true;
        }

        return false;
    }

    // Generate unique token
    private function token() {

        do {
            $user_token = sha1(bin2hex(random_bytes(64)));

            if($this->exist([['user_token', '=', $user_token]])) {
                $repeat = true;

            } else {
                $repeat = false;
            }

        } while ($repeat);

        return $user_token;
    }

    // Generate pass
    private function pass() {

        $user_pass = '';
        for ($i = 0; $i < 6; $i++) {
            $user_pass .= mt_rand(0,9);
        }
        return $user_pass;
    }

    // Get hash
    private function hash($value) {
        return sha1($value);
    }

    // Insert a new user +
    public function insert(array $data) {

        $user_login = !empty($data['user_login']) ? strtolower(trim($data['user_login'])) : '';
        $user_email = !empty($data['user_email']) ? strtolower(trim($data['user_email'])) : '';
        $user_phone = !empty($data['user_phone']) ? trim($data['user_phone'])             : '';

        if(empty($user_login)) {
            $error = 'User login is incorrect. It can not be empty.';

        } elseif(!is_string($user_login) || mb_strlen($user_login, 'utf-8') < 4 || mb_strlen($user_login, 'utf-8') > 40 || !preg_match("/^[a-z0-9_]{1,40}$/", $user_login)) {
            $error = 'User login is incorrect. It must be a string from 4 to 40 symbols and contains lowercase latin symbols and underline only.';

        } elseif($this->exist([['user_login', '=', $user_login]])) {
            $error = 'User login is incorrect. It already exists.';

        } elseif(empty($user_email) && empty($user_phone)) {
            $error = 'User email or user phone are incorrect. It can not be empty both.';

        } elseif(!empty($user_email) && (!is_string($user_email) || mb_strlen($user_email, 'utf-8') > 255 || !preg_match("/^[a-z0-9._-]{1,80}@(([a-z0-9-]+\.)+(com|net|org|mil|"."edu|gov|arpa|info|biz|inc|name|[a-z]{2})|[0-9]{1,3}\.[0-9]{1,3}\.[0-"."9]{1,3}\.[0-9]{1,3})$/", $user_email))) {
            $error = 'User email is incorrect. It must be a string contains a local-part, an @ symbol, a domain.';

        } elseif(!empty($user_email) && $this->exist([['user_email', '=', $user_email]])) {
            $error = 'User email is incorrect. It already exists.';
        
        } elseif(!empty($user_phone) && (!ctype_digit($user_phone) || mb_strlen($user_phone, 'utf-8') < 4 || mb_strlen($user_phone, 'utf-8') > 20)) {
            $error = 'User phone is incorrect. It must be a string from 4 to 20 symbols and contains numbers only.';

        } elseif(!empty($user_phone) && $this->exist([['user_phone', '=', $user_phone]])) {
            $error = 'User phone is incorrect. It already exists.';

        } else {

            $user_id = $this->db->table('users')->insertGetId([
                'created_date'    => $this->db::raw('now()'),
                'user_status'     => 'pending',
                'user_login'      => $user_login,
                'user_hash'       => '',
                'user_hash_date'  => '0000-00-00 00:00:00',
                'user_token'      => $this->token(),
                'user_token_date' => $this->db::raw('now()'),
                'user_email'      => !empty($user_email) ? $user_email : '',
                'user_phone'      => !empty($user_phone) ? $user_phone : ''
            ]);
        }

        return [
            'error'   => !empty($error)   ? $error : '',
            'success' => !empty($user_id) ? true   : false
        ];
    }

    // Select any single user (by id, token, login, email or phone) +
    public function single(array $data) {

        $user_id    = !empty($data['user_id'])    ? intval(trim($data['user_id']))        : '';
        $user_token = !empty($data['user_token']) ? trim($data['user_token'])             : '';
        $user_login = !empty($data['user_login']) ? strtolower(trim($data['user_login'])) : '';
        $user_email = !empty($data['user_email']) ? strtolower(trim($data['user_email'])) : '';
        $user_phone = !empty($data['user_phone']) ? trim($data['user_phone'])             : '';

        if(empty($user_id) && empty($user_token) && empty($user_login) && empty($user_email) && empty($user_phone)) {
            $error = 'User data is incorrect. It can not be empty.';
    
        } elseif(!empty($user_id) && ((!is_int($user_id) && !ctype_digit($user_id)) || mb_strlen($user_id, 'utf-8') > 20)) {
            $error = 'User id is incorrect. It must be a number or string contains numbers only no longer than 20 symbols.';

        } elseif(!empty($user_token) && (!is_string($user_token) || mb_strlen($user_token, 'utf-8') > 80 || !preg_match("/^[a-f0-9]{1,80}$/", $user_token))) {
            $error = 'User token is incorrect. It must be a string no longer than 80 symbols and contains latin letters a-f and numbers only.';

        } elseif(!empty($user_login) && (!is_string($user_login) || mb_strlen($user_login, 'utf-8') < 4 || mb_strlen($user_login, 'utf-8') > 40 || !preg_match("/^[a-z0-9_]{1,40}$/", $user_login))) {
            $error = 'User login is incorrect. It must be a string from 4 to 40 symbols and contains lowercase latin symbols and underline only.';

        } elseif(!empty($user_email) && (!is_string($user_email) || mb_strlen($user_email, 'utf-8') > 255 || !preg_match("/^[a-z0-9._-]{1,80}@(([a-z0-9-]+\.)+(com|net|org|mil|"."edu|gov|arpa|info|biz|inc|name|[a-z]{2})|[0-9]{1,3}\.[0-9]{1,3}\.[0-"."9]{1,3}\.[0-9]{1,3})$/", $user_email))) {
            $error = 'User email is incorrect. It must be a string contains a local-part, an @ symbol, a domain.';

        } elseif(!empty($user_phone) && (!ctype_digit($user_phone) || mb_strlen($user_phone, 'utf-8') < 4 || mb_strlen($user_phone, 'utf-8') > 20)) {
            $error = 'User phone is incorrect. It must be a string from 4 to 20 symbols and contains numbers only.';
    
        } else {

            $user = $this->db
                ->table('users')
                ->select(['created_date', 'user_status', 'user_login']);

            if(!empty($user_id)) {
                    $user = $user->where('id', $user_id);

            } elseif(!empty($user_token)) {
                $user = $user->where('user_token', $user_token);

            } elseif(!empty($user_login)) {
                $user = $user->where('user_login', $user_login);

            } elseif(!empty($user_email)) {
                $user = $user->where('user_email', $user_email);

            } elseif(!empty($user_phone)) {
                $user = $user->where('user_phone', $user_phone);
            }

            $user = $user->first();
    
            if(empty($user)) {
                $error = 'User data is incorrect. It not found.';
            }
        }
            
        return [
            'error'   => !empty($error) ? $error : '',
            'success' => !empty($user)  ? true   : false,
            'user'    => !empty($user)  ? $user  : []
        ];
    }

    // Update approved user (by login / token needed) +
    public function update(array $data) {

        $self_token  = !empty($data['self_token'])  ? trim($data['self_token'])             : '';
        $user_login  = !empty($data['user_login'])  ? strtolower(trim($data['user_login'])) : '';
        $user_email  = !empty($data['user_email'])  ? strtolower(trim($data['user_email'])) : '';
        $user_phone  = !empty($data['user_phone'])  ? trim($data['user_phone'])             : '';

        if(empty($self_token)) {
            $error = 'Self token is incorrect. It can not be empty.';
    
        } elseif(!is_string($self_token) || mb_strlen($self_token, 'utf-8') > 80 || !preg_match("/^[a-f0-9]{1,80}$/", $self_token)) {
            $error = 'Self token is incorrect. It must be a string no longer than 80 symbols and contains latin letters a-f and numbers only.';

        } else {

            $self = $this->db
                ->table('users')
                ->select(['user_login', 'user_status', 'user_token_date'])
                ->selectRaw('now() as now')
                ->where('user_token', '=', $self_token)
                ->first();

            if(empty($self)) {
                $error = 'Self token is incorrect. Self user not found.';

            } elseif($self->user_status == 'trash') {
                $error = 'Self token is incorrect. Self user is trashed.';

            } elseif($self->user_status == 'pending') {
                $error = 'Self token is incorrect. Self user can not be pending.';

            } elseif(strtotime($self->now) - strtotime($self->user_token_date) > 86400 * 30) {
                $error = 'Self token is incorrect. It expired.';

            } elseif($self->user_login != $user_login && $self->user_status != 'admin') {
                $error = 'Self token is incorrect. Self user have no permissions to update this user.';

            } elseif(empty($user_login)) {
                $error = 'User login is incorrect. It can not be empty.';

            } elseif(!is_string($user_login) || mb_strlen($user_login, 'utf-8') < 4 || mb_strlen($user_login, 'utf-8') > 40 || !preg_match("/^[a-z0-9_]{1,40}$/", $user_login)) {
                $error = 'User login is incorrect. It must be a string from 4 to 40 symbols and contains lowercase latin symbols and underline only.';

            } else {

                $user = $this->db
                    ->table('users')
                    ->select(['user_status', 'user_email', 'user_phone'])
                    ->where('user_login', '=', $user_login)
                    ->first();

                if(empty($user)) {
                    $error = 'User login is incorrect. It not found.';

                } elseif(!empty($user_email) && (!is_string($user_email) || mb_strlen($user_email, 'utf-8') > 255 || !preg_match("/^[a-z0-9._-]{1,80}@(([a-z0-9-]+\.)+(com|net|org|mil|"."edu|gov|arpa|info|biz|inc|name|[a-z]{2})|[0-9]{1,3}\.[0-9]{1,3}\.[0-"."9]{1,3}\.[0-9]{1,3})$/", $user_email))) {
                    $error = 'User email is incorrect. It must be a string contains a local-part, an @ symbol, a domain.';
            
                } elseif(!empty($user_email) && $this->exist([['user_email', '=', $user_email]])) {
                    $error = 'User email is incorrect. It already exists.';
        
                } elseif(!empty($user_phone) && (!ctype_digit($user_phone) || mb_strlen($user_phone, 'utf-8') < 4 || mb_strlen($user_phone, 'utf-8') > 20)) {
                    $error = 'User phone is incorrect. It must be a string from 4 to 20 symbols and contains numbers only.';
            
                } elseif(!empty($user_phone) && $this->exist([['user_phone', '=', $user_phone]])) {
                    $error = 'User phone is incorrect. It already exists.';

                } else {

                    $result = $this->db
                        ->table('users')
                        ->where('user_login', $user_login)
                        ->limit(1)
                        ->update([
                            'user_token'      => $this->token(),
                            'user_token_date' => $this->db::raw('now()'),
                            'user_email'      => !empty($user_email)  ? $user_email  : $user->user_email,
                            'user_phone'      => !empty($user_phone)  ? $user_phone  : $user->user_phone
                        ]);
                }
            }
        }

        return [
            'error'   => !empty($error)  ? $error : '',
            'success' => !empty($result) ? true   : false
        ];
    }

    // Trash self approved user (by login / token needed) +
    public function trash(array $data) {

        $self_token = !empty($data['self_token']) ? trim($data['self_token'])             : '';
        $user_login = !empty($data['user_login']) ? strtolower(trim($data['user_login'])) : '';

        if(empty($self_token)) {
            $error = 'Self token is incorrect. It can not be empty.';
    
        } elseif(!is_string($self_token) || mb_strlen($self_token, 'utf-8') > 80 || !preg_match("/^[a-f0-9]{1,80}$/", $self_token)) {
            $error = 'Self token is incorrect. It must be a string no longer than 80 symbols and contains latin letters a-f and numbers only.';

        } else {

            $self = $this->db
                ->table('users')
                ->select(['user_login', 'user_status', 'user_token_date'])
                ->selectRaw('now() as now')
                ->where('user_token', '=', $self_token)
                ->first();

            if(empty($self)) {
                $error = 'Self token is incorrect. Self user not found.';

            } elseif($self->user_status == 'trash') {
                $error = 'Self token is incorrect. Self user is trashed.';

            } elseif($self->user_status == 'pending') {
                $error = 'Self token is incorrect. Self user can not be pending.';

            } elseif(strtotime($self->now) - strtotime($self->user_token_date) > 86400 * 30) {
                $error = 'Self token is incorrect. It expired.';

            } elseif($self->user_login != $user_login && $self->user_status != 'admin') {
                $error = 'Self token is incorrect. Self user have no permissions to update this user.';

            } elseif(empty($user_login)) {
                $error = 'User login is incorrect. It can not be empty.';

            } elseif(!is_string($user_login) || mb_strlen($user_login, 'utf-8') < 4 || mb_strlen($user_login, 'utf-8') > 40 || !preg_match("/^[a-z0-9_]{1,40}$/", $user_login)) {
                $error = 'User login is incorrect. It must be a string from 4 to 40 symbols and contains lowercase latin symbols and underline only.';

            } else {

                $user = $this->db
                    ->table('users')
                    ->select(['user_status', 'user_email', 'user_phone'])
                    ->where('user_login', '=', $user_login)
                    ->first();

                if(empty($user)) {
                    $error = 'User login is incorrect. It not found.';

                } else {

                    $result = $this->db
                        ->table('users')
                        ->where('user_login', $user_login)
                        ->limit(1)
                        ->update(['user_status' => 'trash']);
                }
            }
        }

        return [
            'error'   => !empty($error)  ? $error : '',
            'success' => !empty($result) ? true   : false
        ];
    }

    // Get disposable pass (by email/phone) +
    public function remind(array $data) {

        $user_email = !empty($data['user_email']) ? strtolower(trim($data['user_email'])) : null;
        $user_phone = !empty($data['user_phone']) ? strtolower(trim($data['user_phone'])) : null;

        if(empty($user_email) && empty($user_phone)) {
            $error = 'User email or user phone are incorrect. It can not be empty both.';

        } elseif(!empty($user_email) && (!is_string($user_email) || mb_strlen($user_email, 'utf-8') > 255 || !preg_match("/^[a-z0-9._-]{1,80}@(([a-z0-9-]+\.)+(com|net|org|mil|"."edu|gov|arpa|info|biz|inc|name|[a-z]{2})|[0-9]{1,3}\.[0-9]{1,3}\.[0-"."9]{1,3}\.[0-9]{1,3})$/", $user_email))) {
            $error = 'User email is incorrect. It must be a string contains a local-part, an @ symbol, a domain.';
        
        } elseif(!empty($user_phone) && (!ctype_digit($user_phone) || mb_strlen($user_phone, 'utf-8') < 4 || mb_strlen($user_phone, 'utf-8') > 20)) {
            $error = 'User phone is incorrect. It must be a string from 4 to 20 symbols and contains numbers only.';

        } else {

            $user = $this->db
                ->table('users')
                ->select(['user_status', 'user_hash_date'])
                ->selectRaw('now() as now');

            if(!empty($user_email)) {
                $user = $user->where('user_email', '=', $user_email);

            } elseif(!empty($user_phone)) {
                $user = $user->where('user_phone', '=', $user_phone);
            }

            $user = $user->first();

            if(empty($user)) {
                $error = 'User email or user phone are incorrect. User not found.';

            } elseif($user->user_status == 'trash') {
                $error = 'User email or user phone are incorrect. User is trashed.';

            } elseif(strtotime($user->now) - strtotime($user->user_hash_date) < 60) {
                $error = 'Pass updating error. The password can be changed no more than once every 60 seconds.';

            } else {

                $user_pass = $this->pass();
                $result = $this->db->table('users');

                if(!empty($user_email)) {
                    $result = $result->where('user_email', $user_email);

                } elseif(!empty($user_phone)) {
                    $result = $result->where('user_phone', $user_phone);
                }

                $result = $result
                    ->limit(1)
                    ->update([
                        'user_hash'      => $this->hash($user_pass),
                        'user_hash_date' => $this->db::raw('now()')
                    ]);
            }
        }

        return [
            'error'     => !empty($error)     ? $error     : '',
            'success'   => !empty($result)    ? true       : false,
            'user_pass' => !empty($user_pass) ? $user_pass : ''
        ];
    }

    // Signin user (by email/phone and pass) +
    public function signin(array $data) {

        $user_email = !empty($data['user_email']) ? strtolower(trim($data['user_email'])) : '';
        $user_phone = !empty($data['user_phone']) ? strtolower(trim($data['user_phone'])) : '';
        $user_pass  = !empty($data['user_pass'])  ? trim($data['user_pass'])              : '';
        $user_hash  = $this->hash($user_pass);

        if(empty($user_email) && empty($user_phone)) {
            $error = 'User email or user phone are incorrect. It can not be empty both.';

        } elseif(!empty($user_email) && (!is_string($user_email) || mb_strlen($user_email, 'utf-8') > 255 || !preg_match("/^[a-z0-9._-]{1,80}@(([a-z0-9-]+\.)+(com|net|org|mil|"."edu|gov|arpa|info|biz|inc|name|[a-z]{2})|[0-9]{1,3}\.[0-9]{1,3}\.[0-"."9]{1,3}\.[0-9]{1,3})$/", $user_email))) {
            $error = 'User email is incorrect. It must be a string contains a local-part, an @ symbol, a domain.';
        
        } elseif(!empty($user_phone) && (!ctype_digit($user_phone) || mb_strlen($user_phone, 'utf-8') < 4 || mb_strlen($user_phone, 'utf-8') > 20)) {
            $error = 'User phone is incorrect. It must be a string from 4 to 20 symbols and contains numbers only.';

        } elseif(empty($user_pass)) {
            $error = 'User pass is incorrect. It can not be empty.';
    
        } elseif(!is_string($user_pass) || mb_strlen($user_pass, 'utf-8') > 20) {
            $error = 'User pass is incorrect. It must be a string no longer than 20 symbols.';

        } else {

            $user = $this->db
                ->table('users')
                ->select(['id', 'user_status', 'user_token', 'user_hash_date'])
                ->selectRaw('now() as now')
                ->where('user_hash', '=', $user_hash);

            if(!empty($user_email)) {
                $user = $user->where('user_email', '=', $user_email);

            } elseif(!empty($user_phone)) {
                $user = $user->where('user_phone', '=', $user_phone);
            }

            $user = $user->first();

            if(empty($user)) {
                $error = 'User email or user phone are incorrect. User not found.';

            } elseif($user->user_status == 'trash') {
                $error = 'User email or user phone are incorrect. User is trashed.';

            } elseif(strtotime($user->now) - strtotime($user->user_hash_date) > 300) {
                $error = 'Pass is incorrect. It is valid only for 5 minutes.';

            } else {

                $result = $this->db
                    ->table('users')
                    ->where('id', '=', $user->id)
                    ->limit(1);

                if($user->user_status == 'pending' && $user->id == 1) {
                    $result = $result->update([
                            'user_status' => 'admin',
                            'user_hash' => ''
                        ]);

                } elseif($user->user_status == 'pending') {
                    $result = $result->update([
                            'user_status' => 'approved',
                            'user_hash' => ''
                        ]);

                } else {
                    $result = $result->update([
                            'user_hash' => ''
                        ]);
                }
            }
        }

        return [
            'error'      => !empty($error)  ? $error            : '',
            'success'    => !empty($result) ? true              : false,
            'user_token' => !empty($result) ? $user->user_token : []
        ];
    }

    // Signout user (by login / token needed) +
    public function signout(array $data) {

        $self_token = !empty($data['self_token']) ? trim($data['self_token'])             : '';
        $user_login = !empty($data['user_login']) ? strtolower(trim($data['user_login'])) : '';

        if(empty($self_token)) {
            $error = 'Self token is incorrect. It can not be empty.';
    
        } elseif(!is_string($self_token) || mb_strlen($self_token, 'utf-8') > 80 || !preg_match("/^[a-f0-9]{1,80}$/", $self_token)) {
            $error = 'Self token is incorrect. It must be a string no longer than 80 symbols and contains latin letters a-f and numbers only.';

        } else {

            $self = $this->db
                ->table('users')
                ->select(['user_login', 'user_status', 'user_token_date'])
                ->selectRaw('now() as now')
                ->where('user_token', '=', $self_token)
                ->first();

            if(empty($self)) {
                $error = 'Self token is incorrect. Self user not found.';

            } elseif($self->user_status == 'trash') {
                $error = 'Self token is incorrect. Self user is trashed.';

            } elseif($self->user_status == 'pending') {
                $error = 'Self token is incorrect. Self user can not be pending.';

            } elseif(strtotime($self->now) - strtotime($self->user_token_date) > 86400 * 30) {
                $error = 'Self token is incorrect. It expired.';

            } elseif($self->user_login != $user_login && $self->user_status != 'admin') {
                $error = 'Self token is incorrect. Self user have no permissions to update this user.';

            } elseif(empty($user_login)) {
                $error = 'User login is incorrect. It can not be empty.';

            } elseif(!is_string($user_login) || mb_strlen($user_login, 'utf-8') < 4 || mb_strlen($user_login, 'utf-8') > 40 || !preg_match("/^[a-z0-9_]{1,40}$/", $user_login)) {
                $error = 'User login is incorrect. It must be a string from 4 to 40 symbols and contains lowercase latin symbols and underline only.';

            } else {

                $result = $this->db
                    ->table('users')
                    ->where('user_login', $user_login)
                    ->limit(1)
                    ->update([
                        'user_token'      => $this->token(),
                        'user_token_date' => $this->db::raw('now()')
                    ]);
            }
        }

        return [
            'error'   => !empty($error)  ? $error : '',
            'success' => !empty($result) ? true   : false
        ];
    }

    // temp
    public function some(array $args = ['select' => ['*'], 'limit' => 1]) {

        $posts = $this->db
            ->table('users');

        if(array_key_exists('where', $args)) {
            foreach($args['where'] as $where) {
                $posts = $posts->where($where[0], $where[1], $where[2]);
            }
        }

        if(array_key_exists('select', $args)) {
            $posts = $posts->select($args['select']);
        }

        if(array_key_exists('offset', $args)) {
            $posts = $posts->offset($args['offset']);
        }

        if(array_key_exists('limit', $args)) {
            $posts = $posts->limit($args['limit']);
        }

        $posts = $posts->orderBy('id', 'DESC');

        return $posts->get();
    }

}