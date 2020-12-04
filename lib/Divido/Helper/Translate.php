<?php

class Divido_Helper_Translate
{
    public function translate($string, $language = null, $environment = null)
    {
        $i18n = new Api_I18n();
        return $i18n->_($string, $language, $environment);
    }
}
