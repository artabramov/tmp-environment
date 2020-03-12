<?php

namespace Artabramov\Echidna\Echidna;

class Usermeta
{
    private $db = null;

    public function __construct($db) {
        $this->db = $db;
    }

    // Insert new userdata
    public function updateUsermeta(array $args) {

        $user_id    = !empty($args['user_id'])    ? intval(trim($args['user_id']))      : 0;
        $meta_key   = !empty($args['meta_key'])   ? strtolower(trim($args['meta_key'])) : '';
        $meta_value = !empty($args['meta_value']) ? trim($args['meta_value'])           : '';

        if(empty($user_id)) {
            $error = 'User id is incorrect. It can not be empty.';
            
        } elseif((!is_int($user_id) && !ctype_digit($user_id)) || mb_strlen($user_id, 'utf-8') > 20) {
            $error = 'User id is incorrect. It must be a number or string contains numbers only no longer than 20 symbols.';

        } elseif(empty($meta_key)) {
            $error = 'Meta key is incorrect. It can not be empty.';

        } elseif(!is_string($meta_key) || mb_strlen($meta_key, 'utf-8') < 2 || mb_strlen($meta_key, 'utf-8') > 40 || !preg_match("/^[a-z0-9_]{1,40}$/", $meta_key)) {
            $error = 'Meta key is incorrect. It must be a string from 2 to 40 symbols and contains lowercase latin symbols and underline only.';

        //} elseif(!isset($meta_value)) {
        //    $error = 'Meta value is incorrect. It can must be isset.';

        } elseif(!empty($meta_value) && (!is_string($meta_value) || mb_strlen($meta_value, 'utf-8') > 255)) {
            $error = 'Meta value is incorrect. It must be a string no longer than 255 symbols.';

        } else {

            if(!empty($meta_value)) {
                $this->db
                    ->table('usermeta')
                    ->updateOrInsert([
                        'user_id' => $user_id, 
                        'meta_key' => $meta_key
                    ],[
                        'date' => $this->db::raw('now()'), 
                        'meta_value' => $meta_value]);

            } else {
                $this->db
                    ->table('usermeta')
                    ->where('user_id', $user_id)
                    ->where('meta_key', $meta_key)
                    ->delete();

            }

            $meta = $this->db
                ->table('usermeta')
                ->where('user_id', $user_id)
                ->where('meta_key', $meta_key);

            if(!empty($meta_value)) {
                $meta = $meta
                    ->where('meta_value', $meta_value);
            }

            $meta = $meta
                ->select('id')
                ->first();

            if(!empty($meta_value) && empty($meta->id)) {
                $error = 'Meta updating or inserting error.';

            } elseif(empty($meta_value) && !empty($meta->id)) {
                $error = 'Meta deleting error.';
            }
        }

        return [
            'error'   => !empty($error)    ? $error    : '',
            'meta_id' => !empty($meta->id) ? $meta->id : 0
        ];
    }

    // Delete userdata (by user_id & meta_key)
    /*
    public function delete(array $data) {

        $user_id  = !empty($data['user_id'])    ? intval(trim($data['user_id']))      : 0;
        $meta_key = !empty($data['meta_key']) ? strtolower(trim($data['meta_key'])) : '';

        if(empty($user_id)) {
            $error = 'User id is incorrect. It can not be empty.';

        } elseif((!is_int($user_id) && !ctype_digit($user_id)) || mb_strlen($user_id, 'utf-8') > 20) {
            $error = 'User id is incorrect. It must be a number or string contains numbers only no longer than 20 symbols.';

        } elseif(empty($meta_key)) {
            $error = 'Meta key is incorrect. It can not be empty.';

        } elseif(!is_string($meta_key) || mb_strlen($meta_key, 'utf-8') < 2 || mb_strlen($meta_key, 'utf-8') > 40 || !preg_match("/^[a-z0-9_]{1,40}$/", $meta_key)) {
            $error = 'Meta key is incorrect. It must be a string from 2 to 40 symbols and contains lowercase latin symbols and underline only.';

        } else {

            $this->db
                ->table('usermeta')
                ->where('user_id', $user_id)
                ->where('meta_key', $meta_key)
                ->delete();

            $meta = $this->db
                ->table('usermeta')
                ->where('user_id', $user_id)
                ->where('meta_key', $meta_key)
                ->select('id')
                ->first();

            if(!empty($meta->id)) {
                $error = 'Meta deleting error.';
            }
        }

        return [
            'error' => !empty($error) ? $error : ''
        ];
    }
    */
    
}