<?php namespace Datlv\Meta\Concerns;

use Illuminate\Support\Collection;

/**
 * Trait GetMetaData
 *
 * @property-read \Datlv\Meta\Meta[]|\Illuminate\Database\Eloquent\Collection $metaData
 * @package Datlv\Meta\Concerns
 * @mixin \Datlv\Kit\Extensions\Model
 */
trait GetMetaData
{
    /**
     * @param string|array|null $key
     * @param bool $raw
     * @return \Illuminate\Support\Collection|\Datlv\Meta\Meta|mixed
     */
    public function getMeta($key = null, $raw = false)
    {
        if (is_string($key) && preg_match('/[,|]/is', $key)) {
            $key = preg_split('/ ?[,|] ?/', $key);
        }

        return is_string($key) ? $this->getMetaString($key, $raw) : $this->getMetaArray($key, $raw);
    }

    /**
     * @param string $key
     * @param bool $raw
     * @return mixed|null
     */
    protected function getMetaString($key, $raw = false)
    {
        $meta = $this->metaData->get($key, null);
        if (is_null($meta) || $meta->isMarkedForDeletion()) {
            return null;
        }

        return $raw ? $meta : $meta->value;
    }

    /**
     * @param null|array $keys
     * @param bool $raw
     * @return \Illuminate\Support\Collection
     */
    protected function getMetaArray($keys = null, $raw = false)
    {
        $collection = new Collection();
        foreach ($this->metaData as $meta) {
            if (! $meta->isMarkedForDeletion() && (is_null($keys) || in_array($meta->key, $keys))) {
                $collection->put($meta->key, $raw ? $meta : $meta->value);
            }
        }

        return $collection;
    }
}