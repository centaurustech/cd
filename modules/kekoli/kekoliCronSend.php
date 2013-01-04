<?php

include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/kekoli.php');

class kekoliCron extends kekoli {

     public function __construct() {
          parent::__construct();
          $this->SendOrders("C");
     }

}

// On instancie l'objet
new KekoliCron();