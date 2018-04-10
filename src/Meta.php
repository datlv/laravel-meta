<?php namespace Datlv\Meta;

use Datetime;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Datlv\Kit\Extensions\Model;

/**
 * Class Meta
 *
 * @package Datlv\Meta
 * @property int $id
 * @property string $key
 * @property string|null $value
 * @property string $type
 * @property int $metable_id
 * @property string $metable_type
 * @method static \Illuminate\Database\Eloquent\Builder|\Datlv\Kit\Extensions\Model except($ids = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\Datlv\Kit\Extensions\Model findText($columns, $text)
 * @method static \Illuminate\Database\Eloquent\Builder|\Datlv\Kit\Extensions\Model whereAttributes($attributes)
 * @mixin \Eloquent
 */
class Meta extends Model
{
    public $timestamps = false;

    protected $table = 'meta';

    protected $fillable = ['key', 'value'];

    /**
     * @var array
     */
    protected $dataTypes = ['boolean', 'integer', 'double', 'float', 'string', 'NULL'];

    /**
     * Whether or not to delete the Data on save.
     *
     * @var bool
     */
    protected $markForDeletion = false;

    /**
     * Whether or not to delete the Data on save.
     *
     * @param bool $bool
     */
    public function markForDeletion($bool = true)
    {
        $this->markForDeletion = $bool;
    }

    /**
     * Check if the model needs to be deleted.
     *
     * @return bool
     */
    public function isMarkedForDeletion()
    {
        return (bool) $this->markForDeletion;
    }

    /**
     * Get all of the owning metable models.
     */
    public function metable()
    {
        return $this->morphTo();
    }

    /**
     * Set the value and type.
     *
     * @param mixed $value
     */
    public function setValueAttribute($value)
    {
        if (is_array($value)) {
            $this->type = 'array';
            $this->attributes['value'] = json_encode($value);
        } elseif ($value instanceof DateTime) {
            $this->type = 'datetime';
            $this->attributes['value'] = $this->fromDateTime($value);
        } elseif ($value instanceof EloquentModel) {
            $this->type = 'model';
            $this->attributes['value'] = get_class($value).($value->exists ? '#'.$value->getKey() : '');
        } elseif (is_object($value)) {
            $this->type = 'object';
            $this->attributes['value'] = json_encode($value);
        } else {
            $type = gettype($value);
            $this->type = in_array($type, $this->dataTypes) ? $type : 'string';
            $this->attributes['value'] = $value;
        }
    }

    /**
     * @param string $value
     * @return mixed
     */
    public function getValueAttribute($value)
    {
        $type = $this->type ?: 'null';
        switch ($type) {
            case 'array':
                return json_decode($value, true);
            case 'object':
                return json_decode($value);
            case 'datetime':
                return $this->asDateTime($value);
            case 'model': {
                if (strpos($value, '#') === false) {
                    return new $value();
                }
                list($class, $id) = explode('#', $value);

                return with(new $class())->findOrFail($id);
            }
        }
        if (in_array($type, $this->dataTypes)) {
            settype($value, $type);
        }

        return $value;
    }
}