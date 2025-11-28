<?php

namespace Reconmap\Database;

class MysqlServer extends \mysqli
{
    /**
     * @return bool Returns true if the query was successful, false otherwise.
     */
    public function tryDummyQuery(): bool {
        $result = $this->execute_query('SELECT 1');
        if($result) {
            $result->free();
            return true;
        }
        return false;
    }
}
