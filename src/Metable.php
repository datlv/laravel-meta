<?php namespace Datlv\Meta;

use Illuminate\Database\Eloquent\Collection;
use Datlv\Meta\Concerns\EloquentModelOverride;
use Datlv\Meta\Concerns\GetMetaData;
use Datlv\Meta\Concerns\SetMetaData;
use Datlv\Meta\Concerns\UnsetMetaData;

/**
 * Trait Metable
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\Datlv\Meta\Meta[] $metaData
 * @property-read \Datlv\Meta\Meta[]|\Illuminate\Database\Eloquent\Collection $meta
 * @property-read string $table
 * @package Datlv\Meta
 * @mixin \Datlv\Meta\Concerns\GetMetaData
 * @mixin \Eloquent
 */
trait Metable
{
    use SetMetaData;
    use UnsetMetaData;
    use GetMetaData;
    use EloquentModelOverride;

    /**
     * Giới hạn các meta của Model
     * 'name' => rules
     * @return array
     */
    abstract protected function metaAttributes();

    public static function bootMetable()
    {
        static::saved(function ($model) {
            /** @var \Datlv\Meta\Metable|static $model */
            $model->saveMeta();
        });
        static::deleting(function ($model) {
            /** @var \Datlv\Meta\Metable|static $model */
            $model->meta()->delete();
        });
    }

    public function isMetaAttribute($key)
    {
        return isset($this->metaAttributes()[$key]);
    }

    /**
     * Get all of the model's meta data.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function meta()
    {
        return $this->morphMany(Meta::class, 'metable');
    }

    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return array_merge(parent::toArray(), [
            'meta_data' => $this->getMeta()->toArray(),
        ]);
    }

    public function saveMeta()
    {
        foreach ($this->metaData as $meta) {
            if ($meta->isMarkedForDeletion()) {
                $meta->delete();
                continue;
            }
            if ($meta->isDirty()) {
                // set meta and model relation id's into meta table.
                $this->meta()->save($meta);
            }
        }
    }

    /**
     * Getter $this->metaData
     *
     * @return \Illuminate\Database\Eloquent\Collection|\Datlv\Meta\Meta[]
     */
    protected function getMetaData()
    {
        if (!isset($this->metaLoaded)) {
            $this->metaData = $this->exists && ($objects = $this->meta) ? $objects->keyBy('key') : new Collection();
            $this->metaLoaded = true;
        }
        return $this->metaData;
    }
}