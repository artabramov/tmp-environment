<?php
namespace artabramov\Echidna\Echidna;

class Foo extends \artabramov\Echidna\Echidna
{
    private $error;
    private $id;
    private $date;
    private $user_status;
    private $user_token;
    private $user_email;
    private $user_pass;
    private $user_hash;

    public function bar( $a ) {
        $this->e = 'E';
        //$this->error = 'ERROR';
        return $this->is_empty( $a );
    }

}