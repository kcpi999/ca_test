<?php

class RatesRefresherYahoo extends RatesRefresher {

    protected $currency_to = 'RUB';

    public function refreshRates() {
        $currency_model = new Currency;
        $currencies = $currency_model->getActiveCurrenciesIndexedByCode();

        $pairs = $this->makeFromToPairs($currencies);

        $rates = $this->getRates($pairs);
        if (!$rates) {
            return false;
        }
        $rates = $this->saveRates($currencies, $rates);
    }

    /*
     * Save rates to database
     * 
     * @param rates array
     *  format goes loke this: 
     * 	 [0] => Array
     * 		(
     * 			[id] => USDRUB
     * 			[Name] => USD/RUB
     * 			[Rate] => 56.8845
     * 			[Date] => 7/19/2015
     * 			[Time] => 8:24am
     * 			[Ask] => 56.8920
     * 			[Bid] => 56.8845
     * 		)
     */

    protected function saveRates($currencies, $rates) {
        $currency_model = new Currency;
        foreach ($rates as $rate) {
            $code_from = substr($rate['id'], 0, 3);
            $currency_from = $currencies[$code_from];
            $currency_to = $currency_model->getCurrencyByCode($this->currency_to);
            // getting GMT datetime.
            $date = date('Y-m-d H:i:s', strtotime($rate['Date'] . ' ' . $rate['Time'] . ' -1 hour'));

            $currency_rate_model = new CurrencyRate();
            $data = array(
                'curr_from_id' => $currency_from->id,
                'curr_to_id' => $currency_to->id,
                'rate' => floatval($rate['Rate']),
                'date' => $date,
                'source_url' => 'http://query.yahooapis.com/v1/public/yql',
                'created' => date('Y-m-d H:i:s'),
            );
            $currency_rate_model->insert($data);
        }
    }

    protected function makeFromToPairs($currencies) {
        $pairs = array();
        foreach ($currencies as $currency) {
            $pairs[] = $currency->code . $this->currency_to;
        }
        return $pairs;
    }

    /**
     * currency codes : http://en.wikipedia.org/wiki/ISO_4217
     * 
     * @param $currency_from_to_pairs e.g. array('USDRUB', 'BYRRUB', ..)
     */
    protected function getRates(array $currency_from_to_pairs) {
        $pairs_str = implode(',', $currency_from_to_pairs);

        $yql_base_url = "http://query.yahooapis.com/v1/public/yql";
        $yql_query = 'select * from yahoo.finance.xchange where pair in ("' . $pairs_str . '")';
        $yql_query_url = $yql_base_url . "?q=" . urlencode($yql_query);
        $yql_query_url .= "&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys";
        $yql_session = curl_init($yql_query_url);
        curl_setopt($yql_session, CURLOPT_RETURNTRANSFER, true);
        $yqlexec = curl_exec($yql_session);
        $yql_responce = json_decode($yqlexec, true);
        if (!$yql_responce) {
            return false;
        }

        $rates = $yql_responce['query']['results']['rate'];

        return $rates;
    }

}
