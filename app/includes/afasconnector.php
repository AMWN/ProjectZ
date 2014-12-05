<?php

require_once(app_path() . '/includes/nusoap.php');

class connector {

    private $obj = [];
    public $result = [];

    public function connector($action, $connector, $filter = '', $xml = '') {

        $this->obj = $this->loadsettings($action, $connector, $filter = '', $xml = '');
        
        // Keuze AOL of Lokaal. Get of update.
        if ($this->obj['action'] === 'get') { //Lokaal 0
            switch ($this->obj['settings']['general']['aol']) {
                case 0:
                    $this->get_local($this->obj);
                    break;
                case 1:
                    $this->get_aol($this->obj);
                    break;
            }
        } elseif ($this->obj['action'] === 'update') { //AOL 1
            switch ($this->obj['settings']['general']['aol']) {
                case 0:
                    $this->update_local($this->obj);
                    break;
                case 1:
                    $this->update_aol($this->obj);
                    break;
            }
        }

        // Verwerken naar array
        $this->fetcharray($this->result['xml']);
    }

    private function loadsettings($action, $connector, $filter = '', $xml = '') {

        $obj = array(
            'settings' => Config::get('app.settings'),
            'action' => $action,
            'xml' => $xml,
            'connector' => $connector,
            'filter' => $filter,
            'result' => ''
        );
        return $obj;
    }

    private function get_local($obj) {

        $client = new nusoap_client($obj['settings']['profitlocal']['url'] . "getconnector.asmx?WSDL", 'wsdl');
        $client->soap_defencoding = 'UTF-8';

        $args = array(
            'userId' => $obj['settings']['profitlocal']['username'],
            'password' => $obj['settings']['profitlocal']['password'],
            'environmentId' => $obj['settings']['profitlocal']['environmentid'],
            'connectorId' => $obj['connector'],
            'options' => '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><OutputOptions>2</OutputOptions></options>',
            'filtersXml' => ''
        );



        $result = $client->Call('GetDataWithOptions', $args);


        if ($client->fault) {
            echo '<h2>Fault</h2><pre>';
            print_r($result);
            echo '</pre>';
        } else {
            $err = $client->getError();
            if ($err) {
                echo '<h2>Error</h2><pre>' . $err . '</pre>';
            }
        }

        $xml_string = $result['GetDataWithOptionsResult'];
        $this->result['xml'] = $xml_string;
        return $xml_string;
    }

    private function get_aol($obj) {
        $client = new nusoap_client($obj['settings']['profitaol']['url'] . "getconnector.asmx?WSDL", 'wsdl');
        $client->setCredentials('AOL\\' . $obj['settings']['profitaol']['username'], $obj['settings']['profitaol']['password'], 'ntlm');
        $client->soap_defencoding = 'UTF-8';


        $args = array(
            'userId' => $obj['settings']['profitaol']['username'],
            'password' => $obj['settings']['profitaol']['password'],
            'environmentId' => $obj['settings']['profitaol']['environmentid'],
            'connectorId' => $obj['connector'],
            'options' => '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>3</Outputoptions></options>',
            'filtersXml' => ''
        );

        $result = $client->Call('GetDataWithOptions', $args);

        if ($client->fault) {
            echo '<h2>Fault</h2><pre>';
            print_r($result);
            echo '</pre>';
        } else {
            $err = $client->getError();
            if ($err) {
                echo '<h2>Error</h2><pre>' . $err . '</pre>';
            }
        }
        $xml_string = simplexml_load_string(utf8_encode($result['GetDataWithOptionsResult']));
        $this->result['xml'] = $xml_string;
        return $xml_string;
    }

    private function fetcharray($xml_string) {

        function xml2phpArray($xml, $arr) {
            $iter = 0;
            foreach ($xml->children() as $b) {
                $a = $b->getName();
                if (!$b->children()) {
                    $arr[$a] = trim($b[0]);
                } else {
                    $arr[$a][$iter] = array();
                    $arr[$a][$iter] = xml2phpArray($b, $arr[$a][$iter]);
                }
                $iter++;
            }
            return $arr;
        }

        function xml2columns($xml_string) {
            $columns = array();
            foreach ($xml_string->children() as $lvl0) {
                foreach ($lvl0 as $lvl1) {
                    $columns[] = array(
                        'field' => $lvl1->getName(),
                        'title' => $lvl1->getName(),
                        'formatter' => 'formattertext',
                    );
                }
                break;
            }
            return $columns;
        }

        $arr = xml2phpArray($xml_string, array());
        foreach ($arr as $key => $value) {
            $arr = $arr[$key];
        }

        $this->result['data'] = $arr;
        $this->result['columns'] = xml2columns($xml_string);
    }

}
