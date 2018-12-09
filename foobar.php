<?php
/**
 * Output the numbers from 1 to 100;
 * Where the number is divisible by three (3) output the word “foo”;
 * Where the number is divisible by five (5) output the word “bar”；
 * Where the number is divisible by three (3) and (5) output the word “foobar”;
 * User: Na Zhang
 * Date: 2018-12-10
 * Time: 11:38 AM
 */
for ($i=1;$i<=100; $i++){

    switch($i)
    {
        case ($i%15==0):
            echo "foobar" . ", ";
            break;

        case ($i%3==0):
            echo "foo" . ", ";
            break;

        case ($i%5==0):
            echo "bar".", ";
            break;

        default:
            echo $i.", ";
    }
}