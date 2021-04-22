<?php
namespace optiwariindia;

class database
{
    private $host, $user, $dbname, $pass, $con, $mode = 0, $debug = false;
    public function __Construct($database = array())
    {
        $this->host = (isset($database['host'])) ? $database['host'] : "";
        $this->user = (isset($database['user'])) ? $database['user'] : "";
        $this->dbname = (isset($database['name'])) ? $database['name'] : "";
        $this->pass = (isset($database['pass'])) ? $database['pass'] : "";
    }
    public function debug()
    {
        $this->debug = true;
    }
    public function mode($mode)
    {
        $this->mode = $mode;
    }
    public function connect()
    {
        if ($this->debug) {
            $this->con = mysqli_connect($this->host, $this->user, $this->pass, $this->dbname) or die("Error" . mysqli_connect_error());
            mysqli_set_charset($this->con, "utf8");
        } else {
            $this->con = mysqli_connect($this->host, $this->user, $this->pass, $this->dbname) or die();
        }
    }
    public function test()
    {
        $this->con = mysqli_connect($this->host, $this->user, $this->pass, $this->dbname);
        if (!$this->con) {
            return "Error" . mysqli_connect_error();
        } else {
            return false;
            $this->disconnect();
        }
    }
    public function tables()
    {
        $this->connect();
        $tableList = array();
        if ($this->debug) {
            $res = mysqli_query($this->con, "SHOW TABLES") or die("Error description: " . mysqli_error($this->con));
        } else {
            $res = mysqli_query($this->con, "SHOW TABLES") or die("");
        }
        while ($row = mysqli_fetch_array($res)) {
            $tableList[] = $row[0];
        }
        $this->disconnect();
        return $tableList;
    }
    public function select($table, $fields = "*", $where = "")
    {
        $sql = "select $fields from $table $where";
        $data = array();
        $this->connect();
        if ($this->debug == true) {
            $result = mysqli_query($this->con, $sql) or die("Error in the statement" . $sql . " i.e. " . mysqli_error($this->con));
        } else {
            $result = mysqli_query($this->con, $sql) or die();
        }

        $m = $result->field_count;
        $n = $result->num_rows;
        $data['rows'] = $n;
        $flds = mysqli_fetch_fields($result);
        for ($j = 0; $j < $m; $j++) {
            $fld[$j] = $flds[$j]->name;
        }
        $data['fields'] = $fld;
        for ($i = 0; $i < $n; $i++) {
            $d = mysqli_fetch_array($result);
            for ($j = 0; $j < $m; $j++) {
                $data[$i][$j] = $d[$j];
            }
        }
        $this->disconnect();
        switch ($this->mode) {
            case 0:
                return $data;
                break;
            case 1:
                $dat = array();
                for ($i = 0; $i < $data['rows']; $i++) {
                    foreach ($data['fields'] as $j => $value) {
                        $dat[$i][$value] = $data[$i][$j];
                    }
                }
                return $dat;
                break;
            case 2:
                $dat = array();
                for ($i = 0; $i < $data['rows']; $i++) {
                    foreach ($data['fields'] as $j => $value) {
                        $dat[$data[$i][0]][$value] = $data[$i][$j]; //Row index is first value(id in most cases)
                    }
                }
                return $dat;
                break;
            case 3:
                $dat = array();
                for ($i = 0; $i < $data['rows']; $i++) {
                    foreach ($data['fields'] as $j => $value) {
                        if (($value != "image") & ($value != "mime")) {

                            $jsonDat = json_decode($data[$i][$j], 1);
                            if (is_array($jsonDat)) {
                                $dat[$i][$value] = $jsonDat;
                            } else {
                                $dat[$i][$value] = $data[$i][$j];
                            }
                        }

                    }
                }
                return $dat;
                break;
        }
    }
    public function insert($table, $data)
    {
        $sql = "insert into $table set ";
        $fld = array();
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }

            if (($key == "pass") || ($key == "passwd")) {
                $fld[] = " $key=md5('{$value}')";
            } else {
                if ($value == "now()") {
                    $fld[] = " $key = $value ";
                } else {

                    $fld[] = " $key='{$value}'";
                }
            }

        }
        $sql .= $fld[0];
        for ($i = 1; $i < count($fld); $i++) {
            $sql .= " , " . $fld[$i];
        }
        return $this->query($sql);
    }
    public function update($table, $data, $where = "")
    {
        $sql = "update `{$table}` set ";
        $fld = array();
        foreach ($data as $key => $value) {
          if (($key == "pass") || ($key == "passwd")) {
              $fld[] = " $key=md5('{$value}')";
          }elseif ($value == "now()") {
                $fld[] = " $key = $value ";
            } else {
                if (is_array($value)) {
                    $value = json_encode($value);
                }

                $fld[] = (is_numeric($value)) ? " $key={$value}" : " $key='{$value}'";
            }
        }
        $sql .= $fld[0];
        for ($i = 1; $i < count($fld); $i++) {
            $sql .= " , " . $fld[$i];
        }

        $sql .= " " . $where;
        return $this->query($sql);
    }
    public function delete($table, $where = "")
    {
        $sql = "delete from `{$table}` ";
        $sql .= " " . $where;
        return $this->query($sql);
    }
    public function resa($var)
    {
        $data = array();
        foreach ($var as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->resa($value);
            } else {
                $data[$key] = $this->res($value);
            }

        }
        return $data;
    }
    public function res($var)
    {
        $this->connect();
        $val = mysqli_escape_string($this->con, $var);
        $this->disconnect();
        return $val;
    }
    public function disconnect()
    {
        mysqli_close($this->con);
    }
    public function query($sql, $debug = false)
    {
        $this->connect();
        if ($this->debug) {
            $result = mysqli_query($this->con, $sql) or die("Error description: " . mysqli_error($this->con));

        } else {
            $result = mysqli_query($this->con, $sql) or die();
        }
        if (strstr($sql, "insert")) {
            $id = $this->con->insert_id;
            $this->disconnect();
            $resp = array("result" => $result, 'id' => $id);
            if ($this->debug) {
                $res["query"] = $sql;
            }

            return $resp;
        } else {
            $this->disconnect();
            return array("result" => $result, "query" => $sql);
        }
    }
}
