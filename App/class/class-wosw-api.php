<?php 

/**
 * @package class-wosw-wosw-api.php
 * init settings for install shipping method
 **/
if( !class_exists('wosw_api') ){
  class wosw_api{

        public static $senderId      = '5f7d9adda9c4fb27d47eb748';
        public static $senderApiKey  = '7de06a85-cfda-48cb-9e29-39ffec9faea5';
        private   $request_url;
        public    const http_url      = 'https://dashboard.wassalnow.com/api/trips/';
        public    $wassal_params = array();
        protected $request_type  ='';
        

        /**
         * curl_api_connect function.
         *
         * @access public
         * @param mixed $package
         * @return void
         */ 

         function curl_api_connect(){
            try {
            
                  // cURL request
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL,self::http_url.$this->request_url);
                curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.52 Safari/537.17');
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                 "cache-control: no-cache",
                                   "Content-Type : application/".($this->request_type=='POST'?'x-www-form-urlencoded':'json') ) );
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $this->get_params());
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_AUTOREFERER, true); 
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($ch, CURLOPT_VERBOSE, 1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // this should be set to true in production
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST,$this->request_type);
                $responseData = curl_exec($ch);
                if(curl_errno($ch)) {
                  return curl_error($ch);
                }
                curl_close($ch);
                 $data = json_decode($responseData,true);
                 return $data;
              
            } catch (Exception $e) {
                return 'error '.$e->getMessage();
              
            }
         }


        /**
         * Track_a_Shipment function.
         *
         * @access public
         * @param mixed $package
         * @return void
         */ 
         function Create_a_New_Shipment($data = array() ){
             
             //check if data is sent correctly
             if(empty($data))
               return 'error';

            // here set url page in wassal
            $this->request_url = 'create-trip-by-params?access_token=1';

            //here set parameters sent in curl request
            $this->wassal_params = [
               'senderId'         =>self::$senderId,
               'senderApiKey'     =>self::$senderApiKey,
               'customerMobile'   =>$data['customerMobile'] ?? '',
               'customerName'     =>$data['customerName']   ?? '',
               'customerAddress'  =>$data['customerAddress']?? '',
               'customerCity'     =>$data['customerCity']   ?? '',
               'promoCode'        =>$data['promoCode']      ?? '',
               'COD'              =>$data['COD']            ?? '',
               'shipmentContents' =>$data['shipmentContents']  ?? '',
            ];

            //here set type of request get or post 
            $this->request_type = 'POST';

            //return $this->wassal_params;
            //return $this->curl_api_connect();

            $data = json_decode($this->curl_api_connect()['response'],true);

            // here sent curl request
            return $this->Get_ShipmentId_and_TrackNo($data);
            
         }

        /**
         * Track_a_Shipment function.
         *
         * @access public
         * @param mixed $package
         * @return void
         */ 
         function Track_a_Shipment($data = array()){
           
             //check if data is sent correctly
             if(empty($data))
               return 'error';

            // here set url page in wassal
            $this->request_url = 'checkStatus?access_token=1';

            //here set parameters sent in curl request
            $this->wassal_params = [
               'senderId'       =>self::$senderId,
               'senderApiKey'   =>self::$senderApiKey,
               'trackingNumber' =>$data['trackingNumber']   ?? '',
               'orderStatusId'  =>$data['orderStatusId']    ?? '',
            ];

            //here set type of request get or post 
            $this->request_type = 'POST';
            

            // here sent curl request
            return $this->curl_api_connect();
         }


         /**
         * Cancel_a_Shipment function.
         *
         * @access public
         * @param mixed $package
         * @return void
         */ 
         function Cancel_a_Shipment($data = array()){
             
             //check if data is sent correctly
             if(empty($data))
               return 'error';

            // here set url page in wassal
            $this->request_url = 'cancelShipment?access_token=1';

            //here set parameters sent in curl request
            $this->wassal_params = [
               'senderId'       =>self::$senderId,
               'senderApiKey'   =>self::$senderApiKey,
               'shipmentId'     =>$data['shipmentId'] ?? '',
            ];

            //here set type of request get or post 
            $this->request_type = 'POST';
            

            // here sent curl request
            return $this->curl_api_connect();
         }


         /**
         * Check_Shipping_Prices function.
         *
         * @access public
         * @param mixed $package
         * @return void
         */ 
         function Check_Shipping_Prices($data = array()){
             
            
            // here set url page in wassal
            $this->request_url = 'pricing';

            //here set parameters sent in curl request
            $this->wassal_params = [
               'from'           =>$data['from'] ?? 'Alexandria',
            ];

            //here set type of request get or post 
            $this->request_type = 'GET';
            

            // here sent curl request
            // and call method handle_prices_with_destinations to arrange destination and price 
            return $this->handle_prices_with_destinations($this->curl_api_connect());
         }

        /**
         * get_params function.
         *
         * @access public
         * @param mixed $package
         * @return void
         */ 
         function get_params(){
            if(empty($this->wassal_params))
              return 'error';
            
            $handle_params = '';           

            foreach ($this->wassal_params as $key => $param):
                 $handle_params .="{$key}={$param}&";
            endforeach;

            return $handle_params;

         }

        /**
         * Get_ShipmentId_and_TrackNo function.
         *
         * @access public
         * @param mixed $package
         * @return void
         */ 
         function Get_ShipmentId_and_TrackNo($response_json){
              if(empty($response_json))
                  return;

              return array(
                   'orderStatusId'  => $response_json['orderStatusId'],
                   'trackingNumber' => $response_json['trackNo'],
                   'shipmentId'     => $response_json['id'],
              );

         }


        /**
         * get_params function.
         *
         * @access public
         * @param mixed $package
         * @return void
         */ 
         function handle_prices_with_destinations($destinations){
            if(empty($destinations))
              return 'error';
            
            $handle_destinations = array();           

            foreach ($destinations as $key => $param):
                $handle_destinations[$param['destination']]=$param['price'];
            endforeach;

            return $handle_destinations;

         }


  }
}