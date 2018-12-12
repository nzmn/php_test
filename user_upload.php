<?php
/**
 * Part 2: Programming task
 * Author: Na Zhang
 * Date: 2018-12-13
 */


/**
 * Open a file and read this file by lines. If this file does not exist, an error message will be sent.
 *
 * @param string $path
 * @return array
 */
function openAndReadFile($path)
{
    $data_list = [];
    $row = 0;
    try {
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
    } catch (Exception $exception){
    echo $exception->getMessage();
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
    try{
        for ($i=0;$i<count($dataArr);$i++) {
        $dataArr[$i][0] = ucfirst(strtolower(trim($dataArr[$i][0])));
        $dataArr[$i][1] = ucfirst(strtolower(trim($dataArr[$i][1])));
        $dataArr[$i][2] = strtolower(trim($dataArr[$i][2]));
    }}catch (Exception $exception) {
        echo $exception->getMessage();
    }
    return $dataArr;
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
          fwrite(STDOUT, $dataArr[$i][0] . " " . $dataArr[$i][1] . " has an invalid email address. \n");
            unset($dataArr[$i]);
        }
    }}catch (Exception $exception){
        echo $exception->getMessage();
    }
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
 *Create a database in MySQL
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
 *Build a table in MySQL
 * @param mysqli $con
 * @param string $table_name
 */
function buildTable($con,$table_name)
{

    $sql = "        
        CREATE TABLE IF NOT EXISTS $table_name(
          id int(6) unsigned NOT NULL AUTO_INCREMENT,
          name varchar(20) NOT NULL,
          surename varchar(20) NOT NULL,
          email varchar(50) NOT NULL,
          PRIMARY KEY (id),
          UNIQUE KEY (email)
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
 * @param string $table_name
 */
function insertValues($con, $d_list, $table_name)
{
    try{
    foreach ($d_list as $value) {
        $stmt = $con->prepare(/** @lang text */
            "INSERT INTO $table_name(name, surename, email) VALUES (?,?,?)");
        $stmt->bind_param('sss', $value[0], $value[1], $value[2]);
        $success = $stmt->execute();
    }
    if ($success) {
        echo "Succeeded to insert Values.";
    } else {
        echo "Failed to insert Values.";
    }}catch (Exception $exception){
        echo $exception->getMessage();
    }
}

/**
 * Sent information to help
 */
function sendHelpMessages(){
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


//Define all the variables of command line
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

$file = isset($options['file']) ? $options['file'] : 'users.csv';
$createTable = isset($options['create_table']);
$dryRun = isset($options['dry_run']);
$help = isset($options['help']);
$username = isset($options['u']) ? $options['u'] : 'root';
$password = isset($options['p']) ? $options['p'] : '';
$host = isset($options['h']) ? $options['h'] : 'localhost';
$db_name="my_db";
$table_name = "users";

//var_dump($file, $createTable, $dryRun, $help, $username, $password, $host);

//Send messages for help if needed.
if ($help){
    sendHelpMessages();
}
//Create a array to read information from the file
$data_list = openAndReadFile($file);
//Filter the values according to the requirements
$data_list = dataFilter($data_list);
//Validate the email address according to the requirements
$data_list = emailValidate($data_list);
//Connect to the database
$conn = getDBConnection($host, $username, $password);
//Create a DB in MySQL
createDB($conn);
mysqli_select_db($conn, $db_name);
// Get permission before creating a table
fwrite(STDOUT, "Want to create a table? (T/F)");
// get input from the user
if(trim(fgets(STDIN))=='T'){
    $createTable = true;
    buildTable($conn,$table_name);
}
else {
    $createTable = false;
    echo "Thank you for using my program! \n";
}
//If --dry_run is false, then inserts values into the table.
if (!$dryRun) {
    insertValues($conn, $data_list, $table_name);
}
mysqli_close($conn);




