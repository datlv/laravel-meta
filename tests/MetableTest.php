<?php namespace Datlv\Meta\Tests;

use Carbon\Carbon;
use Datlv\Meta\Tests\Stubs\Model1;
use Datlv\Meta\Tests\Stubs\TestCase;

class MetableTest extends TestCase
{
    public function test_Set_meta_data()
    {
        $model = new Model1([
            'title' => 'Model title',
            'body' => 'Body Text',
        ]);
        $model->setMeta([
            'extra1' => 'Meta Data 1',
            'extra2' => ['text' => 'Meta Data 2'],
        ]);
        $model->setMeta('extra3', true);
        $model->setMeta('extra4');
        $model->extra5 = 555;
        $model['extra6'] = 666;
        $this->assertTrue(isset($model->extra1));
        $this->assertTrue(isset($model['extra2']));

        $this->assertTrue($model->metaData->has('extra1'));
        $this->assertTrue($model->metaData->has('extra2'));
        $this->assertTrue($model->metaData->has('extra3'));
        $this->assertTrue($model->metaData->has('extra4'));
        $this->assertTrue($model->metaData->has('extra5'));
        $this->assertTrue($model->metaData->has('extra6'));
        $this->assertFalse($model->metaData->has('extra7'));
    }

    public function test_Fill_meta_data()
    {
        $model = new Model1([
            'title' => 'Model title',
            'body' => 'Body Text',
        ]);
        $this->assertFalse($model->metaData->has('extra1'));
        $model->fill([
            'extra1' => 'Meta Data 1',
            'extra2' => ['text' => 'Meta Data 2'],
            'title' => 'Model title 111',
            'extra13' => 'Invalid Extra',
        ]);
        $this->assertTrue($model->metaData->has('extra1'));
        $this->assertTrue($model->title == 'Model title 111');
        $this->assertFalse($model->metaData->has('extra3'));
        $this->assertFalse($model->metaData->has('extra13'));
    }

    public function test_Unset_meta_data()
    {
        $model = new Model1([
            'title' => 'Model title',
            'body' => 'Body Text',
        ]);
        $model->setMeta([
            'extra1' => 'Meta Data 1',
            'extra2' => ['text' => 'Meta Data 2'],
            'extra3' => false,
            'extra4' => 100,
            'extra5' => 1.0,
            'extra6' => null,
            'extra7' => 9999,
            'extra8' => 8888,
            'extra9' => 3232,
        ]);

        $model->unsetMeta('extra2');
        $model->unsetMeta('extra2|extra3');
        $model->unsetMeta('extra4 ,extra5');
        $model->unsetMeta(['extra6']);
        unset($model->extra7);
        unset($model['extra8']);

        $meta = $model->getMeta();

        $this->assertTrue($meta->has('extra1'));
        $this->assertFalse($meta->has('extra2'));
        $this->assertFalse($meta->has('extra3'));
        $this->assertFalse($meta->has('extra4'));
        $this->assertFalse($meta->has('extra5'));
        $this->assertFalse($meta->has('extra6'));
        $this->assertFalse($meta->has('extra7'));
        $this->assertFalse($meta->has('extra8'));
        $this->assertTrue($meta->has('extra9'));
    }

    public function test_Auto_save_meta_data_when_save_model()
    {
        $model = new Model1([
            'title' => 'Model title',
            'body' => 'Body Text',
        ]);
        $model->setMeta([
            'extra1' => 'Meta Data 1',
            'extra2' => ['text' => 'Meta Data 2'],
        ]);
        $model->setMeta('extra3', true);
        $model->setMeta('extra4');

        $model->save();

        $this->assertDatabaseHas('meta', ['metable_id' => $model->id, 'key' => 'extra1']);
        $this->assertDatabaseHas('meta', ['metable_id' => $model->id, 'key' => 'extra2']);
        $this->assertDatabaseHas('meta', ['metable_id' => $model->id, 'key' => 'extra3']);
        $this->assertDatabaseHas('meta', ['metable_id' => $model->id, 'key' => 'extra4']);
    }

    public function test_Auto_delete_meta_data_when_delete_model()
    {
        $model = new Model1([
            'title' => 'Model title',
            'body' => 'Body Text',
        ]);
        $model->setMeta([
            'extra1' => 'Meta Data 1',
            'extra2' => ['text' => 'Meta Data 2'],
        ]);

        $model->save();

        $model = Model1::find($model->id);
        $model->delete();

        $this->assertDatabaseMissing('meta', ['metable_id' => $model->id, 'key' => 'extra1']);
        $this->assertDatabaseMissing('meta', ['metable_id' => $model->id, 'key' => 'extra2']);
    }

    public function test_Meta_data_mutator()
    {
        $model = new Model1([
            'title' => 'Model title',
            'body' => 'Body Text',
        ]);
        $datetime = Carbon::now();
        $model2 = new Model1();
        $model3 = Model1::create(['title' => 'foo', 'body' => 'bar']);
        $model->setMeta([
            'extra_array' => ['foo' => 'bar'],
            'extra_datetime' => $datetime,
            'extra_model' => $model2,
            'extra_model_exists' => $model3,
            'extra_object' => (object)['foo' => 'bar'],
            'extra_boolean' => true,
            'extra_integer' => 100,
            'extra_double' => (double)100,
            'extra_float' => (float)100,
            'extra_string' => 'meta data',
            'extra_null' => null,
        ]);
        $model->save();
        $this->assertDatabaseHas('meta', [
            'metable_id' => $model->id,
            'key' => 'extra_array',
            'value' => json_encode(['foo' => 'bar']),
            'type' => 'array',
        ]);
        $this->assertDatabaseHas('meta', [
            'metable_id' => $model->id,
            'key' => 'extra_datetime',
            'value' => $model->fromDateTime($datetime),
            'type' => 'datetime',
        ]);
        $this->assertDatabaseHas('meta', [
            'metable_id' => $model->id,
            'key' => 'extra_model',
            'value' => Model1::class,
            'type' => 'model',
        ]);
        $this->assertDatabaseHas('meta', [
            'metable_id' => $model->id,
            'key' => 'extra_model_exists',
            'value' => Model1::class . "#{$model3->id}",
            'type' => 'model',
        ]);
        $this->assertDatabaseHas('meta', [
            'metable_id' => $model->id,
            'key' => 'extra_object',
            'value' => json_encode((object)['foo' => 'bar']),
            'type' => 'object',
        ]);
        $this->assertDatabaseHas('meta', [
            'metable_id' => $model->id,
            'key' => 'extra_boolean',
            'value' => true,
            'type' => 'boolean',
        ]);
        $this->assertDatabaseHas('meta', [
            'metable_id' => $model->id,
            'key' => 'extra_integer',
            'value' => 100,
            'type' => 'integer',
        ]);
        $this->assertDatabaseHas('meta', [
            'metable_id' => $model->id,
            'key' => 'extra_double',
            'value' => (double)100,
            'type' => 'double',
        ]);
        $this->assertDatabaseHas('meta', [
            'metable_id' => $model->id,
            'key' => 'extra_float',
            'value' => (float)100,
            'type' => 'double', // float nhưng gettype luôn trả về double
        ]);
        $this->assertDatabaseHas('meta', [
            'metable_id' => $model->id,
            'key' => 'extra_string',
            'value' => 'meta data',
            'type' => 'string',
        ]);
        $this->assertDatabaseHas('meta', [
            'metable_id' => $model->id,
            'key' => 'extra_null',
            'value' => null,
            'type' => 'NULL',
        ]);
    }

    public function test_Meta_data_accessor()
    {
        $model = new Model1([
            'title' => 'Model title',
            'body' => 'Body Text',
        ]);
        $datetime = Carbon::now();
        $model2 = new Model1();
        $model3 = Model1::create(['title' => 'foo', 'body' => 'bar']);
        $model->setMeta([
            'extra_array' => ['foo' => 'bar'],
            'extra_datetime' => $datetime,
            'extra_model' => $model2,
            'extra_model_exists' => $model3,
            'extra_object' => (object)['foo' => 'bar'],
            'extra_boolean' => true,
            'extra_integer' => 100,
            'extra_double' => (double)100,
            'extra_float' => (float)100,
            'extra_string' => 'meta data',
            'extra_null' => null,
        ]);
        $model->save();
        /** @var Model1 $model4 */
        $model4 = Model1::find($model->id);
        $this->assertTrue($model4->metaData->get('extra_array')->value === ['foo' => 'bar']);
        $this->assertTrue($model4->metaData->get('extra_datetime')->value->toDateTimeString() == $datetime->toDateTimeString());
        $this->assertTrue($model4->metaData->get('extra_model')->value == $model2);
        $this->assertTrue($model4->metaData->get('extra_model_exists')->value->id == $model3->id);
        $this->assertTrue($model4->metaData->get('extra_object')->value == (object)['foo' => 'bar']);
        $this->assertTrue($model4->metaData->get('extra_boolean')->value === true);
        $this->assertTrue($model4->metaData->get('extra_integer')->value === 100);
        $this->assertTrue($model4->metaData->get('extra_double')->value === (double)100);
        $this->assertTrue($model4->metaData->get('extra_float')->value === (float)100);
        $this->assertTrue($model4->metaData->get('extra_string')->value === 'meta data');
        $this->assertTrue($model4->metaData->get('extra_null')->value === null);
    }

    public function test_Update_Meta_data()
    {
        $model = new Model1([
            'title' => 'Model title',
            'body' => 'Body Text',
        ]);
        $model->setMeta([
            'extra1' => 'Meta Data 1',
            'extra2' => ['text' => 'Meta Data 2'],
        ]);
        $model->setMeta('extra3', true);
        $model->setMeta('extra4');

        $model->save();
        $model1 = Model1::find($model->id);
        // Update
        unset($model1->extra1);
        $model1->setMeta('extra2', null);
        $model1->save();

        $this->assertDatabaseMissing('meta', ['metable_id' => $model->id, 'key' => 'extra1']);
        $this->assertDatabaseHas('meta', ['metable_id' => $model->id, 'key' => 'extra2', 'type' => 'NULL']);
    }
}