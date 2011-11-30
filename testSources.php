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
            array('AddressesSource', '+17817498755', 'us', 'Lynn D Donovan', '36 Myers Farm Rd, Hingham, MA 02043'),
            array('AddressesYellowPagesSource', '+18604297433', 'us', 'Willington Pizza House', 'Route 32, Willington, CT 06279'),
            //array('CanpagesSource', '+15147371111', 'ca', 'Pizza Pizza', '5184 av du Parc, Montréal, QC'),
            array('CitizensInfoSource', '+12169215024', 'us', 'Able Dixon', '3259 Van Aken Blvd, Shaker Heights, OH 44120'),
            array('CountrySource', '+17817498755', 'us', 'Unknown (United States)', null),
            array('FonectaSource', '+35897012298', 'fi', 'Pizza-Kebab Apollo', 'Vaasankatu 8, 00500 HELSINKI'),
            //array('GoogleSource', '', '', '', ''),
            array('HittaSource', '+468291229', 'se', 'John Doe MC', 'Vidjavägen 2 123 52 Farsta'),
            array('InfobelSource', '+390744433549', 'it', 'ALBERGO RISTORANTE LOCANDA DEL VECCHIO MAGLIO (CENTRALINO ALBERGO RISTORANTE)', 'VLE BRIN 148, 05100 TERNI'),
            array('InfobelSource', '+493041726953', 'de', 'Jones Brigitte', 'Trautenaustr. 11, 10717 Berlin'),
            //array('LocalTelSource', '', '', '', ''),
            //array('PagineBiancheSource', '', '', '', ''),
            //array('PersonLookupSource', '', '', '', ''),
            array('TelcoDataSource', '+13137370000', 'us', 'DETROITZN5, MI', 'DETROITZN5, MI'),
            //array('UkPhoneInfoSource', '', '', '', ''),
            //array('WhitePagesCanadaSource', '', '', '', ''),
            //array('WhitePagesSource', '', '', '', ''),
            //array('YellowPagesBusinessSource', '', '', '', ''),
            //array('YellowPagesPersonSource', '', '', '', ''),
            //array('YellowPages_PTSource', '', '', '', '')
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

