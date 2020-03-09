<?php

namespace Artabramov\Echidna;

class Userdata
{
    private $db = null;

    public function __construct($db) {
        $this->db = $db;
    }

    // Insert new userdata (by login and token)
    public function updateOrInsert(array $data) {

        $user_token = !empty($data['user_token']) ? trim($data['user_token']) : '';
        $user_login = !empty($data['user_login']) ? trim($data['user_login']) : '';
        $data_key   = !empty($data['data_key'])   ? strtolower(trim($data['data_key'])) : '';
        $data_value = !empty($data['data_key'])   ? trim($data['data_value']) : '';

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
                ->select(['id', 'user_status', 'user_token'])
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

            } elseif(empty($data_key)) {
                $error = 'Data key is incorrect. It can not be empty.';
    
            } elseif(!is_string($data_key) || mb_strlen($data_key, 'utf-8') < 2 || mb_strlen($data_key, 'utf-8') > 40 || !preg_match("/^[a-z0-9_]{1,40}$/", $data_key)) {
                $error = 'Data key is incorrect. It must be a string from 2 to 40 symbols and contains lowercase latin symbols and underline only.';

            } elseif(empty($data_value)) {
                $error = 'Data value is incorrect. It can not be empty.';

            } elseif(!is_string($data_value) || mb_strlen($data_value, 'utf-8') > 255) {
                $error = 'Data value is incorrect. It must be a string no longer than 255 symbols.';

            } else {

                $this->db->table('userdata')->updateOrInsert(
                    ['creator_id' => $user->id, 'data_key' => $data_key],
                    ['created_date' => $this->db::raw('now()'), 'data_value' => $data_value]);

                if($this->db->table('userdata')->where('creator_id', $user->id)->where('data_key', $data_key)->where('data_value', $data_value)->count() > 0) {
                    $result = true;
                }
            }
        }

        return [
            'error'   => !empty($error)  ? $error : '',
            'success' => !empty($result) ? true   : false
        ];
    }

    
}