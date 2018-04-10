<?php namespace Datlv\Meta\Concerns;

use Datlv\Meta\Meta;

/**
 * Trait SetMetaData
 *
 * @property-read \Datlv\Meta\Meta[]|\Illuminate\Database\Eloquent\Collection $metaData
 * @package Datlv\Meta\Concerns
 * @mixin \Datlv\Kit\Extensions\Model
 */
trait SetMetaData
{
    /**
     * @param string|array $key
     * @param mixed $value
     * @return mixed
     */
    public function setMeta($key, $value = null)
    {
        return is_string($key) ? $this->setMetaString($key, $value) : $this->setMetaArray($key);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return \Datlv\Meta\Meta|null
     */
    protected function setMetaString($key, $value)
    {
        if ($this->isMetaAttribute($key)) {
            if ($this->metaData->has($key)) {
                // Make sure deletion marker is not set
                $this->metaData[$key]->markForDeletion(false);
                $this->metaData[$key]->value = $value;

                return $this->metaData[$key];
            }

            return $this->metaData[$key] = Meta::newModelInstance(['key' => $key, 'value' => $value]);
        }
    }

    /**
     * @param array $values
     * @return \Illuminate\Database\Eloquent\Collection|\Datlv\Meta\Meta[]
     */
    protected function setMetaArray($values)
    {
        foreach ($values as $key => $value) {
            $this->setMetaString($key, $value);
        }

        return $this->metaData->sortByDesc('id')->take(count($values));
    }
}