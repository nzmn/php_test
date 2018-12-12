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
    $data_list = [];
    $row = 0;
    if (($handle = fopen($path, "r"))) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if ($row == 0) {
                $row++;
                continue;
            }
            $data_list[] = $data;
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
function dataFilter($dataArr): array
{
    $new_datalist = [];
    for ($i=0;$i<count($dataArr);$i++) {
        $new_datalist[$i][0] = ucfirst(strtolower(trim($dataArr[$i][0])));
        $new_datalist[$i][1] = ucfirst(strtolower(trim($dataArr[$i][1])));
        $new_datalist[$i][2] = strtolower(trim($dataArr[$i][2]));
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
    try{

    for ($i=0;$i<count($dataArr);$i++) {
        if (!filter_var($dataArr[$i][2], FILTER_VALIDATE_EMAIL)) {
//            $new_datalist[$i]['name'] = $dataArr[$i]['name'];
//            $new_datalist[$i]['surename'] = $dataArr[$i]['surename'];
//            $new_datalist[$i]['email'] = $dataArr[$i]['email'];

            fwrite(STDOUT, $dataArr[$i][0] . " " . $dataArr[$i][1] . " has an invalid email address. \n");
            unset($dataArr[$i]);
        }
    }}catch (Exception $exception){
        echo $exception->getMessage();
    }
//    return $dataArr;
    return $dataArr;
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
    $sql = "DROP TABLE IF EXISTS users;";
    $sql = "        
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
        $stmt->bind_param('sss', $value[0], $value[1], $value[2]);
        $success = $stmt->execute();
    }
    if ($success) {
        echo "Succeeded to insert Values.";
    } else {
        echo "Failed to insert Values.";
    }
}

/**
 * > php user_upload.php --file=users.csv --create_table  --dry_run -u = root   -p=  -h=localhost
 *
 * @var TYPE_NAME $argv
 */

var_dump($argv);


$shortOpts = "";
$shortOpts .= "u::";//-u=root
$shortOpts .= "p::";//-p=
$shortOpts .= "h::";//-h=localhost

$longOpts = [
    'file::',//--file=users.csv
    'create_table',//--create_table
    'dry_run',//--dry_run
    'help',//--help
];

$options = getopt($shortOpts, $longOpts);
var_dump($options);

$file = isset($options['file']) ? $options['file'] : 'users.csv';
$createTable = isset($options['create_table']);
$dryRun = isset($options['dry_run']);
$help = isset($options['help']);
$username = isset($options['u']) ? $options['u'] : 'root';
$password = isset($options['p']) ? $options['p'] : '';
$host = isset($options['h']) ? $options['h'] : 'localhost';

var_dump($file, $createTable, $dryRun, $help, $username, $password, $host);

if ($help) {
    echo "Help!!! Usage: php test_arg.php -u=root
        • --file [csv file name] – this is the name of the CSV to be parsed;
        • --create_table – this will cause the MySQL users table to be built (and no further
        • action will be taken);
        • --dry_run – this will be used with the --file directive in the instance that we want to run the
        script but not insert into the DB. All other functions will be executed, but the database won't
        be altered;
        • -u – MySQL username;
        • -p – MySQL password;
        • -h – MySQL host;
        • --help – which will output the above list of directives with details./n;";
}

$data_list = openAndReadFile($file);
$data_list = dataFilter($data_list);
$data_list = emailValidate($data_list);
$conn = getDBConnection($host, $username, $password);
createDB($conn);
mysqli_select_db($conn, "my_db");
createTable($conn);
if (!$dryRun) {
    insertValues($conn, $data_list);
}
mysqli_close($conn);




