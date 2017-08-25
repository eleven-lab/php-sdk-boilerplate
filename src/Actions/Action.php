<?php

namespace SDK\Actions;

use SDK\Base\Action as BaseAction;
use GuzzleHttp\Psr7\Request as Psr7Request;

abstract class Action extends BaseAction
{


    /**
     * Leaf classes must override that attribute with the validation rules for the request parameters
     * @return array
     */
    protected function getRequestParamsRules()
    {
        return [];
    }

    protected function getUrlParametersRules()
    {
        return [];
    }

    /**
     * Leaf classes must override that attribute with the validation rules for the response parameters
     * @return array
     */
    protected function getResponseParamsRules()
    {
        return [];
    }

    /**
     * @return array
     */
    protected function getHeaders()
    {
        return [
            'Accept'        => "application/json",
            'Content-Type'  => "application/json"
        ];
    }

    /**
     * Take a request and add authentication data
     * @param Psr7Request $request
     * @return mixed $request
     */
    function authenticate(Psr7Request $request)
    {
        return $request->withHeader('Authorization', 'Basic ' . $this->context->getCredentials()->toHttpBasic());
    }

    /**
     * Supported payload types:
     * - raw (http://docs.guzzlephp.org/en/latest/request-options.html#body):
     * - form_params (http://docs.guzzlephp.org/en/latest/request-options.html#form_params):
     * - multipart (http://docs.guzzlephp.org/en/latest/request-options.html#multipart)
     * - json (http://docs.guzzlephp.org/en/latest/request-options.html#json)
     *
     * @return string
     */
    protected function getPayloadType()
    {
        return 'json';
    }
}
