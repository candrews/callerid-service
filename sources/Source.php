<?php
if (!defined('CALLERID')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

abstract class Source
{

    static $npalistUS = array(
	    '201', '202', '203', '205', '206', '207', '208', '209', '210', '212',
	    '213', '214', '215', '216', '217', '218', '219', '224', '225', '228',
	    '229', '231', '234', '239', '240', '242', '246', '248', '251', '252',
	    '253', '254', '256', '260', '262', '264', '267', '268', '269', '270',
	    '276', '281', '284', '301', '302', '303', '304', '305', '307', '308',
	    '309', '310', '312', '313', '314', '315', '316', '317', '318', '319',
	    '320', '321', '323', '325', '330', '331', '334', '336', '337', '339',
	    '340', '343', '345', '347', '351', '352', '360', '361', '386', '401', '402',
	    '404', '405', '406', '407', '408', '409', '410', '412', '413', '414',
	    '415', '417', '419', '423', '424', '425', '430', '432', '434', '435',
	    '440', '441', '443', '456', '469', '473', '478', '479', '480', '484',
	    '500', '501', '502', '503', '504', '505', '507', '508', '509', '510',
	    '512', '513', '515', '516', '517', '518', '520', '530', '540', '541',
	    '551', '559', '561', '562', '563', '567', '570', '571', '573', '574',
	    '575', '580', '585', '586', '600', '601', '602', '603', '605', '606',
	    '607', '608', '609', '610', '612', '614', '615', '616', '617', '618',
	    '619', '620', '623', '626', '630', '631', '636', '641', '646', '649',
	    '650', '651', '660', '661', '662', '664', '670', '671', '678', '682',
	    '684', '700', '701', '702', '703', '704', '706', '707', '708', '710',
	    '712', '713', '714', '715', '716', '717', '718', '719', '720', '724',
	    '727', '731', '732', '734', '740', '754', '757', '758', '760', '762',
	    '763', '765', '767', '769', '770', '772', '773', '774', '775', '779',
	    '781', '784', '785', '786', '787', '801', '802', '803', '804', '805',
	    '806', '808', '809', '810', '812', '813', '814', '815', '816', '817',
	    '818', '828', '829', '830', '831', '832', '843', '845', '847', '848',
	    '850', '856', '857', '858', '859', '860', '862', '863', '864', '865',
	    '868', '869', '870', '876', '878', '900', '901', '903', '904', '906',
	    '907', '908', '909', '910', '912', '913', '914', '915', '916', '917',
	    '918', '919', '920', '925', '928', '931', '936', '937', '939', '940',
	    '941', '947', '949', '951', '952', '954', '956', '970', '971', '972',
	    '973', '978', '979', '980', '985', '989',
	    '800', '866', '877', '888'
    );

    static $npalistCAN = array(
	    '204', '226', '249', '250', '289', '306', '343', '365', '403', '416', '418', '438', '450',
	    '506', '514', '519', '579', '581', '587', '604', '613', '647', '705', '709',
	    '778', '780', '807', '819', '867', '873', '902', '905',
	    '800', '866', '877', '888'
      );


    public $run_param;
    
    function get_configuration()
    {
        return array();
    }
    
    abstract function lookup();
    
    function prepare(){
        return true;
    }
    
    /**
     * given a block of messy, scraped html contains tags, escape sequences, etc, return a cleaned up string ready to be displayed to the user
     * 
     * if the parameter is empty, return null
     * removes multiple white spaces
     * replaces new line with spaces, 
     * convert html entities to unicode characters
     * strips html tags
     * trim leading/trailing white space
     */
    function clean_scraped_html($html){
        if(empty($html))
            return null;
        else
            $ret = trim(preg_replace('/\s+/',' ',html_entity_decode(strip_tags($html),ENT_QUOTES,'UTF-8')));
            if(empty($ret)){
                return null;
            }else{
                return $ret;
            }
    }
    
    /**
     * given a phone number in any format (with any punctuation), return the 10 digit phone number (or false if the number is not valid)
     */
    function clean_us_number($number){
        return clean_uscan_number($number, 'US');
    }
    
    /**
     * given a phone number in any format (with any punctuation), return the 10 digit phone number (or false if the number is not valid)
     */
    function clean_can_number($number){
        return clean_uscan_number($number, 'CAN');
    }
    
    /**
     * given a phone number in any format (with any punctuation), return the 10 digit phone number (or false if the number is not valid)
     */
    function clean_uscan_number($number, $country='USCAN'){
        $number = preg_replace('/[\D]/','',$number);
        switch(strlen($number)){
            case 10:
	            $pattern = '/(\d{3})(\d{3})(\d{4})/';
                break;
            case 11:
	            $pattern = '/1(\d{3})(\d{3})(\d{4})/';
                break;
            case 13:
	            $pattern = '/001(\d{3})(\d{3})(\d{4})/';
                break;
            case 14:
	            $pattern = '/0111(\d{3})(\d{3})(\d{4})/';
                break;
            default:
                return false;
        }
	    if(preg_match($pattern, $number, $matches)){
	        $npa = $matches[1];
	        $nxx = $matches[2];
	        $station = $matches[3];
	        switch($country){
	            case 'US':
	                if(in_array($npa, self::$npalistUS)){
	                    return $npa.$nxx.$station;
                    }else{
                        return false;
                    }
	                break;
	            case 'CAN':
	                if(in_array($npa, self::$npalistCAN)){
	                    return $npa.$nxx.$station;
                    }else{
                        return false;
                    }
	                break;
	            case 'USCAN':
	                if(in_array($npa, array_merge(self::$npalistUS,self::$npalistCAN))){
	                    return $npa.$nxx.$station;
                    }else{
                        return false;
                    }
	                break;
                default:
                    return false;
	        }
	    }else{
	        return false;
        }
    }
    
    /**
     * given a phone number in any format (with any punctuation), return the phone number, with leading 0 as dialed in Sweden (or false if the number is not valid)
     */
    function clean_se_number($number){
        $number = preg_replace('/[\D]/','',$number);
	    //check for the correct digits for Sweden in international format.
	    // international dialing prefix + country code + number
	    if (strlen($number) > 8)
	    {
		    if (substr($number,0,2) == '46')
		    {
			    $number = '0'.substr($number, 2);
		    }
		    else
		    {
			    if (substr($number,0,4) == '0046')
			    {
				    $number = '0'.substr($number, 4);
			    }
			    else
			    {
				    if (substr($number,0,5) == '01146')
				    {
					    $number = '0'.substr($number,5);
				    }			
				    else
				    {
					    return false;
				    }
			    }
		    }
	    }	
	    // number
        if(strlen($number) < 11 && substr($number,0,1) == '0')
	    {
		    return $number;
	    }
	    else
	    {
		    return false;
	    }
    }
    
    /**
     * given a phone number in any format (with any punctuation), return the phone number, with leading 0 as dialed in Australia (or false if the number is not valid)
     */
    function clean_au_number($number){
        $number = preg_replace('/[\D]/','',$number);
        switch(strlen($number)){
            case 10:
	            $pattern = '/(0\d{9})/';
                break;
            default:
                return false;
        }
	    if(preg_match($pattern, $number, $matches)){
	        return $matches[1];
	    }else{
	        return false;
        }
    }
}

