<?php

namespace App\Http\Controllers\Api;

use App\Models\Note;
use App\Models\User;
use App\Models\Branch;
use App\Enums\RoleEnum;
use App\Mail\NewNoteCreated;
use Illuminate\Http\Request;
use App\Enums\NoteRelationEnum;
use App\Http\Resources\NoteResource;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Notifications\NewNoteNotification;
use App\Http\Requests\Note\StoreNoteRequest;
use Illuminate\Support\Facades\Notification;
use App\Http\Requests\Note\UpdateNoteRequest;

class NoteController extends ApiController
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreNoteRequest $request)
    {
        $data = $request->validated();

        $model = NoteRelationEnum::getInstance($data['note_type']);
        $noteType = $model->findOrFail($data['id']);

        $owner = $noteType->owner;
        $branch = Branch::where('name', $owner->branch)->first();

        $note = Note::create([
            'user_id' => $data['user_id'],
            'content' => $data['content'],
            'noteable_id' => $data['id'],
            'noteable_type' => $model::class
        ]);

        if ($note->user_id != $noteType->owner_id) {
            $noteType->owner->notify(new NewNoteNotification($note));
        }

        // $usersToNotify = User::query()
        //     ->where('id', '<>', $owner->id)
        //     ->where(function($query) use($branch){
        //         $query->where('role', RoleEnum::Admin->value)
        //             ->where(function($query) use($branch) {
        //                 $query->where('branch', $branch->name)
        //                     ->orWhereHas('branches', function($query) use($branch) {
        //                         $query->where('branch_id', $branch->id);
        //                     });
        //             });
        //     })
        //     ->orWhere(function($query) use($owner){
        //         $query->where('role', RoleEnum::TeamLeader->value)
        //             ->whereHas('assignedUsers', function($query) use($owner) {
        //                 $query->where('user_id', $owner->id);
        //             });
        //     })
        //     ->get();

        // if ($usersToNotify->count() > 0) {
        //     \Mail::to($usersToNotify)->send(new NewNoteCreated($note));
        //     // Notification::send($usersToNotify, new NewNoteNotification($note));
        // }

        return new NoteResource($note);
    }

    /**
     * Display the specified resource.
     */
    public function show(Note $note)
    {
        return new NoteResource($note);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateNoteRequest $request, Note $note)
    {
        $note->update($request->validated());

        return new NoteResource($note);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Note $note)
    {
        $note->delete();

        $this->responseNoContent();
    }
}
