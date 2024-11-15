<?php

namespace App\Http\Controllers\Api;

use App\Models\Document;
use Illuminate\Http\Request;
use App\Enums\DocumentModelEnum;
use App\Http\Resources\DocumentResource;
use App\Http\Requests\Document\StoreDocumentRequest;
use App\Http\Requests\Document\UpdateDocumentRequest;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DocumentController extends ApiController
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDocumentRequest $request)
    {
        $data = $request->validated();

        $model = DocumentModelEnum::getInstance($data['model_type']);
        $resource = $model->findOrFail($data['model_id']);

        $resource->addMediaFromRequest('file')
            ->toMediaCollection($data['collection']);

        return $this->responseNoContent();
    }

    public function update(Media $media, UpdateDocumentRequest $request)
    {
        $newFileName = $request->name . '.' . explode('.', $media->file_name)[1];
        $newFilePath = (explode('/', $media->getPathRelativeToRoot()))[0] . '/' . $newFileName;

        Storage::move($media->getPathRelativeToRoot(), $newFileName);

        $media->update([
            'name' => $request->name,
            'file_name' => $newFileName
        ]);

        return $this->responseNoContent();
    }

    public function destroy(Media $media)
    {
        $media->delete();

        return $this->responseNoContent();
    }
}
