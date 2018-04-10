<?php namespace Datlv\Meta\Tests\Stubs;

use Illuminate\Database\Eloquent\Model;
use Datlv\Meta\Metable;

/**
 * Class Model1
 *
 * @property integer $id
 * @property string $title
 * @property string $body
 *
 * @package Datlv\Meta\Test\Models
 */
class Model1 extends Model
{
    use Metable;

    public $table = 'metatest_model1s';

    protected $fillable = ['title', 'body'];

    protected function metaAttributes()
    {
        return [
            'extra1' => 'required',
            'extra2' => 'required',
            'extra3' => 'required',
            'extra4' => 'required',
            'extra5' => 'required',
            'extra6' => 'required',
            'extra7' => 'required',
            'extra8' => 'required',
            'extra9' => 'required',

            'extra_array' => '',
            'extra_datetime' => '',
            'extra_model' => '',
            'extra_model_exists' => '',
            'extra_object' => '',
            'extra_boolean' => '',
            'extra_integer' => '',
            'extra_double' => '',
            'extra_float' => '',
            'extra_string' => '',
            'extra_null' => '',
        ];
    }

}