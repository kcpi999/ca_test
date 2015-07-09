<?php

/**
 * class for currency rates refreshing
 */
abstract class RatesRefresher extends Zend_Db_Table_Abstract {

    /**
     * get the currencies' rates from outer source.
     * 
     * @return true on success, false otherwise.
     */
    public abstract function refreshRates();
}
