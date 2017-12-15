<?php

namespace LocaleRouter\Strategy\Extract;

use LocaleRouter\Model\StrategyResultModel;
use Zend\Stdlib\RequestInterface;

class HttpAcceptLanguageStrategy extends AbstractExtractStrategy
{
    public function extractLocale(RequestInterface $request, $baseUrl)
    {
        $result = new StrategyResultModel();
        $locale = null;

        $headers = $request->getHeaders();
        if ($headers->has('Accept-Language')) {
            $locales = $headers->get('Accept-Language')->getPrioritized();

            foreach ($locales as $locale) {
                $localeString = $locale->getLanguage();

                $localeArray = \Locale::parseLocale($localeString);

                if (array_key_exists('language', $localeArray) && array_key_exists('region', $localeArray)) {
                    $locale = $this->getLanguage($localeArray['language'] . '_' . $localeArray['region']);
                } else {
                    if (array_key_exists('language', $localeArray)) {
                        $locale = $this->getLanguage($localeArray['language']);
                    }
                }

                if ($locale) {
                    break;
                }
            }
        }
        $result->setLocale($locale);

        return $result;
    }
}
