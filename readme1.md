**Implementační dokumentace k 1. úloze do IPP 2021/2022**\
**Jméno a příjmení: Dominik Klon**\
**Login: xklond00**

## Návrh

Návrhem programu bylo vytvořit co nepřehlednější a nejlépe rozšiřitelný parser, který převádí jazyk IPPcode22 na XML reprezentaci. Základem měl být jeden velký switch pro instrukce, které budou vykonávat funkce na základě daných argumentů. 

## Interní reprezentace

Program je rozdělen na více funkcí s cílem dosáhnout co největší přehlednosti.
Začíná s funkcí, která přečte a ošetří vstup z terminálových argumentů `processArguments($argc,$argv)`.
Další funkce `checkTheSTDIN($stdin)` ověřuje vstup z STDIN, protože s prázdným STDINem by se funkce zacyklila. 
Poté už se definuje nový objekt třídy **XMLWriter**, kterou používám pro jednodušší práci s XML. Nastaví se všechny potřebné atributy pro objekt a to ve funkci `setStartXML($xml)`. 
Následuje již přímo začátek syntaktické analýzi a to funkcí `checkTheHeader($stdin)`, která ověří, zdali je před všemi instrukcemi napsána hlavička.
Poté už následuje základní cyklus programu a to je postupné čtení každého řádku STDIN a práce s ním. 
Jako první je třeba řádek rozdělit na validní pole. Proto je volána funkce `lineToProperArray($line, $stdin)`, která řádek zbaví přebytečných mezer, komentářů, přeskočí prázdné řádky, zbaví se v XML nevhodných znaků a přepíše je na escape sekvence a řádek poté rozdělí na pole, které vrací. 
Poté již přecházíme na základní složku syntaktické analýzi a to je podmíněný příkaz `switch` se všemi instrukcemi, které může parser zpracovat. 
V každém `case` tohoto příkazu se jako první kontroluje počet přijatých argumentů. Ten je pro každou skupinu příkazů specifický. Od tohoto čísla se dále odvíjí i počet zavolání funkce `createArgument($xml, "arg1", "var", $words[1]);`, která tvoří XML argumenty a s pomocí dalších funkcí provadí všechny potřebné kontroly a přetypování. Tímto končí hlavní funkce a XML dokument se uzavírá a posílá na STDOUT.

##  Postup řešení
Jako první jsem si otestoval funkčnost STDIN a jeho procházení v PHP. Poté jsem navrhl hlavní for smyčku, která postupně projde všechny řádky STDINu a do ní postavil zbytek programu. Dále jsem si vyzkoušel funkcionalitu XMLWriteru, který mi přišel jako nejvhodnější názor pro tvorbu XML.
Napsal jsem switch s úmyslem do každého casu psát zvlášťní kód, ale rychle mi došlo, že to nebude potřeba, jelikož ke každé instrukci se přistupovalo téměř totožně. Proto jsem vytvořil hlavní opěrnou funkci `createArgument($xml, "arg1", "var", $words[1]);`, která tvoří argumenty se všemi potřebnými ošetřeními. Pozděj jsem zjistil, že funkce je příliš složitá a rozdělil ji na více častí. Pro argumenty s typem symbol jsem vytvořil funkci `getArgType($arg)`, která vrátí typ a pro vracení obsahu bez typových předpon funkci ` getContent($arg)`. Funkce `checkTheType($argType,$content)` měla původně kontrolovat pouze správně zadané typy, ale nakonec jsem do ní přidal několik dalších podmínek, které jsem vyčetl ze zadání. Funkce `lineToProperArray($line, $stdin)` původně pouze rozdělovala řádek do pole a odstraňovala komentáře, nicméně postupně jsem do ní přidal mnohem více oštření včetně přeskakování prázdných řádků. 
Všechny ošetření jsem bohužel nestihl implementovat.