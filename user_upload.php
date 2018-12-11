<?php
/**
 * Part 2: Programming task
 * Author: Na Zhang
 * Date: 2018-12-11
 */


/**
 *This function removes extra characters from both sides of a string,
 *capitalizes the first letter of the name and surname, and set the email address to lower case
 *
 * @param $dataArr
 * @return array
 */
function dataValidate($dataArr):array
{
    $new_datalist=[];
    for ($i=1;$i<=count($dataArr);$i++) {
        $new_datalist[$i]['name'] = ucfirst(strtolower (trim($dataArr[$i]['name'], " \t\n\r\0\!\ ")));
        $new_datalist[$i]['surename'] = ucfirst(strtolower (trim($dataArr[$i]['surename'], " \t\n\r \!\ ")));
        $new_datalist[$i]['email'] = strtolower (trim($dataArr[$i]['email'], " \t\n\r \!\ "));
    }
    return $new_datalist;
}

/**
 * This function remove invalid email addresses before insert these into the database
 *
 * @param $dataArr
 * @return array
 */
function emailValidate($dataArr):array{
    $new_datalist=[];
    for ($i=1;$i<=count($dataArr);$i++) {
        if(filter_var($dataArr[$i]['email'],FILTER_VALIDATE_EMAIL)) {
            $new_datalist[$i]['name'] = $dataArr[$i]['name'];
            $new_datalist[$i]['surename'] = $dataArr[$i]['surename'];
            $new_datalist[$i]['email'] = $dataArr[$i]['email'];
        }
        else{
            fwrite(STDOUT, $dataArr[$i]['name']." ".$dataArr[$i]['surename']." has an invalid email address. \n");
            continue;
        }
    }
    return $new_datalist;
}


    $path = 'users.csv';
    $row = 0;
    //Open the 'users.csv' file and sent an error message if this file does not exist.
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

    $data_list=dataValidate($data_list);

    $data_list=emailValidate($data_list);

    var_dump($data_list);






