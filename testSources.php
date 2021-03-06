<?php
define('CALLERID',true);
define('INSTALLDIR', dirname(__FILE__));

require_once(INSTALLDIR . '/lib/StringFunctions.php');

function test_autoload($class_name) {
    if(endswith($class_name,'NumberCleaner') && $class_name!='NumberCleaner' && file_exists(INSTALLDIR . '/number_cleaners/' . strtolower(substr($class_name,0,-13)) .'.php')){
        require_once(INSTALLDIR . '/number_cleaners/' . strtolower(substr($class_name,0,-13)) .'.php');
    }else if(endswith($class_name,'Source') && file_exists(INSTALLDIR . "/sources/$class_name.php")){
        require_once(INSTALLDIR . "/sources/$class_name.php");
    }else if(file_exists(INSTALLDIR . "/lib/$class_name.php")){
        require_once(INSTALLDIR . "/lib/$class_name.php");
    }else{
        Event::handle('Autoload', array(&$class_name));
    }
}
spl_autoload_register('test_autoload');

class SourcesTest extends PHPUnit_Framework_TestCase
{
    public function provider(){
        return array(
            array('LibphonenumberSource', '+17817498755', 'us', 'Hingham, MA', 'Hingham, MA'),
            array('AddressesSource', '+17817498755', 'us', 'Lynn D Donovan', '36 Myers Farm Rd, Hingham, MA 02043'),
            array('AddressesSource', '+18002927508', 'us', null),
            array('AddressesYellowPagesSource', '+18604297433', 'us', 'Willington Pizza House', '25 River Rd, Willington, CT 06279'),
            array('AddressesYellowPagesSource', '+18002927508', 'us', null),
            array('CanpagesSource', '+18002927508', 'ca', null),
            array('CitizensInfoSource', '+12169215024', 'us', 'Able Dixon', '3259 Van Aken Blvd, Shaker Heights, OH 44120'),
            array('CitizensInfoSource', '+18002927508', 'us', null),
            array('CountrySource', '+17817498755', 'us', 'Unknown (United States)', null),
            array('FonectaSource', '+35897012298', 'fi', 'Pizza-Kebab Apollo', 'Vaasankatu 8, 00500 HELSINKI'),
            array('FonectaSource', '+35896012279', 'fi', null),
            array('HittaSource', '+468291229', 'se', 'John Doe MC', 'Vidjavägen 2 12352 FARSTA'),
            array('HittaSource', '+468252227', 'se', null),
            array('InfobelSource', '+390744433549', 'it', 'ALBERGO RISTORANTE LOCANDA DEL VECCHIO MAGLIO (CENTRALINO ALBERGO RISTORANTE)', 'VLE BRIN 148, 05100 TERNI'),
            array('InfobelSource', '+492234497590', 'de', '1A Pizza Colonia', 'Aachener Str. 1169, 50858 Köln'),
            array('InfobelSource', '+493041726954', 'de', null),
            array('LocalTelSource', '+41264244141', 'ch', 'A-Allô Pizza', 'route de la Glâne 7, 1700 Fribourg'),
            array('LocalTelSource', '+41254254141', 'ch', null),
            array('PagineBiancheSource', '+390697605917', 'it', 'AL CASALE PIZZERIA - Di ALESSIO E NOEMI TRIBINI Snc', 'V. Rocca Fiorita 155 - 00133 Roma (RM)'),
            array('PagineBiancheSource', '+390521830398', 'it', 'JONES ALAN', 'Via Calestano 202 - 43035 Felino (PR)'),
            array('PagineBiancheSource', '+390522730498', 'it', null),
            array('PersonLookupSource', '+61295231015', 'au', 'Burnell-Jones C', '21 Berry St Cronulla NSW 2230'),
            array('PersonLookupSource', '+61393962708', 'au', null),
            array('TelcoDataSource', '+13137370000', 'us', 'DETROITZN5, MI', 'DETROITZN5, MI'),
            array('TelcoDataSource', '+18002927508', 'us', null),
            array('UkPhoneInfoSource', '+442074862080', 'uk', 'London', 'London'),
            array('UkPhoneInfoSource', '+442175891278', 'uk', null),
            array('WhitePagesSource', '+17817498755', 'ca', 'Lynn D Donovan', 'Avalon Dr, Cohasset, MA'),
            array('WhitePagesSource', '+18002927508', 'ca', null),
            array('YellowPagesBusinessSource', '+18604297433', 'us', 'Willington Pizza House', '25 River Rd, Willington, CT 06279'),
            array('YellowPagesPersonSource', '+17817498755', 'us', 'Lynn D Donovan', '36 Myers Farm Rd, Hingham, MA 02043'),
            array('YellowPagesPersonSource', '+12016608433', 'us', 'Jae L Choi', '119 Bogerts Mill Rd, Harrington Park, NJ 07640'),
            array('YellowPagesPersonSource', '+18002927508', 'us', null),
            array('YellowPages_PTSource', '+351232415051', 'pt', 'Best Pizza Lda', 'Avenida 25 Abril 35-r/c, 3510-118 VISEU'),
            array('YellowPages_PTSource', '+351319570449', 'pt', null)
        );
    }
    
    /**
     * @dataProvider provider
     */
    public function testLookup($sourceName, $thenumber, $country, $expectedName, $expectedAddress=null){
        $source = new $sourceName();
        $source->thenumber = $thenumber;
        $source->country = $country;
        $this->assertTrue($source->prepare());
        $query_start_time = microtime(true);
        $result = $source->lookup();
        if($expectedName == null){
            $this->assertFalse($result);
        }else{
            $this->assertTrue($result!==false);
            $this->assertEquals($expectedName, $result->name);
            $this->assertEquals($expectedAddress, $result->address);
        }
        $query_time = (microtime(true) - $query_start_time) * 1000;
        //error_log("$sourceName: Query time (ms): $query_time");
    }
}

