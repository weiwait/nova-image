<?php

namespace Weiwait\NovaImage;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Http\Requests\NovaRequest;

class Image extends File
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'nova-image';


    public function __construct($name, $attribute = null, $disk = 'public', $storageCallback = null)
    {
        parent::__construct($name, $attribute, $disk, $storageCallback);

        $this->acceptedTypes('image/*');

        $this->thumbnail(function () {
            return $this->value ? Storage::disk($this->getStorageDisk())->url($this->value) : null;
        })->preview(function () {
            return $this->value ? Storage::disk($this->getStorageDisk())->url($this->value) : null;
        });
    }

    protected function fillAttribute(NovaRequest $request, $requestAttribute, $model, $attribute)
    {
        if ($request->exists($requestAttribute)) {
            if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $request[$requestAttribute], $res)) {
                $filename = 'images/' . md5(Str::uuid()) . '.' . $res[2];
                Storage::disk()->put($filename, base64_decode(str_replace($res[1], '', $request[$requestAttribute])));
                $model->{$attribute} = $filename;

                if ($this->sizeColumn) {
                    $model[$this->sizeColumn] = Storage::disk()->size($filename);
                }

                if ($this->originalNameColumn && !empty($request['originalName'])) {
                    $model[$this->originalNameColumn] = $request['originalName'];
                }
            } elseif (isset($model->getOriginal()[$requestAttribute])) {
                Storage::disk($this->getStorageDisk())->delete($model->getOriginal()[$requestAttribute]);
                $model->{$attribute} = null;
            }
        }
    }

    public function cropper(array $options = []): Image
    {
        return $this->withMeta(['cropper' => $options]);
    }

    protected function mergeExtraStorageColumns($request, array $attributes)
    {
        return $attributes;
    }
}
