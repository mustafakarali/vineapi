<?php

/*
 * vineApi v1
 *
 */

class vineApi{

     protected $options = array(
        'userAgent' => 'com.vine.iphone/1.0.3 (unknown, iPhone OS 6.1.0, iPhone, Scale/2.000000)',
        'baseUrl'   => 'https://api.vineapp.com/',
     );

    protected $userData = null;


    function __construct( $username = null, $password = null ){

        if( $username && $password )
            $this->login( array(
                'username' => $username,
                'password' => $password
            ) );


        return $this;
    }

    public function login( $loginParam ){

        $loginReq = $this->request('users/authenticate', $loginParam );

        $this->userData = $loginReq->data;

        return $this;

    }

    public function logout(){
        $this->request( 'users/authenticate', array(), 'DELETE' );
    }

    public function getPopular(){

        $popularReq = $this->request('timelines/popular');

        return $popularReq->data;
    }

    public function getUser( $userId = null ){

        $requestUrl = 'users/';

        if( $userId != null )
            $requestUrl .= 'profiles/'. $userId;
        else
            $requestUrl .= 'me';

        return $this->request( $requestUrl )->data;

    }

    public function getTimeline( $userId = null ){

        $requestUrl = 'timelines/users/';

        if( $userId == null )
            $userId = $this->userData->userId;

        $requestUrl .= $userId;

        return $this->request( $requestUrl )->data;
    }

    public function search( $key ){
        return $this->request('timelines/tags/'. $key)->data;
    }

    public function getPost( $postId){
        return $this->request('timelines/posts/'. $postId)->data;
    }

    protected function request( $url,  $data = array(), $costumRequest = null ){

        if( !function_exists('curl_version') )
            exit( 'cURL is not installed on this server' );

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->options['baseUrl'] . $url);
        curl_setopt($curl, CURLOPT_USERAGENT, $this->options['userAgent']);

        if( count( $data ) ){
            $data = http_build_query( $data );
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        if( is_object($this->userData) ){
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('vine-session-id: '.$this->userData->key));
        }

        if( $costumRequest )
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $costumRequest);

        $response = curl_exec($curl);

        if(curl_errno($curl)){
            exit('Curl error: ' . curl_error($curl));
        }

        curl_close($curl);

        return $this->responseControl(json_decode($response));
    }

    protected function responseControl( $response ){

        if( is_object( $response ) ){

            if( $response->success == false )
                exit( 'Vine Error: '. $response->error );

        }else
            exit('Unknown Error');

        return $response;
    }

}