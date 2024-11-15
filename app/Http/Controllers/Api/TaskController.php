<?php

namespace App\Http\Controllers\Api;

use App\Enums\TaskRelationEnum;
use App\Models\Task;
use Illuminate\Http\Request;
use App\Http\Resources\TaskResource;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;

class TaskController extends ApiController
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request)
    {
        $data = $request->validated();

        $model = TaskRelationEnum::getInstance($data['task_type']);
        $taskType = $model->findOrFail($data['id']);

        $task = Task::create([
            'user_id' => $data['user_id'],
            'owner_id' => $data['owner_id'],
            'content' => $data['content'],
            'due_at' => $data['due_at'],
            'taskable_id' => $data['id'],
            'taskable_type' => $model::class
        ]);

        $task->load('owner');

        return new TaskResource($task);
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        return new TaskResource($task);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, Task $task)
    {
        $task->update($request->validated());

        return new TaskResource($task);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        $task->delete();

        $this->responseNoContent();
    }
}
