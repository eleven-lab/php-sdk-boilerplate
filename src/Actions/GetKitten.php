<?php

namespace SDK\Actions;


use SDK\Objects\Kitten;

class GetKitten extends Action
{

    protected $url_params = [
        'kitten_id'
    ];

    /**
     * Leaf classes must override that attribute with the relative endpoint url.
     * @return string
     */
    protected function getEndpoint()
    {
        return 'kittens/{kitten_id}';
    }

    /**
     * Leaf classes must override that attribute with the http method.
     * @return string
     */
    protected function getMethod()
    {
        return 'GET';
    }

    /**
     * Leaf classes must override that Attribute with the validation rules for the response parameters
     * @return array
     */
    protected function getResponseParamsRules()
    {
        return Kitten::getRules();
    }

    /**
     * Leaf classes must override that Attribute with the validation rules for the request body parameters
     *
     * @return array
     */
    protected function getRequestParamsRules()
    {
        return [];
    }

    /**
     * Leaf classes must override this function with the validation rules for the request endpoint
     *
     * @return array
     */
    protected function getUrlParametersRules()
    {
        return [
            'kitten_id' => 'required|integer'
        ];
    }


    /**
     * Parse the response, should be useful to instantiate an object.
     * @param $response
     * @return Kitten
     */
    protected function parseResponse(array $response)
    {
        return Kitten::parse($response);
    }
}