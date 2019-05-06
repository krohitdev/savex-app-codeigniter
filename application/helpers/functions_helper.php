<?php
	define("Status","status",true);
    define("Message","message",true);
    define("Success","success",true);
    define("Failure","error",true);
    define("DataFound","Data Found",true);
    define("NoDataFound","No Data Found",true);
	
	 /**
    *  Convert json response to $array
    *
    * @param array $arr response data  
    */
	function apiResponse($arr){
    	header('Content-type: application/json; charset=utf-8');
		echo json_encode($arr);
    }
	
	
	function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        
        $charactersLength = strlen($characters);
        
        $randomString = '';
        
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        
		return $randomString;
    }
	
	function in_array_r($item , $array){
		return preg_match('/"'.preg_quote($item, '/').'"/i' , json_encode($array));
	}
	
	
?>