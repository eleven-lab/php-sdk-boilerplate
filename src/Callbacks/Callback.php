<?php

namespace ElevenLab\API\Boilerplate\Callbacks;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

abstract class Callback
{

    protected static $callbackTypes = [];

    protected $context;
    protected $payload;
    protected $signature;
    protected $verifier;

    protected $base_attributes = [
        'event' => '',
        'created_at' => -1
    ];

    protected $attributes = [];

    protected $objects = [];

    protected $dates = [
        'created_at'
    ];

    /**
     * Callback constructor
     */
    public function __construct()
    {
        $this->attributes = array_merge(
            $this->base_attributes,
            $this->attributes
        );
    }

    /**
     *
     * Generic callback parser, get the raw data and parse it to a valid callback type
     *
     * @param string $type
     * @param integer $created_at
     * @param array $payload The callback 'data' payload attribute
     *
     * @return static
     */
    public static function parse($type, $created_at, $payload)
    {
        $object = new static();
        $object->payload = $payload;

        $object->attributes['event'] = $type;
        $object->attributes['created_at'] = $created_at;

        foreach($object->attributes as $key => $default)
        {
            if(array_key_exists($key, $object->objects) && isset($object->payload[$key])){
                $value = $object->parseObject($object->payload[$key], $object->objects[$key]);
            } else {
                $value = isset($object->payload[$key]) ? $object->payload[$key] : $default;
            }

            $object->attributes[$key] = $value;
        }

        return $object;
    }

    /**
     *
     * Parse an array of API Objects
     *
     * @param array $items
     * @param string $class
     * @return array
     */
    protected function parseArrayObjects($items, $class)
    {

        $value = [];
        foreach ($items as $item) {
            $value[] = $this->parseObject($item, $class);
        }

        return $value;
    }

    /**
     *
     * Parse an API Object
     *
     * @param array $item
     * @param string $class
     * @return Object
     */
    protected function parseObject($item, $class)
    {

        $parts = explode('|', $class, 2);
        list($array, $class) = count($parts) > 1 ? $parts : ['', $parts[0]];
        if ($array === 'array') {
            $value = $this->parseArrayObjects($item, $class);
        } else {
            $value = call_user_func($class . '::parse', $item);
        }

        return $value;
    }

    /**
     * @return array
     */
    public static function getTypes()
    {
        return static::$callbackTypes;
    }

    /**
     * @param string $type
     * @return mixed
     */
    public static function getTypeClass($type)
    {
        return Arr::get(static::getTypes(), $type);
    }

    /**
     * @return array
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @param string $type
     * @return bool
     */
    public function is($type)
    {
        return $this->type === $type;
    }

    /**
     * @return mixed|null
     */
    public function getTypeAttribute()
    {
        return $this->event;
    }

    public function toArray($stripEmpty = true)
    {

        $arr = [];

        foreach($this->base_attributes as $key => $value)
        {
            $val = $this->$key;
            if(in_array($key, $this->dates)) {
                $val = $this->$key->timestamp;
            }

            $arr[$key] = $val;
        }

        $arr['data'] = array_diff_key($this->attributes, array_flip(array_keys($this->base_attributes)));

        foreach ($this->objects as $attribute => $object) {
            /*
             * Parse all sub relations
             */
            if (!is_array($arr['data'][$attribute])) {
                $value = $arr['data'][$attribute] ? $arr['data'][$attribute]->toArray($stripEmpty) : null;
            } else {
                $temp = [];
                foreach ($arr['data'][$attribute] as $item) {
                    $temp[] = $item->toArray($stripEmpty);
                }
                $value = $temp;
            }
            $arr['data'][$attribute] = $value;
        }

        return $arr;
    }

    public function __get($key)
    {

        /*
         * Here we check if attriubte has defined an accessor,
         * if so we invoke the requested method
         */
        if(method_exists($this, 'get' . Str::studly($key) . 'Attribute'))
        {
            $method = 'get' . Str::studly($key) . 'Attribute';
            return $this->$method();
        }

        if(array_key_exists($key, $this->attributes)) {

            /*
             *  If it is a date we will cast it to a Carbon object
             */
            if(in_array($key, $this->dates, true)){
                return Carbon::createFromTimestamp($this->attributes[$key]);
            }

            return $this->attributes[$key];
        }

        return null;
    }

}