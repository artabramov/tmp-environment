<?php

namespace Artabramov\Echidna\Echidna;

class User
{
    private $db = null;

    public function __construct($db) {
        $this->db = $db;
    }

    // Check user exist
    private function isExist(array $args) {

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
    private function createToken() {

        do {
            $user_token = sha1(bin2hex(random_bytes(64)));

            if($this->isExist([['user_token', '=', $user_token]])) {
                $repeat = true;

            } else {
                $repeat = false;
            }

        } while ($repeat);

        return $user_token;
    }

    // Generate pass
    private function createPass() {

        $user_pass = '';
        for ($i = 0; $i < 6; $i++) {
            $user_pass .= mt_rand(0,9);
        }
        return $user_pass;
    }

    // Get hash
    private function getHash($pass) {
        return sha1($pass);
    }

    //-----

    /* Добавление нового пользователя. Требуется user_login и user_email
     * и user_phone (или и то, и другое). 
     * data: user_login, user_email (user_phone)
     * return: user_id, error
     */
    public function insertUser(array $data) {

        $user_login = !empty($data['user_login']) ? strtolower(trim($data['user_login'])) : '';
        $user_email = !empty($data['user_email']) ? strtolower(trim($data['user_email'])) : '';
        $user_phone = !empty($data['user_phone']) ? trim($data['user_phone'])             : '';

        if(empty($user_login)) {
            $error = 'User login is incorrect. It can not be empty.';

        } elseif(!is_string($user_login) || mb_strlen($user_login, 'utf-8') < 4 || mb_strlen($user_login, 'utf-8') > 40 || !preg_match("/^[a-z0-9_]{1,40}$/", $user_login)) {
            $error = 'User login is incorrect. It must be a string from 4 to 40 symbols and contains lowercase latin symbols and underline only.';

        } elseif($this->isExist([['user_login', '=', $user_login]])) {
            $error = 'User login is incorrect. It already exists.';

        } elseif(empty($user_email) && empty($user_phone)) {
            $error = 'User email or user phone are incorrect. It can not be empty both.';

        } elseif(!empty($user_email) && (!is_string($user_email) || mb_strlen($user_email, 'utf-8') > 255 || !preg_match("/^[a-z0-9._-]{1,80}@(([a-z0-9-]+\.)+(com|net|org|mil|"."edu|gov|arpa|info|biz|inc|name|[a-z]{2})|[0-9]{1,3}\.[0-9]{1,3}\.[0-"."9]{1,3}\.[0-9]{1,3})$/", $user_email))) {
            $error = 'User email is incorrect. It must be a string contains a local-part, an @ symbol, a domain.';

        } elseif(!empty($user_email) && $this->isExist([['user_email', '=', $user_email]])) {
            $error = 'User email is incorrect. It already exists.';
        
        } elseif(!empty($user_phone) && (!ctype_digit($user_phone) || mb_strlen($user_phone, 'utf-8') < 4 || mb_strlen($user_phone, 'utf-8') > 20)) {
            $error = 'User phone is incorrect. It must be a string from 4 to 20 symbols and contains numbers only.';

        } elseif(!empty($user_phone) && $this->isExist([['user_phone', '=', $user_phone]])) {
            $error = 'User phone is incorrect. It already exists.';

        } else {

            $user_id = $this->db->table('users')->insertGetId([
                'date'            => $this->db::raw('now()'),
                'user_status'     => 'pending',
                'user_login'      => $user_login,
                'user_hash'       => '',
                'user_hash_date'  => '0000-00-00 00:00:00',
                'user_token'      => $this->createToken(),
                'user_token_date' => $this->db::raw('now()'),
                'user_email'      => !empty($user_email) ? $user_email : '',
                'user_phone'      => !empty($user_phone) ? $user_phone : ''
            ]);

            if(empty($user_id)) {
                $error = 'User inserting error.';
            }
        }

        return [
            'error'   => !empty($error)   ? $error   : '',
            'user_id' => !empty($user_id) ? $user_id : 0
        ];
    }

    /*
     * Получение пользователя. Если пользователь выбирается по токену, то возвращаются
     * все доступные данные. Если выбирается по общедоступному идентификатору,
     * то возвращаются только открытые данные.
     * args: user_id, user_token, user_login, user_email, user_phone
     * return: user (расширенный или сокращенный), error
     */
    public function getUser(array $args) {

        $user_id    = !empty($args['user_id'])    ? intval(trim($args['user_id']))        : '';
        $user_token = !empty($args['user_token']) ? trim($args['user_token'])             : '';
        $user_login = !empty($args['user_login']) ? strtolower(trim($args['user_login'])) : '';
        $user_email = !empty($args['user_email']) ? strtolower(trim($args['user_email'])) : '';
        $user_phone = !empty($args['user_phone']) ? trim($args['user_phone'])             : '';

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

            $user = $this->db->table('users');

            if(!empty($user_token)) {
                $user = $user->select(['id', 'date', 'user_status', 'user_login', 'user_email', 'user_phone']);
            } else {
                $user = $user->select(['id', 'date', 'user_status', 'user_login']);
            }

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
                $error = 'User args is incorrect. User not found.';
            }
        }
            
        return [
            'error'   => !empty($error)    ? $error    : '',
            'user_id' => !empty($user->id) ? $user->id : 0,
            'user'    => !empty($user)     ? $user     : new \stdClass
        ];
    }

    /*
     * Если изменяется user_email или user_phone, то дополнительно генерируется новый токен,
     * поэтому после изменений пользователю придется заново получать одноразовый пароль.
     * args: user_token
     * data: user_login, user_email, user_phone
     * return: user_id, error
     */
    public function updateUser(array $args) {

        $user_token = !empty($args['user_token']) ? trim($args['user_token'])             : '';
        $user_login = !empty($args['user_login']) ? strtolower(trim($args['user_login'])) : '';
        $user_email = !empty($args['user_email']) ? strtolower(trim($args['user_email'])) : '';
        $user_phone = !empty($args['user_phone']) ? trim($args['user_phone'])             : '';

        if(empty($user_token)) {
            $error = 'User token is incorrect. It can not be empty.';
    
        } elseif(!is_string($user_token) || mb_strlen($user_token, 'utf-8') > 80 || !preg_match("/^[a-f0-9]{1,80}$/", $user_token)) {
            $error = 'User token is incorrect. It must be a string no longer than 80 symbols and contains latin letters a-f and numbers only.';

        } else {
            $user = $this->db
                ->table('users')
                ->select(['id', 'user_status', 'user_login', 'user_email', 'user_phone', 'user_token_date'])
                ->selectRaw('now() as now')
                ->where('user_token', '=', $user_token)
                ->first();

            if(empty($user)) {
                $error = 'User token is incorrect. User not found.';

            } elseif($user->user_status != 'approved') {
                $error = 'User token is incorrect. User must be approved.';

            } elseif(strtotime($user->now) - strtotime($user->user_token_date) > 86400 * 30) {
                $error = 'User token is incorrect. It expired.';

            } elseif(empty($user_login) && empty($user_email) && empty($user_phone)) {
                $error = 'User login, user email or user phone are incorrect. It can not be empty all.';

            } elseif(!empty($user_login) && (!is_string($user_login) || mb_strlen($user_login, 'utf-8') < 4 || mb_strlen($user_login, 'utf-8') > 40 || !preg_match("/^[a-z0-9_]{1,40}$/", $user_login))) {
                $error = 'User login is incorrect. It must be a string from 4 to 40 symbols and contains lowercase latin symbols and underline only.';
    
            } elseif(!empty($user_login) && $this->isExist([['user_login', '=', $user_login]])) {
                $error = 'User login is incorrect. It already exists.';

            } elseif(!empty($user_email) && (!is_string($user_email) || mb_strlen($user_email, 'utf-8') > 255 || !preg_match("/^[a-z0-9._-]{1,80}@(([a-z0-9-]+\.)+(com|net|org|mil|"."edu|gov|arpa|info|biz|inc|name|[a-z]{2})|[0-9]{1,3}\.[0-9]{1,3}\.[0-"."9]{1,3}\.[0-9]{1,3})$/", $user_email))) {
                $error = 'User email is incorrect. It must be a string contains a local-part, an @ symbol, a domain.';
        
            } elseif(!empty($user_email) && $this->isExist([['user_email', '=', $user_email]])) {
                $error = 'User email is incorrect. It already exists.';
    
            } elseif(!empty($user_phone) && (!ctype_digit($user_phone) || mb_strlen($user_phone, 'utf-8') < 4 || mb_strlen($user_phone, 'utf-8') > 20)) {
                $error = 'User phone is incorrect. It must be a string from 4 to 20 symbols and contains numbers only.';
        
            } elseif(!empty($user_phone) && $this->isExist([['user_phone', '=', $user_phone]])) {
                $error = 'User phone is incorrect. It already exists.';

            } else {

                $args = [
                    'user_login' => !empty($user_login) ? $user_login : $user->user_login,
                    'user_email' => !empty($user_email) ? $user_email : $user->user_email,
                    'user_phone' => !empty($user_phone) ? $user_phone : $user->user_phone
                ];

                if(!empty($user_email) || !empty($user_phone)) {
                    $args['user_token']      = $this->createToken();
                    $args['user_token_date'] = $this->db::raw('now()');
                }

                $result = $this->db
                    ->table('users')
                    ->where('user_token', $user_token)
                    ->limit(1)
                    ->update($args);

                if(empty($result)) {
                    $error = 'User updating error.';
                }
            }
        }

        return [
            'error'   => !empty($error)                     ? $error    : '',
            'user_id' => !empty($user->id) && empty($error) ? $user->id : 0
        ];
    }

    /* При удалении пользователя изменяется его статус на trash.
     * args: user_token
     * return: user_id, error
     */
    public function trashUser(array $args) {

        $user_token = !empty($args['user_token']) ? trim($args['user_token']) : '';

        if(empty($user_token)) {
            $error = 'User token is incorrect. It can not be empty.';
    
        } elseif(!is_string($user_token) || mb_strlen($user_token, 'utf-8') > 80 || !preg_match("/^[a-f0-9]{1,80}$/", $user_token)) {
            $error = 'User token is incorrect. It must be a string no longer than 80 symbols and contains latin letters a-f and numbers only.';

        } else {

            $user = $this->db
                ->table('users')
                ->select(['id', 'user_status', 'user_token_date'])
                ->selectRaw('now() as now')
                ->where('user_token', '=', $user_token)
                ->first();

            if(empty($user)) {
                $error = 'User token is incorrect. User not found.';

            } elseif($user->user_status == 'trash') {
                $error = 'User token is incorrect. User is trashed.';

            } elseif($user->user_status != 'approved') {
                $error = 'User token is incorrect. User must be approved.';

            } elseif(strtotime($user->now) - strtotime($user->user_token_date) > 86400 * 30) {
                $error = 'User token is incorrect. It expired.';

            } else {

                $result = $this->db
                    ->table('users')
                    ->where('user_token', $user_token)
                    ->limit(1)
                    ->update(['user_status' => 'trash']);

                if(empty($result)) {
                    $error = 'User trashing error.';
                }
            }
        }

        return [
            'error'   => !empty($error) ? $error : '',
            'user_id' => !empty($user->id) && empty($error) ? $user->id : 0
        ];
    }

    //-----

    /* Получение одноразового пароля пользователя по его электронной почте или номеру телефона.
     * args: user_email или user_phone
     * return: user_id, user_pass, error
     */
    public function getPass(array $args) {

        $user_email = !empty($args['user_email']) ? strtolower(trim($args['user_email'])) : null;
        $user_phone = !empty($args['user_phone']) ? strtolower(trim($args['user_phone'])) : null;

        if(empty($user_email) && empty($user_phone)) {
            $error = 'User email or user phone are incorrect. It can not be empty both.';

        } elseif(!empty($user_email) && (!is_string($user_email) || mb_strlen($user_email, 'utf-8') > 255 || !preg_match("/^[a-z0-9._-]{1,80}@(([a-z0-9-]+\.)+(com|net|org|mil|"."edu|gov|arpa|info|biz|inc|name|[a-z]{2})|[0-9]{1,3}\.[0-9]{1,3}\.[0-"."9]{1,3}\.[0-9]{1,3})$/", $user_email))) {
            $error = 'User email is incorrect. It must be a string contains a local-part, an @ symbol, a domain.';
        
        } elseif(!empty($user_phone) && (!ctype_digit($user_phone) || mb_strlen($user_phone, 'utf-8') < 4 || mb_strlen($user_phone, 'utf-8') > 20)) {
            $error = 'User phone is incorrect. It must be a string from 4 to 20 symbols and contains numbers only.';

        } else {

            $user = $this->db
                ->table('users')
                ->select(['id', 'user_status', 'user_hash_date'])
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

                $user_pass = $this->createPass();
                $result = $this->db->table('users');

                if(!empty($user_email)) {
                    $result = $result->where('user_email', $user_email);

                } elseif(!empty($user_phone)) {
                    $result = $result->where('user_phone', $user_phone);
                }

                $result = $result
                    ->limit(1)
                    ->update([
                        'user_hash'      => $this->getHash($user_pass),
                        'user_hash_date' => $this->db::raw('now()')
                    ]);
            }
        }

        return [
            'error'     => !empty($error)     ? $error     : '',
            'user_id'   => !empty($user->id)  ? $user->id  : 0,
            'user_pass' => !empty($user_pass) ? $user_pass : ''
        ];
    }

    /* Получение токена пользователя по его электронной почте и высланному одноразовому паролю.
     * Пароль действителен 5 минут. В случае правильного входа, пароль уничтожается и 
     * требуется его запрашивать заново.
     * args: user_email или user_phone и user_pass
     * return: user_id, user_token, error
     */
    public function getToken(array $args) {

        $user_email = !empty($args['user_email']) ? strtolower(trim($args['user_email'])) : '';
        $user_phone = !empty($args['user_phone']) ? strtolower(trim($args['user_phone'])) : '';
        $user_pass  = !empty($args['user_pass'])  ? trim($args['user_pass'])              : '';
        $user_hash  = $this->getHash($user_pass);

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
                $error = 'User email, user phone or user pass are incorrect. User not found.';

            } elseif($user->user_status == 'trash') {
                $error = 'User email or user phone are incorrect. User is trashed.';

            } elseif(strtotime($user->now) - strtotime($user->user_hash_date) > 300) {
                $error = 'Pass is incorrect. It is valid only for 5 minutes.';

            } else {

                $result = $this->db
                    ->table('users')
                    ->where('user_token', '=', $user->user_token)
                    ->limit(1);

                if($user->user_status == 'pending') {
                    $result = $result->update([
                            'user_status' => 'approved',
                            'user_hash' => ''
                        ]);

                } else {
                    $result = $result->update(['user_hash' => '']);
                }
            }
        }

        return [
            'error'      => !empty($error)    ? $error            : '',
            'user_id'    => !empty($user->id) ? $user->id         : 0,
            'user_token' => !empty($result)   ? $user->user_token : []
        ];
    }

    // updateToken (signout): TOKEN (signout)
    public function updateToken(array $args) {

        $user_token = !empty($args['user_token']) ? trim($args['user_token']) : '';

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
                $error = 'User token is incorrect. User not found.';

            } elseif($user->user_status == 'trash') {
                $error = 'User token is incorrect. It can not be trashed.';

            } else {

                $result = $this->db
                    ->table('users')
                    ->where('user_token', $user_token)
                    ->limit(1)
                    ->update([
                        'user_token'      => $this->createToken(),
                        'user_token_date' => $this->db::raw('now()')
                    ]);
            }
        }

        return [
            'error'   => !empty($error) ? $error : '',
            'user_id' => !empty($user->id) && empty($error) ? $user->id : 0
        ];
    }

    // temp
    /*
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
    */

}