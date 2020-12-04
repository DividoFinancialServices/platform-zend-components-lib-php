<?php
class Divido_Currency extends Zend_Currency
{
    private static $_currencies = null;

    public function toCurrency($value = null, array $options = array())
    {
        $display = parent::toCurrency($value, $options);

        $display = (isset($this->_options['showCurrencyCode']) && $this->_options['showCurrencyCode']) ? $display . " " . $this->getShortname() : $display;

        return str_replace(".00", "", $display);
    }

    public static function getCurrencies()
    {
        if (null === self::$_currencies) {
            self::$_currencies = self::buildMap();
        }

        return self::$_currencies;
    }

    public static function getDisplayName($identifier)
    {
        $countries = self::getCurrencies();
        if (isset($currencies[$identifier])) {
            return $currencies[$identifier];
        }
        return '';
    }

    protected static function buildMap()
    {
        $locale = 'en';

        $oCurrency = new Zend_Locale_Data();
        $aAvailableCurrencies = $oCurrency->getList($locale, "nametocurrency");

        $currencies = array();

        foreach ($aAvailableCurrencies as $code => $name) {
            $currencies[$code] = $code . ": " . $name;
        }

        return $currencies;
    }

}
