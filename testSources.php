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
        /*
            array('AddressesSource', '+17817498755', 'us', 'Lynn D Donovan', '36 Myers Farm Rd, Hingham, MA 02043'),
            array('AddressesYellowPagesSource', '+18604297433', 'us', 'Willington Pizza House', 'Route 32, Willington, CT 06279'),
            array('CanpagesSource', '+15147371111', 'ca', 'Pizza Pizza', '5184 av du Parc, Montréal, QC'),
            array('CitizensInfoSource', '+12169215024', 'us', 'Able Dixon', '3259 Van Aken Blvd, Shaker Heights, OH 44120'),
            array('CountrySource', '+17817498755', 'us', 'Unknown (United States)', null),
            array('FonectaSource', '+35897012298', 'fi', 'Pizza-Kebab Apollo', 'Vaasankatu 8, 00500 HELSINKI'),
            array('HittaSource', '+468291229', 'se', 'John Doe MC', 'Vidjavägen 2 123 52 Farsta'),
            array('InfobelSource', '+390744433549', 'it', 'ALBERGO RISTORANTE LOCANDA DEL VECCHIO MAGLIO (CENTRALINO ALBERGO RISTORANTE)', 'VLE BRIN 148, 05100 TERNI'),
            array('InfobelSource', '+493041726953', 'de', 'Jones Brigitte', 'Trautenaustr. 11, 10717 Berlin'),
            array('LocalTelSource', '+41264244141', 'ch', 'A-Allô Pizza', 'route de la Glâne 7, 1700 Fribourg'),
            array('PagineBiancheSource', '+390697605917', 'it', 'AL CASALE PIZZERIA - Di ALESSIO E NOEMI TRIBINI Snc', 'Via Rocca Fiorita 155 - 00133 Valle Fiorita (RM)'),
            array('PagineBiancheSource', '+390521830398', 'it', 'Jones Alan', 'Via Calestano 202 - 43035 Felino (PR)'),
            array('PersonLookupSource', '+61393952708', 'au', 'Jones A', '12 Caledonian Way, Point Cook, VIC 3030'),
            array('TelcoDataSource', '+13137370000', 'us', 'DETROITZN5, MI', 'DETROITZN5, MI'),
            array('UkPhoneInfoSource', '+442075891278', 'uk', 'London', 'London'),
            array('WhitePagesSource', '+17817498755', 'ca', 'Lynn D Donovan', 'Myers Farm Rd, Hingham, MA'),
            array('YellowPagesBusinessSource', '+18604297433', 'us', 'Willington Pizza House', 'Route 32, Willington, CT 06279'),
            */
            array('YellowPagesPersonSource', '+17817498755', 'us', 'Lynn D Donovan', '36 Myers Farm Rd, Hingham, MA 02043'),
            array('YellowPagesPersonSource', '+12016608433', 'us', 'Jae L Choi', '119 Bogerts Mill Rd, Harrington Park, NJ 07640'),
            //array('YellowPages_PTSource', '+351219560449', 'pt', 'Xou Pizza-Pizzaria e Churrasqueira Sociedade Lda', 'Avenida Ernest Solvay 5-lj 5, 2625-168 PÓVOA DE SANTA IRIA')
        );
    }
    
    /**
     * @dataProvider provider
     */
    public function testLookup($sourceName, $thenumber, $country, $expectedName, $expectedAddress){
        $source = new $sourceName();
        $source->thenumber = $thenumber;
        $source->country = $country;
        $this->assertTrue($source->prepare());
        $result = $source->lookup();
        $this->assertTrue($result!==false);
        $this->assertEquals($expectedName, $result->name);
        $this->assertEquals($expectedAddress, $result->address);
    }
}

