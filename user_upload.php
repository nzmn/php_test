<?php
/**
 * Part 2: Programming task
 * Author: Na Zhang
 * Date: 2018-12-09
 * Time: 11:01 PM
 */


$path = 'users.csv';
$data_list = [];
$row=0;
if (($handle = fopen($path, "r"))) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

        if($row==0){
            $row++;
            continue;
        }
            $data_list[$row]['name']=$data[0];
            $data_list[$row]['surename']=$data[1];
            $data_list[$row]['email']=$data[2];
            $row++;
    }
    var_dump($data_list);
    fclose($handle);
}
else{
    die("File not find!");
}




