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

    if($words[0]==""){ // we are not working with empty lines
        break;
    }

    $xml->startElement("instruction");
    $xml->writeAttribute("order",$i);
    $xml->writeAttribute("opcode",$words[0]);

    switch($words[0]){
        case "BREAK":
        case "RETURN":
        case "CREATEFRAME":
        case "PUSHFRAME":
        case "POPFRAME":
            checkNumbArg($words,0);
            break;

        case "POPS":
        case "CALL":
        case "DEFVAR":
            checkNumbArg($words,1);

            createArgument($xml, "arg1", "var", $words[1]);
            break;

        case "WRITE":
        case "EXIT":
        case "DPRINT":
        case "PUSH":
            checkNumbArg($words,1);
            createArgument($xml, "arg1", "sym", $words[1]);
            break;

        case "LABEL":
        case "JUMP":
            checkNumbArg($words,1);
            createArgument($xml, "arg1", "label", $words[1]);
            break;

        case "READ":
        case "STRLEN":
        case "TYPE":
        case "MOVE":
            checkNumbArg($words,2);
            createArgument($xml, "arg1", "var", $words[1]);
            createArgument($xml, "arg2", "sym", $words[2]);
            break;

        case "ADD" :
        case "SUB":
        case "MUL":
        case "IDIV":
        case "LT":
        case "GT":
        case "EQ":
        case "AND":
        case "OR":
        case "NOT":
        case "STRI2INT":
        case "CONCAT":
        case "GETCHAR":
        case "SETCHAR":
            checkNumbArg($words,3);
            createArgument($xml, "arg1", "var", $words[1]);
            createArgument($xml, "arg2", "sym", $words[2]);
            createArgument($xml, "arg3", "sym", $words[3]);
            break;

        case "JUMPIFEQ":
        case "JUMPIFNEQ":
            checkNumbArg($words,3);
            createArgument($xml, "arg1", "label", $words[1]);
            createArgument($xml, "arg2", "sym", $words[2]);
            createArgument($xml, "arg3", "sym", $words[3]);
            break;
        default:
           // exit(22);
    }
    $xml->endElement(); // instruction element end
}

$xml->endDocument(); // program element end

// ************** MAIN FUNCTION END **************

function getArgType($arg){
    if(str_contains($arg,"string@")){
        return "string";
    }
    elseif(str_contains($arg,"int@")){
        return "int";
    }
    elseif(str_contains($arg,"bool@")){
        return "bool";
    }
    elseif(str_contains($arg,"nil@")){
        return "nil";
    }
    elseif(str_contains($arg,"LF@")) {
        return "var";
    }
    elseif(str_contains($arg,"GF@")) {
        return "var";
    }
    else{
        return "label";
    }
}

function getContent($arg){
    if(str_contains($arg,"string@")){
        return substr($arg,7);
    }
    elseif(str_contains($arg,"int@")){
        return substr($arg,4);
    }
    elseif(str_contains($arg,"bool@")) {
        return substr($arg, 5);
    }
    elseif(str_contains($arg,"nil@")) {
        return "nil";
    }
    else{
        return $arg;
    }
}

/**Function check if the instruction has correct number of arguments*/
function checkNumbArg($words,$corrNum){
    if(count($words)-1!=$corrNum){
        exit(23);
    }
}

function checkTheType($argType,$content){
    if($argType == "label" && getArgType($content) == "label"){
        return;
    }
    if($argType != "sym" && $argType != getArgType($content)){
        exit(23);
    }
}

/**Function creates XML argument element*/
function createArgument ($xml, $argName, $argType, $content){
    checkTheType($argType,$content);

    if($argType == "sym"){
        $argType = getArgType($content);
    }

    if($argType=="" && getArgType($content) == "var"){
        $argType == "var";
    }

    $content = getContent($content);
    //if var type, then var content

    $xml->startElement($argName);
    $xml->writeAttribute("type", $argType);
    xmlwriter_text($xml, $content);
    $xml->endElement();
}

/**Function will delete the comment, delete empty line and split the line to array*/
function lineToProperArray($line, $stdin): array
{
    $line = commentIgnore($line);
    $line=trim($line);
    //if its empty line, read the next line
    if($line == "" || $line == "\n"){
        $line = fgets($stdin);
        $line = commentIgnore($line);
    }
    $line=trim($line);
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
