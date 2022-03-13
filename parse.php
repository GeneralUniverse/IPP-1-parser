<?php

//******* MAIN FUNCTION START **********

ini_set('display_errors', 'stderr');

processArguments($argc,$argv);

$stdin = fopen("php://stdin", "r");
checkTheSTDIN($stdin);

$xml = new XMLWriter();
setStartXML($xml);

//start of the parsing
checkTheHeader($stdin);

$xml->startElement("program");
$xml->writeAttribute("language","IPPcode22");

for ($i=0;($line = fgets($stdin)) !== false;$i++) {

    $words = lineToProperArray($line, $stdin);

    $xml->startElement("instruction");
    $xml->writeAttribute("order",$i);
    $xml->writeAttribute("opcode",$words[0]);
    createArgument($xml,"arg1",getArgType($words,1),$words[1]);
    switch($words[0]){
        case "DEFVAR":
            createArgument($xml,"arg1",getArgType($words,1),"");
            break;
    }

    $xml->endElement(); // instruction element end
}

if (!feof($stdin)) {
    echo "Error: unexpected fail\n";
}
$xml->endDocument(); // program element end

// ************** MAIN FUNCTION END **************

function getArgType($words,$argNumber){
    if($words[0] == "LABEL"){
        return "label";
    }
    $arg = $words[$argNumber];
    if(strpos($arg,"@")){
        if(strpos($arg,"string@")){
            return "string";
        }
        else{
            return "var";
        }
    }
}

function createArgument ($xml,$argName,$argType,$content){
    $xml->startElement($argName);
    $xml->writeAttribute("type",$argType);
    xmlwriter_text($xml, $content);
    $xml->endElement();
}

/**Function will delete the comment, delete empty line and split the line to array*/
function lineToProperArray($line, $stdin): array
{
    $line = commentIgnore($line);

    //if its empty line, read the next line
    if($line == ""){
        $line = fgets($stdin);
        $line = commentIgnore($line);
    }

    $words = explode(" ",$line);
    $words[0] = strtoupper($words[0]);

    return $words;
}

/**Function check if stdin has the proper header*/
function checkTheHeader($stdin){
    if(fgets($stdin)!=".IPPcode22\n"){
        exit("21\n");
    }
}

/**Function set the necessary XML properties and write the XML header.*/
function setStartXML($xml){
    $xml->openUri('php://output');
    xmlwriter_set_indent($xml, 4);
    xmlwriter_set_indent_string($xml, "\t");
    $xml->startDocument('1.0','utf-8');
}

/**Function handle all possible arguments from terminal.*/
function processArguments($argc,$argv){
    if($argc>2 || ($argc == 2 && $argv[1] != "--help")){
        exit("Error: Not valid arguments.\n");
    }

    if($argc == 2 && $argv[1] == "--help"){
        printHelp();
        exit;
    }
}

/**Function handle all possible stdin inputs*/
function checkTheSTDIN($stdin){
    if (!$stdin) {
        exit("Error: Stdin opening has failed.");
    }

    $read=array($stdin);$write = NULL;$except = NULL;
    if( stream_select( $read, $write, $except, 0 ) !== 1){
        exit("Error: No data on stdin.\n");
    }
}

/**Function delete comments (everything after "#" )*/
function commentIgnore($line): string
{
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

/**Function prints help for users.*/
function printHelp(){
    echo "POMOOOOOC\n";
}
