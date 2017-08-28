<?php
namespace SDK\Base;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;

abstract class Object implements Parsable, Relationable, SelfValidates, Arrayable, \ArrayAccess
{

    use HasRelations, HasValidationRules;

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @var array
     */
    protected $dates = [];

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';


    /**
     * @param array $attributes
     * @return static
     */
    public static function parse(array $attributes)
    {

        $object = new static();

        foreach ($object->attributes as $key => $default) {
            /*
             * If the attribute is a relation we are going to parse it
             * calling the ::parse() method of the respective Object class
             */
            if (self::isRelation($key) && isset($attributes[$key])) {
                $item = $attributes[$key];
                $relationClass = self::getRelationClass($key);
                /*
                 * If we have an array of relations we need to parse
                 * every element of the array and return an array of
                 * Object of the respective class
                 */
                $value = !self::isArrayOfRelations($key) ?
                    $relationClass::parse($item) :
                    array_map(function($relation) use($key){
                        $class = self::getRelationClass($key);
                        return $class::parse($relation);
                    }, $item);

            } else {
                $value = isset($attributes[$key]) ? $attributes[$key] : $default;
            }

            $object->attributes[$key] = $value;
        }

        return $object;
    }

    public static function parseArray(array $objects)
    {

        $parsedObjects = array_map(function($item) {
            return self::parse($item);
        }, $objects);

        return collect($parsedObjects);
    }

    /**
     * @param string $radix
     * @return array
     */
    static function getRules($radix = "")
    {

        $parent = $radix;
        /*
         * If a validation rule 'required' ends with a '*' operator, it means
         * that the field is required only if the whole array is present,
         * so it is better to remove that
         */
        if(Str::endsWith($radix, '.*')){
            $parent = Str::replaceLast('.*', '', $parent);
        }
        $rules = static::getValidationRules($parent);

        if (!empty($radix)) {
            $radix = "$radix.";
            $tmp = [];
            foreach ($rules as $attribute => $rule) {
                $tmp["$radix$attribute"] = $rule;
            }
            $rules = $tmp;
        }

        $relations = static::getRelations();
        foreach (array_keys($relations) as $attribute) {
            $fullRadix = $radix.$attribute;
            if(self::isArrayOfRelations($attribute)) $fullRadix .= '.*';
            $relationClass = self::getRelationClass($attribute);
            $relation_rules = $relationClass::getRules($fullRadix);
            $rules = array_merge($rules, $relation_rules);
        }

        return $rules;
    }

    public function toArray($stripEmpty = true)
    {
        $arr = [];

        foreach ($this->attributes as $attribute => $value) {
            if($value && $this->isRelation($attribute)){
                $parsedRelation = !is_array($value) ? $value->toArray($stripEmpty) :
                    array_map(function($value) use($stripEmpty){
                            return $value->toArray($stripEmpty);
                        }, $value);
                $arr[$attribute] = $parsedRelation;
            }else if(in_array($value, $this->dates, true)) {
                $arr[$attribute] = $this->formatDate($this->$attribute);
            }else{
                $arr[$attribute] = $value;
            }
        }

        if($stripEmpty) {
            /*
             * Remove empty items
             */
            return array_filter($arr, function ($value) {
                return is_numeric($value) || !empty($value);
            });
        }

        return $arr;
    }

    protected static function isArrayOfRelations($attribute)
    {
        $relations = static::getRelations();
        return self::isRelation($attribute) && Str::startsWith($relations[$attribute], 'array|');
    }

    protected static function getRelationClass($attribute)
    {
        if(!self::isRelation($attribute)) return null;

        $relations = static::getRelations();
        if(self::isArrayOfRelations($attribute)){
            return Str::replaceFirst('array|', '', $relations[$attribute]);
        }else{
            return $relations[$attribute];
        }
    }

    protected static function isRelation($attribute)
    {
        return array_key_exists($attribute, static::getRelations());
    }

    private function escapeMethod($method, $strip)
    {
        $escaped = Str::replaceFirst($strip, '', $method);
        $escaped = Str::snake($escaped);
        return $escaped;
    }

    private function hasRelationGetter($method)
    {
        return $this->hasRelationMethod($method, 'get');
    }

    private function hasRelationSetter($method)
    {
        return $this->hasRelationMethod($method, 'set');
    }

    private function hasRelationMethod($method, $type)
    {
        if(Str::startsWith($method, $type)) {
            $escaped = $this->escapeMethod($method, $type);
            $relations = static::getRelations();
            if (array_key_exists($escaped, $relations)) {
                return true;
            }
        }

        return false;
    }

    private function getRelationAttributeValue($relationName)
    {
        $relation = static::getRelationClass($relationName);
        $attributeValue = isset($this->attributes[$relationName]) ? $this->attributes[$relationName] : null;
        if($attributeValue instanceof $relation || is_null($attributeValue)) return $attributeValue;

        return $relation::parse($attributeValue);
    }

    private function setRelationAttributeValue($relationName, $value)
    {
        $relation = static::getRelations()[$relationName];
        if($value instanceof $relation) {
            $this->attributes[$relationName] = $value;
            return $this;
        }

        throw new \LogicException("Wrong relation type: argument for relation '$relationName'' must be of type '$relation' '" . gettype($value) . "' given." );
    }

    private function getRelationAttribute($method)
    {

        if($this->hasRelationGetter($method)){
            return $this->getRelationAttributeValue($this->escapeMethod($method, 'get'));
        }

        return null;

    }

    private function setRelationAttribute($method, $value)
    {
        if($this->hasRelationSetter($method)){
            return $this->setRelationAttributeValue($this->escapeMethod($method, 'set'), $value);
        }

        return null;
    }

    protected function castDate($value)
    {

        if(is_null($value)) return null;
        if(is_integer($value)) return Carbon::createFromTimestamp($value);
        if(is_string($value)) return Carbon::parse($value);

        return null;
    }

    protected function formatDate($value)
    {

        if(is_null($value)) return '';
        if($value instanceof Carbon) return $value->format($this->dateFormat);

        $casted = $this->castDate($value);

        return !$casted ?: $casted->format($this->dateFormat);

    }

    public function __call($method, $args)
    {

        if(($value = $this->getRelationAttribute($method))){
            return $value;
        }

        if(($value = $this->setRelationAttribute($method, ...$args))){
            return $value;
        }

        throw new \BadMethodCallException('Method ' . self::class . '::' . $method . ' not found.');
    }

    public function __get($key)
    {
        if(array_key_exists($key, static::getRelations())){
            return $this->getRelationAttributeValue($key);
        }

        /*
         * Check if an accessor exists, if so invoke it
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
                return $this->castDate($this->attributes[$key]);
            }

            return $this->attributes[$key];
        }

        return null;
    }

    public function __set($key, $value)
    {
        if(array_key_exists($key, static::getRelations())){
            return $this->setRelationAttributeValue($key, $value);
        }

        if(array_key_exists($key, $this->attributes)){
            $this->attributes[$key] = $value;
        }

        return $this;
    }

    /**
     * Determine if the given attribute exists.
     *
     * @param  mixed  $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->$offset);
    }

    /**
     * Get the value for a given offset.
     *
     * @param  mixed  $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    /**
     * Set the value for a given offset.
     *
     * @param  mixed  $offset
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
    }

    /**
     * Unset the value for a given offset.
     *
     * @param  mixed  $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->$offset);
    }

    /**
     * Determine if an attribute or relation exists on the object.
     *
     * @param  string  $key
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->attributes[$key]) || !is_null($this->attribute[$key]);
    }

    /**
     * Unset an attribute on the object.
     *
     * @param  string  $key
     * @return void
     */
    public function __unset($key)
    {
        unset($this->attributes[$key], $this->relations[$key]);
    }


}
