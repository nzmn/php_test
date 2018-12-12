<?php
/**
 * Part 2: Programming task
 * Author: Na Zhang
 * Date: 2018-12-12
 */


/**
 * Open a file and sent an error message if this file does not exist.
 *
 * @param string $path
 * @return array
 */
function openAndReadFile($path)
{
    $data_list=[];
    $row = 0;
    if (($handle = fopen($path, "r"))) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if ($row == 0) {
                $row++;
                continue;
            }
            $data_list[$row]['name'] = $data[0];
            $data_list[$row]['surename'] = $data[1];
            $data_list[$row]['email'] = $data[2];
            $row++;
        }
        fclose($handle);
    } else {
        die("File not find!");
    }
    return $data_list;
}

/**
 *This function removes extra characters from both sides of a string,
 *capitalizes the first letter of the name and surname, and set the email address to lower case.
 *
 * @param array $dataArr
 * @return array
 */
function dataValidate($dataArr): array
{
    $new_datalist = [];
    for ($i = 1; $i <= count($dataArr); $i++) {
        $new_datalist[$i]['name'] = ucfirst(strtolower(trim($dataArr[$i]['name'], " \t\n\r\0\ ")));
        $new_datalist[$i]['surename'] = ucfirst(strtolower(trim($dataArr[$i]['surename'], " \t\n\r\ ")));
        $new_datalist[$i]['email'] = strtolower(trim($dataArr[$i]['email'], " \t\n\r\ "));
    }
    return $new_datalist;
}

/**
 * This function remove invalid email addresses before insert these into the database
 *
 * @param array $dataArr
 * @return array
 */
function emailValidate($dataArr): array
{
    $new_datalist = [];
    for ($i = 1; $i <= count($dataArr); $i++) {
        if (filter_var($dataArr[$i]['email'], FILTER_VALIDATE_EMAIL)) {
            $new_datalist[$i]['name'] = $dataArr[$i]['name'];
            $new_datalist[$i]['surename'] = $dataArr[$i]['surename'];
            $new_datalist[$i]['email'] = $dataArr[$i]['email'];
        } else {
            fwrite(STDOUT, $dataArr[$i]['name'] . " " . $dataArr[$i]['surename'] . " has an invalid email address. \n");
            continue;
        }
    }
    return $new_datalist;
}

/**
 * Get database connection
 *
 * @param string $host
 * @param string $user
 * @param string $password
 * @return mysqli
 */

function getDBConnection($host, $user, $password)
{
    return mysqli_connect($host, $user, $password, '', '3306');
}

/**
 *Create a DB in MySQL
 *
 * @param mysqli $con
 */
function createDB($con)
{
    if (mysqli_query($con, "CREATE DATABASE IF NOT EXISTS my_db")) {
        echo "Database created.\n";
    } else {
        echo "Error creating database: " . mysqli_error($con);
    }
}
/**
 *Create a table in MySQL
 * @param mysqli $con
 */
function createTable($con)
{
    $sql= "    
        CREATE TABLE IF NOT EXISTS users (
          id int(6) unsigned NOT NULL AUTO_INCREMENT,
          Name varchar(20) NOT NULL,
          Surename varchar(20) NOT NULL,
          Email varchar(50) NOT NULL,
          PRIMARY KEY (id),
          UNIQUE KEY (Email)
        );";
    if ($con->query($sql) === TRUE) {
        echo "Table users created successfully.\n";
    } else {
        echo "Failed to create table users: " . $con->error;
    }
}

/**
 *Insert values(name, surename, email) into the table
 *
 * @param mysqli $con
 * @param array $d_list
 */
function insertValues($con, $d_list)
{
    foreach ($d_list as $value) {
        $stmt = $con->prepare(/** @lang text */
            "INSERT INTO users(Name, Surename, Email) VALUES (?,?,?)");
        $stmt->bind_param('sss', $value['name'], $value['surename'], $value['email']);
        $success = $stmt->execute();
    }
    if ($success) {
        echo "Succeeded to insert Values.";
    } else {
        echo "Failed to insert Values.";
    }
}

$file = 'users.csv';
$data_list = [];
$data_list = openAndReadFile($file);
$data_list = dataValidate($data_list);
$data_list = emailValidate($data_list);
$conn = getDBConnection('localhost', 'root', '');
createDB($conn);
mysqli_select_db($conn, "my_db");
createTable($conn);
insertValues($conn, $data_list);
mysqli_close($conn);




