<?php namespace Datlv\Meta\Concerns;

use Schema;

/**
 * Trait EloquentModelOverride
 *
 * @propert-read string $table
 * @package Datlv\Meta\Concerns
 * @mixin \Datlv\Meta\Concerns\GetMetaData
 * @mixin \Datlv\Meta\Concerns\SetMetaData
 * @mixin \Datlv\Meta\Concerns\UnsetMetaData
 * @mixin \Datlv\Kit\Extensions\Model
 *
 */
trait EloquentModelOverride
{
    /**
     * @return array
     */
    public function getFillable()
    {
        return array_merge($this->fillable, array_keys($this->metaAttributes()));
    }

    public function setAttribute($key, $value)
    {
        // ignore the trait properties being set.
        if (starts_with($key, 'meta') || $key == 'query') {
            $this->$key = $value;

            return;
        }
        // if key is a model attribute, set as is
        if (array_key_exists($key, parent::getAttributes())) {
            parent::setAttribute($key, $value);

            return;
        }
        // if the key has a mutator execute it
        $mutator = camel_case('set_' . $key . '_meta');
        if (method_exists($this, $mutator)) {
            $this->{$mutator}($value);

            return;
        }
        // if key belongs to meta data, append its value.
        if ($this->metaData->has($key)) {
            $this->metaData[$key]->value = $value;

            return;
        }
        // if model table has the column named to the key
        if (Schema::hasColumn($this->table, $key)) {
            parent::setAttribute($key, $value);

            return;
        }
        // key doesn't belong to model, lets create a new meta relationship
        $this->setMetaString($key, $value);
    }

    /**
     * Get an attribute from the model.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getAttribute($key)
    {
        // parent call first.
        if (($attr = parent::getAttribute($key)) !== null) {
            return $attr;
        }

        // Check for meta accessor
        $accessor = camel_case('get_' . $key . '_meta');
        if (method_exists($this, $accessor)) {
            return $this->{$accessor}();
        }
        // Check for legacy getter
        $getter = 'get' . ucfirst($key);
        // leave model relation methods for parent::
        $isRelationship = method_exists($this, $key);
        if (method_exists($this, $getter) && !$isRelationship) {
            return $this->{$getter}();
        }

        // there was no attribute on the model, retrieve the data from meta relationship
        return $this->getMeta($key);
    }

    public function offsetUnset($offset)
    {
        parent::offsetUnset($offset);
        $this->unsetMeta($offset);
    }

    public function offsetExists($offset)
    {
        // trait properties.
        if (starts_with($offset, 'meta') || $offset == 'query') {
            return isset($this->{$offset});
        }
        return parent::offsetExists($offset) ?: $this->getMetaData()->has($offset);
    }
}