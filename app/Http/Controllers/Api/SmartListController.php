<?php

namespace App\Http\Controllers\Api;

use App\Models\SmartList;
use Illuminate\Http\Request;
use App\Http\Resources\SmartListResource;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Requests\SmartList\StoreSmartListRequest;
use App\Http\Requests\SmartList\UpdateSmartListRequest;

class SmartListController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $limit = 'all';

        $smartLists = QueryBuilder::for(SmartList::class)
            ->where('user_id', auth()->user()->id)
            ->allowedFilters([
                AllowedFilter::exact('resource_type')
            ])
            ->allowedSorts(['name', 'created_at'])
            ->defaultSort('-created_at');

        return SmartListResource::collection(($smartLists->paginateData($limit)));
    }

    /**
     * Display the specified resource.
     */
    public function show(SmartList $smartList)
    {
        return new SmartListResource($smartList);
    }

     /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSmartlistRequest $request)
    {
        $data = $request->validated();

        $smartList = Smartlist::create($data);

        return new SmartlistResource($smartList);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSmartListRequest $request, SmartList $smartList)
    {
        $smartList->update($request->validated());

        return new SmartListResource($smartList);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SmartList $smartList)
    {
        $smartList->delete();

        $this->responseNoContent();
    }
}
