<?php namespace Datlv\Meta\Concerns;

/**
 * Trait UnsetMetaData
 *
 * @property-read \Datlv\Meta\Meta[]|\Illuminate\Database\Eloquent\Collection $metaData
 * @package Datlv\Meta\Concerns
 * @mixin \Datlv\Kit\Extensions\Model
 */
trait UnsetMetaData
{
    /**
     * @param string|array $key
     */
    public function unsetMeta($key)
    {
        if (is_string($key) && preg_match('/[,|]/is', $key)) {
            $key = preg_split('/ ?[,|] ?/', $key);
        }
        is_string($key) ? $this->unsetMetaString($key) : $this->unsetMetaArray($key);
    }

    /**
     * @param string $key
     */
    protected function unsetMetaString($key)
    {
        if ($this->metaData->has($key)) {
            $this->metaData[$key]->markForDeletion();
        }
    }

    /**
     * @param string[] $keys
     */
    protected function unsetMetaArray($keys)
    {
        foreach ($keys as $key) {
            $this->unsetMetaString($key);
        }
    }
}