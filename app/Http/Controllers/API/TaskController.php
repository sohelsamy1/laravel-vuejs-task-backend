<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Task\TaskResource;
use App\Http\Resources\Task\TaskCollection;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Controllers\API\Base\BaseApiController;


class TaskController extends BaseApiController
{

    public function index(): JsonResponse
    {
        try {
            $tasks = Auth::user()->tasks()->latest()->paginate(10);
            return $this->success(new TaskCollection($tasks), 'Tasks fetched successfully');
        } catch (\Throwable $e) {

            return $this->error('Failed to fetch tasks', 500, $e);
        }
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        try {
            $task = Auth::user()->tasks()->create($request->validated());
            return $this->success(new TaskResource($task), 'Task created successfully', 201);
        } catch (\Throwable $e) {
            return $this->error('Failed to create task', 500, $e);
        }
    }
}
