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

for ($i=1;($line = fgets($stdin)) !== false;$i++) {

    $words = lineToProperArray($line, $stdin);

    if($words[0]==""){ // we are not working with empty lines
        $i--;
        continue;
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
        case "PUSHS":
            checkNumbArg($words,1);
            createArgument($xml, "arg1", "sym", $words[1]);
            break;

        case "LABEL":
        case "JUMP":
            checkNumbArg($words,1);
            createArgument($xml, "arg1", "label", $words[1]);
            break;

        case "READ":
            checkNumbArg($words,2);
            createArgument($xml, "arg1", "var", $words[1]);
            createArgument($xml, "arg1", "type", $words[2]);
            break;
        case "STRLEN":
        case "TYPE":
        case "MOVE":
        case "NOT":
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
            exit(22);
    }
    $xml->endElement(); // instruction element end
}

$xml->endDocument(); // program element end
return 0;
// ************** MAIN FUNCTION END **************

/** This function will get the argument type from argument with @. */
function getArgType($arg){
    $arg = strtolower($arg);

    if(strpos($arg,"string@") === 0){
        return "string";
    }
    elseif(strpos($arg,"int@") === 0){
        return "int";
    }
    elseif(strpos($arg,"bool@") === 0){
        return "bool";
    }
    elseif(strpos($arg,"nil@") === 0){
        return "nil";
    }
    elseif(strpos($arg,"lf@") === 0) {
        return "var";
    }
    elseif(strpos($arg,"gf@") === 0) {
        return "var";
    }
    elseif(strpos($arg,"tf@") === 0) {
        return "var";
    }
    else{
        return "label";
    }
}

/** This function will cut out the type from and argument and return the content only. */
function getContent($arg){
    $arg = strtolower($arg);

    if(strpos($arg,"string@") === 0){
        return substr($arg,7);
    }
    elseif(strpos($arg,"int@") === 0){
        if(!ctype_digit(substr($arg,4))){
            exit(23);
        }
        return substr($arg,4);
    }
    elseif(strpos($arg,"bool@") === 0) {
        return substr($arg, 5);
    }
    elseif(strpos($arg,"nil@") === 0) {
        if(substr($arg, 4) != "nil"){ //only value for nil is nil
            exit(23);
        }
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

/** This function check various problem with types. */
function checkTheType($argType,$content){

    if($argType == "var"){      //var cant start with 0 or /
         if(strpos($content,"0") === 3 || strpos($content,"/") === 3){
             exit(23);
         }
    }

    if (substr_count($content, "@") > 1) { // there cant be more than 1 @ in argument
        exit(23);
    }

    if($argType == "type"){ //type can contain only these three value
        $content = strtoupper($content);
        if($content != "INT" && $content != "STRING" && $content != "BOOL"){
            exit(23);
        }
        else{
            return;
        }
    }
    if($argType == "label" && getArgType($content) == "label"){
        return;
    }
    if($argType != "sym" && $argType != getArgType($content)){
        exit(23);
    }
    if($argType == "sym" && (strpos($content,"@") == false)){ //if its label in sym then exit
        exit (23);
    }
}

/**Function creates XML argument element*/
function createArgument ($xml, $argName, $argType, $content){
    checkTheType($argType,$content);

    if($argType == "sym"){
        $argType = getArgType($content);
    }

    if($argType == "type"){         // content of type type variable change the type of the variable
        $argType = $content;
    }

    $content = getContent($content);

    $xml->startElement($argName);
    $xml->writeAttribute("type", $argType);
    xmlwriter_text($xml, $content);
    $xml->endElement();
}

function deleteWhiteSpaces($input){
    return preg_replace('/\s+/', ' ',$input);
}

/**Function will delete the comment, delete empty line and split the line to array*/
function lineToProperArray($line, $stdin)
{
    $line = commentIgnore($line);
    $line = trim($line);
    $line = deleteWhiteSpaces($line);

    //if its empty line, read the next line
    if($line == ""){
        $line = fgets($stdin);
        $line = commentIgnore($line);
        $line = trim($line);
        $line = deleteWhiteSpaces($line);
    }

    $line = htmlspecialchars($line);

    $words = explode(" ",$line);
    $words[0] = strtoupper($words[0]);

    return $words;
}

/**Function check if stdin has the proper header*/
function checkTheHeader($stdin){
    $words = lineToProperArray(fgets($stdin),$stdin);

    if(count($words)>1){
        exit(21);
    }

    if ($words[0] == ".IPPCODE22") {
        return;
    }

    while($words[0] == "\n" || $words[0] == "") {
        $words = lineToProperArray(fgets($stdin),$stdin);
        if ($words[0] == ".IPPCODE22") {
            return;
        }
    }

    exit (21);
}

/**Function set the necessary XML properties and write the XML header.*/
function setStartXML($xml){
    $xml -> openURI("php://stdout");
    xmlwriter_set_indent($xml, "1");
    xmlwriter_set_indent_string($xml, " ");
    $xml->startDocument('1.0','utf-8');
}

/**Function handle all possible arguments from terminal.*/
function processArguments($argc,$argv){
    if($argc>2 || ($argc == 2 && $argv[1] != "--help")){
        exit(10);
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
function commentIgnore($line)
{
    $i=0;
    $commentFreeLine="";

    if(!$line){
        return $commentFreeLine; //explode function returns error with empty string
    }

    $letter = $line[0];

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
    echo "Skript typu filtr (parse.php v jazyce PHP 8.1) nacte ze standardniho vstupu zdrojovy kod v IPPcode22, zkontroluje lexikalni a syntaktickou spravnost kodu a vypise na standardni vystup XML reprezentaci programu.

Tento skript pracuje s temito parametry:
* --help vypise tuto napovedu.

Chybove navratove kody specificke pro analyzator:
* 21 - chybna nebo chybejici hlavicka ve zdrojovem kodu zapsanem v IPPcode22;
* 22 - neznamy nebo chybny operacni kod ve zdrojovem kodu zapsanem v IPPcode22;
* 23 - jina lexikalni nebo syntakticka chyba zdrojoveho kodu zapsaneho v IPPcode22.\n";
}