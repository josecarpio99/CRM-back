<?php

namespace App\Http\Controllers\Api;

use App\Models\Contact;
use Illuminate\Http\Request;
use App\Enums\ContactRelationEnum;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Http\Resources\ContactResource;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Contact\StoreContactRequest;
use App\Http\Requests\Contact\UpdateContactRequest;

class ContactController extends ApiController
{

    public function index(Request $request)
    {
        $limit = $request->limit ?? 20;

        $contacts = QueryBuilder::for(Contact::class)
            ->allowedFilters([
                AllowedFilter::callback('search', function(Builder $query, $value) {
                    $query->where(function($query) use($value) {
                        $query->where('name', 'like', '%'.$value.'%')
                            ->orWhere('phone', 'like', '%'.$value.'%')
                            ->orWhere('email', 'like', '%'.$value.'%');
                    });
                }),
                AllowedFilter::callback('contactable_type', function(Builder $query, $value) {
                    $query->where('contactable_type', ContactRelationEnum::getMorphClass($value));
                }),
                AllowedFilter::exact('contactable_id')
            ])
            ->defaultSort('-name');

        return ContactResource::collection(($contacts->paginateData($limit)));
    }

    public function store(StoreContactRequest $request)
    {
        $model = $request->getModel();

        $contact = $model->contacts()->create(
            $request->only('name', 'phone', 'email', 'email2', 'phone2')
        );

        return new ContactResource($contact);
    }

    public function update(UpdateContactRequest $request, Contact $contact)
    {
        $contact->update($request->only('name', 'phone', 'email', 'email2', 'phone2'));

        return new ContactResource($contact);
    }
}
