<?php 
    $myfile = fopen("logs.txt", "w") or die("Unable to open file!");
    $txt = "user id date5";
    fwrite($myfile, $txt."\n");
    fclose($myfile);
?>