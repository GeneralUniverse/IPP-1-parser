<?php

//******* MAIN FUNCTION START **********

//arguments handle
if($argc>2 || ($argc == 2 && $argv[1] != "--help")){
    exit("Error: Not valid arguments.\n");
}

if($argc == 2 && $argv[1] == "--help"){
    printHelp();
    exit;
}

//stdin handle
if (!$stdin = fopen("php://stdin", "r")) {
    exit("No data on stdin.");
}

if(fgets($stdin)!=".IPPcode22\n"){
    exit("Syntax error: Missing header.\n");
}

while (($line = fgets($stdin)) !== false) {
    $line = commentIgnore($line);
    echo $line;
//    $words = explode(" ",$line);
//    echo $words[0];
}

if (!feof($stdin)) {
    echo "Error: unexpected fail\n";
}

// ************** MAIN FUNCTION END **************

function commentIgnore($line){
    $i=0;
    $commentFreeLine="";
    $letter=$line[0];
    while($letter!='#' && $i<strlen($line)){
        $letter=$line[$i];
        if($letter=='#'){
            $commentFreeLine=$commentFreeLine."\n";
            break;
        }
        $commentFreeLine=$commentFreeLine.$letter;
        $i++;
    }
    return $commentFreeLine;
}
function printHelp(){
    echo "POMOOOOOC\n";
}
