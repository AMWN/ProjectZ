<?php

include(app_path() . '/includes/afasconnector.php');

class GetdataController extends BaseController {

    private $action;
    private $connector;

    public function getdata() {

        $action = 'get';
        $connector = Input::get('data.1');

        $result = new connector($action, $connector, '', '');

        $arr = array(
            'data' => $result->result['data'],
            'columns' => $result->result['columns']
        );

        return json_encode($arr);
        
    }

}
