<?php

class Currency extends Zend_Db_Table_Abstract {

    /**
     * table name
     */
    protected $_name = 'currency';

    public function getActiveCurrencies() {
        $db = Zend_Db_Table::getDefaultAdapter();
        $select = $db->select()
                ->from(array('c' => 'currency'))
                ->where('c.active = 1')
                ->order('c.sort ASC');
        $currencies = $select->query()->fetchAll(Zend_Db::FETCH_OBJ);
        return $currencies;
    }

    public function getActiveCurrenciesIndexedByCode() {
        $currencies = $this->getActiveCurrencies();
        $currencies_indexed = array();
        foreach ($currencies as $currency) {
            $currencies_indexed[$currency->code] = $currency;
        }
        return $currencies_indexed;
    }

    public function getCurrencyByCode($code) {
        $db = Zend_Db_Table::getDefaultAdapter();
        $select = $db->select()
                ->from(array('c' => 'currency'))
                ->where('c.code = ?', $code);
        $result = $select->query()->fetch(Zend_Db::FETCH_OBJ);
        return $result;
    }

    /**
     * Load Currency Rates for the collection of Currencies.
     */
    public function loadRates(&$currencies, $currency_to_code) {
        $db = Zend_Db_Table::getDefaultAdapter();
        $currency_to = $this->getCurrencyByCode($currency_to_code);
        foreach ($currencies as &$currency) {
            //getting date of last record for currency pair.
            $select = $db->select()
                    ->from(array('cr' => 'currency_rates'), array('date' => 'MAX(cr.date)'))
                    ->where('cr.curr_from_id = ?', $currency->id)
                    ->where('cr.curr_to_id = ?', $currency_to->id);
            $date = $select->query()->fetch(Zend_Db::FETCH_COLUMN);
            if (!$date) {
                $currency->rate = '0.00';
                continue;
            }

            $select2 = $db->select()
                    ->from(array('cr' => 'currency_rates'), array('rate' => 'cr.rate'))
                    ->where('cr.curr_from_id = ?', $currency->id)
                    ->where('cr.curr_to_id = ?', $currency_to->id)
                    ->where('cr.date = ?', $date);
            $rate = $select2->query()->fetch(Zend_Db::FETCH_COLUMN);
            $currency->rate = $rate;
        }
    }

}
