<?php

namespace Echidna;

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

    // Insert a new user
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

    // Select any single user (by id, token, login, email or phone)
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

    // Update self approved user (by login and token)
    public function update(array $data) {

        $user_token = !empty($data['user_token']) ? trim($data['user_token'])             : '';
        $user_login = !empty($data['user_login']) ? strtolower(trim($data['user_login'])) : '';
        $user_email = !empty($data['user_email']) ? strtolower(trim($data['user_email'])) : '';
        $user_phone = !empty($data['user_phone']) ? trim($data['user_phone'])             : '';

        if(empty($user_token)) {
            $error = 'User token is incorrect. It can not be empty.';
    
        } elseif(!is_string($user_token) || mb_strlen($user_token, 'utf-8') > 80 || !preg_match("/^[a-f0-9]{1,80}$/", $user_token)) {
            $error = 'User token is incorrect. It must be a string no longer than 80 symbols and contains latin letters a-f and numbers only.';

        } elseif(empty($user_login)) {
            $error = 'User login is incorrect. It can not be empty.';

        } elseif(!is_string($user_login) || mb_strlen($user_login, 'utf-8') < 4 || mb_strlen($user_login, 'utf-8') > 40 || !preg_match("/^[a-z0-9_]{1,40}$/", $user_login)) {
            $error = 'User login is incorrect. It must be a string from 4 to 40 symbols and contains lowercase latin symbols and underline only.';

        } else {

            $user = $this->db
                ->table('users')
                ->select(['user_status', 'user_token', 'user_email', 'user_phone'])
                ->selectRaw('now() - user_token_date as user_token_interval')
                ->where('user_login', '=', $user_login)
                ->first();

            if(empty($user)) {
                $error = 'User login is incorrect. It not found.';

            } elseif($user->user_status == 'trash') {
                $error = 'User login is incorrect. It is trashed.';

            } elseif($user->user_status != 'approved') {
                $error = 'User login is incorrect. It must be approved.';

            } elseif($user->user_token_interval > 86400 * 30) {
                $error = 'User token is incorrect. It expired.';

            } elseif($user->user_token != $user_token) {
                $error = 'User token is incorrect. It have no permissions to trash this user.';

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
                        'user_token'   => $this->token(),
                        'user_status'  => 'pending',
                        'user_email'   => !empty($user_email) ? $user_email : $user->user_email,
                        'user_phone'   => !empty($user_phone) ? $user_phone : $user->user_phone
                    ]);
            }
        }

        return [
            'error'   => !empty($error)  ? $error : '',
            'success' => !empty($result) ? true   : false
        ];
    }

    // Trash self approved user (by login and token)
    public function trash(array $data) {

        $user_token = !empty($data['user_token']) ? trim($data['user_token'])             : '';
        $user_login = !empty($data['user_login']) ? strtolower(trim($data['user_login'])) : '';

        if(empty($user_token)) {
            $error = 'User token is incorrect. It can not be empty.';
    
        } elseif(!is_string($user_token) || mb_strlen($user_token, 'utf-8') > 80 || !preg_match("/^[a-f0-9]{1,80}$/", $user_token)) {
            $error = 'User token is incorrect. It must be a string no longer than 80 symbols and contains latin letters a-f and numbers only.';

        } elseif(empty($user_login)) {
            $error = 'User login is incorrect. It can not be empty.';

        } elseif(!is_string($user_login) || mb_strlen($user_login, 'utf-8') < 4 || mb_strlen($user_login, 'utf-8') > 40 || !preg_match("/^[a-z0-9_]{1,40}$/", $user_login)) {
            $error = 'User login is incorrect. It must be a string from 4 to 40 symbols and contains lowercase latin symbols and underline only.';

        } else {

            $user = $this->db
                ->table('users')
                ->select(['user_status', 'user_token'])
                ->selectRaw('now() - user_token_date as user_token_interval')
                ->where('user_login', '=', $user_login)
                ->first();

            if(empty($user)) {
                $error = 'User login is incorrect. It not found.';

            } elseif($user->user_status == 'trash') {
                $error = 'User login is incorrect. It is trashed.';

            } elseif($user->user_status != 'approved') {
                $error = 'User login is incorrect. It must be approved.';

            } elseif($user->user_token_interval > 86400 * 30) {
                $error = 'User token is incorrect. It expired.';

            } elseif($user->user_token != $user_token) {
                $error = 'User token is incorrect. It have no permissions to trash this user.';

            } else {

                $result = $this->db
                    ->table('users')
                    ->where('user_login', '=', $user_login)
                    ->limit(1)
                    ->update(['user_status' => 'trash']);
            }
        }

        return [
            'error'   => !empty($error)  ? $error : '',
            'success' => !empty($result) ? true   : false
        ];
    }

    // Get disposable pass (by email/phone)
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
                ->select(['id', 'user_status'])
                ->selectRaw('now() - user_hash_date as user_hash_interval');

            if(!empty($user_email)) {
                $user = $user->where('user_email', '=', $user_email);

            } elseif(!empty($user_phone)) {
                $user = $user->where('user_phone', '=', $user_phone);
            }

            $user = $user->first();

            if(empty($user)) {
                $error = 'User email or user phone are incorrect. User not found.';

            } elseif($user->user_status == 'trash') {
                $error = 'User email or user phone are incorrect. User trashed.';

            } elseif($user->user_hash_interval < 60) {
                $error = 'Pass updating error. The password can be changed no more than once every 60 seconds.';

            } else {

                $user_pass = $this->pass();
                $user_updated = $this->db
                    ->table('users')
                    ->where('id', $user->id)
                    ->limit(1)
                    ->update([
                        'user_hash'      => $this->hash($user_pass),
                        'user_hash_date' => $this->db::raw('now()')
                    ]);
            }
        }

        return [
            'error'     => !empty($error)     ? $error     : '',
            'success'   => !empty($user_pass) ? true       : false,
            'user_pass' => !empty($user_pass) ? $user_pass : ''
        ];
    }

    // Signin user (by email/phone and pass)
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
                ->select(['id', 'user_token', 'user_status'])
                ->selectRaw('now() - user_hash_date as user_hash_interval')
                ->where('user_hash', '=', $user_hash);

            if(!empty($user_email)) {
                $user = $user->where('user_email', '=', $user_email);

            } elseif(!empty($user_phone)) {
                $user = $user->where('user_phone', '=', $user_phone);
            }

            $user = $user->first();

            if(empty($user->id)) {
                $error = 'User data is incorrect. User not found.';

            } elseif($user->user_status == 'trash') {
                $error = 'User data is incorrect. User is trashed.';

            } elseif($user->user_hash_interval > 300) {
                $error = 'User data is incorrect. Password is valid only for 5 minutes. Update it.';

            } else {

                if($user->user_status == 'pending') {
                    $this->db
                        ->table('users')
                        ->where('id', $user->id)
                        ->limit(1)
                        ->update(['user_status' => 'approved']);
                }

                $result = $this->db
                    ->table('users')
                    ->where('id', $user->id)
                    ->limit(1)
                    ->update([
                        'user_hash' => ''
                    ]);
            }
        }

        return [
            'error'      => !empty($error)  ? $error            : '',
            'success'    => !empty($result) ? true              : false,
            'user_token' => !empty($result) ? $user->user_token : []
        ];
    }

    // Signout user (by token)
    public function signout(array $data) {

        $user_token = !empty($data['user_token']) ? trim($data['user_token']) : '';

        if(empty($user_token)) {
            $error = 'User token is incorrect. It can not be empty.';
    
        } elseif(!is_string($user_token) || mb_strlen($user_token, 'utf-8') > 80 || !preg_match("/^[a-f0-9]{1,80}$/", $user_token)) {
            $error = 'User token is incorrect. It must be a string no longer than 80 symbols and contains latin letters a-f and numbers only.';

        } else {

            $user = $this->db
                ->table('users')
                ->select(['id', 'user_status'])
                ->where('user_token', '=', $user_token)
                ->first();

            if(empty($user)) {
                $error = 'User token is incorrect. It not found.';

            } elseif($user->user_status == 'trash') {
                $error = 'User token is incorrect. User is trashed.';

            } elseif($user->user_status != 'approved') {
                $error = 'User token is incorrect. User must be approved.';

            } else {

                $result = $this->db
                    ->table('users')
                    ->where('id', $user->id)
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
    public function _select(array $args = ['select' => ['*'], 'limit' => 1]) {

        $posts = $this
            ->db
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