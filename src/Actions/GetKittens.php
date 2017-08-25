<?php

namespace SDK\Actions;


use SDK\Objects\Kitten;

class GetKittens extends Action
{

    /**
     * Leaf classes must override that attribute with the relative endpoint url.
     * @return string
     */
    protected function getEndpoint()
    {
        return 'kittens';
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
        return array_merge([
            'kittens' => 'required|array'
        ], Kitten::getValidationRules('kittens.*'));
    }

    /**
     * Parse the response, should be useful to instantiate an object.
     * @param $response
     * @return Kitten
     */
    protected function parseResponse(array $response)
    {
        return Kitten::parse($response['kittens']);
    }
}