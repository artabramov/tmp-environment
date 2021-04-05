<?php
namespace artabramov\Echidna\Models;

class Echidna
{
    protected $pdo;
    protected $e;

    public function __construct( \PDO $pdo ) {
        $this->pdo = $pdo;
    }

    /**
     * @param string $table
     * @param array $data
     * @return mixed
     * @throws \PDOException
     */
    protected function insert( string $table, array $data ) : mixed {

        $keys = '';
        $values = '';
        foreach( $data as $key=>$value ) {
            $keys .= empty( $keys ) ? $key : ', ' . $key;
            $values .= empty( $values ) ? ':' . $key : ', ' . ':' . $key;
        }

        try {
            $stmt = $this->pdo->prepare( 'INSERT INTO ' . $table . ' ( ' . $keys . ' ) VALUES ( ' . $values . ' )' );
            foreach( $data as $key=>$value ) {
                $stmt->bindParam( ':' . $key, $data[ $key ], \PDO::PARAM_STR );
            }

            $stmt->execute();
            $id = $this->pdo->lastInsertId();

        } catch( \PDOException $e ) {
            $this->e = $e;
        }

        return empty( $this->e ) ? $id : false;
    }

    /**
     * @param string $table
     * @param array $args
     * @param array $data
     * @return bool
     * @throws \PDOException
     */
    protected function update( string $table, array $args, array $data ) : mixed {

        $set = '';
        foreach( $data as $key=>$value ) {
            $set .= empty( $set ) ? 'SET ' : ', ';
            $set .= $key . '=:' . $key;
        }

        $where = '';
        foreach( $args as $arg ) {
            $where .= empty( $where ) ? 'WHERE ' : ' AND ';
            $where .= $arg[0] . $arg[1] . ':' . $arg[0];
        }

        try {
            $stmt = $this->pdo->prepare( 'UPDATE ' . $table . ' ' . $set . ' ' . $where . ' LIMIT 1' );

            foreach( $args as $arg ) {
                if( $arg[0] == 'id' ) {
                    $stmt->bindParam( ':id', $arg[2], \PDO::PARAM_INT );

                } else {
                    $stmt->bindParam( ':' . $arg[0], $arg[2], \PDO::PARAM_STR );
                }
            }

            foreach( $data as $key=>&$value ) {
                $stmt->bindParam( ':' . $key, $value, \PDO::PARAM_STR );
            }

            $stmt->execute();
            $rows = $stmt->rowCount();

        } catch( \PDOException $e ) {
            $this->e = $e;
        }

        return empty( $this->e ) ? true : false;
    }

    /**
     * @param string $table
     * @param array $args
     * @param int $limit
     * @param int $offset
     * @return mixed
     * @throws \PDOException
     */
    protected function select( string $fields, string $table, array $args, int $limit, int $offset ) : mixed {

        $where = '';
        foreach( $args as $arg ) {
            $where .= empty( $where ) ? 'WHERE ' : ' AND ';
            $where .= $arg[0] . $arg[1] . ':' . $arg[0];
        }

        try {
            $stmt = $this->pdo->prepare( 'SELECT ' . $fields . ' FROM ' . $table . ' ' . $where . ' LIMIT :limit OFFSET :offset' );

            foreach( $args as $arg ) {
                if( $arg[0] == 'id' ) {
                    $stmt->bindParam( ':id', $arg[2], \PDO::PARAM_INT );

                } else {
                    $stmt->bindParam( ':' . $arg[0], $arg[2], \PDO::PARAM_STR );
                }
            }

            $stmt->bindValue( ':limit', $limit, \PDO::PARAM_INT );
            $stmt->bindValue( ':offset', $offset, \PDO::PARAM_INT );

            $stmt->execute();
            $rows = $stmt->fetchAll( \PDO::FETCH_ASSOC );

        } catch( \PDOException $e ) {
            $this->e = $e;
        }

        return empty( $this->e ) ? $rows : false;
    }

    /**
     * @param string $table
     * @param array $args
     * @param int $limit
     * @param int $offset
     * @return bool
     * @throws \PDOException
     */
    protected function delete( string $table, array $args ) : mixed {

        $where = '';
        foreach( $args as $arg ) {
            $where .= empty( $where ) ? 'WHERE ' : ' AND ';
            $where .= $arg[0] . $arg[1] . ':' . $arg[0];
        }

        try {
            $stmt = $this->pdo->prepare( 'DELETE FROM ' . $table . ' ' . $where . ' LIMIT 1' );

            foreach( $args as $arg ) {
                if( $arg[0] == 'id' ) {
                    $stmt->bindParam( ':id', $arg[2], \PDO::PARAM_INT );

                } else {
                    $stmt->bindParam( ':' . $arg[0], $arg[2], \PDO::PARAM_STR );
                }
            }

            $stmt->execute();
            $rows = $stmt->rowCount();

        } catch( \PDOException $e ) {
            $this->e = $e;
        }

        return empty( $this->e ) ? true : false;
    }

    /**
     * @param string $table
     * @param array $args
     * @return int
     * @throws \PDOException
     */
    protected function count( string $table, array $args ) : int {

        $where = '';
        foreach( $args as $arg ) {
            $where .= empty( $where ) ? 'WHERE ' : ' AND ';
            $where .= $arg[0] . $arg[1] . ':' . $arg[0];
        }

        try {
            $stmt = $this->pdo->prepare( 'SELECT COUNT(id) FROM ' . $table . ' ' .$where );
            foreach( $args as $arg ) {

                if( $arg[0] == 'id' ) {
                    $stmt->bindParam( ':' . $arg[0], $arg[2], \PDO::PARAM_INT );

                } else {
                    $stmt->bindParam( ':' . $arg[0], $arg[2], \PDO::PARAM_STR );
                }
            }

            $stmt->execute();
            $rows = $stmt->fetch( \PDO::FETCH_ASSOC );
            return $rows['COUNT(id)'];

        } catch( \PDOException $e ) {
            $this->e = $e;
        }

        return 0;
    }

    /**
     * @return string
     * @throws \PDOException
     */
    public function datetime() : string {

        try {
            $result = $this->pdo->query( 'SELECT NOW() as datetime' )->fetch();

        } catch( \PDOException $e ) {
            $this->e = $e;
        }

        return empty( $this->e ) ? $result['datetime'] : '0000-00-00 00:00:00';
    }

}
