<?php

class IndexController extends Zend_Controller_Action {

    public function init() {
        /* Initialize action controller here */
    }

    public function indexAction() {

        $currency_model = new Currency;
        $currencies = $currency_model->getActiveCurrencies();
        $currency_model->loadRates($currencies, 'RUB');

        $need_refresh = false;
        $now = strtotime(gmdate('Y-m-d H:i:s'));
        foreach ($currencies as $currency) {
            $updated_at = strtotime($currency->updated_at);
            if ($now - $updated_at > 60 * 60 * 24) {
                $need_refresh = true;
                break;
            }
        }
        if ($need_refresh) {
            $rates_refresher = new RatesRefresherYahoo;
            $rates_refresher->refreshRates();
        }
        $currency_model->loadRates($currencies, 'RUB');

        $this->view->assign('rates', $currencies);
    }

    /**
     * ajax
     */
    public function refreshratesAction() {
        if ($this->getRequest()->getQuery('ajax') == 1 || $this->getRequest()->isXmlHttpRequest()) {
            $params = $this->getRequest()->getParams();
            $result = false;

            $rates_refresher = new RatesRefresherYahoo;
            $rates_refresher->refreshRates();

            $currency_model = new Currency;
            $currencies = $currency_model->getActiveCurrencies();
            $currency_model->loadRates($currencies, 'RUB');

            $result = $currencies;
            $this->_helper->json($result);
        }
    }

    public function omgAction() {
        /*
          $omg = 'OMG OMG OMG';
          $this->view->assign('omg', $omg);

          $rates_refresher = new RatesRefresherYahoo;
          $r = $rates_refresher->refreshRates();
         */
    }

    protected function pr($val) {
        echo '<pre>';
        print_r($val);
        echo '</pre>';
    }

    protected function vd($val) {
        echo '<pre>';
        var_dump($val);
        echo '</pre>';
    }

}
