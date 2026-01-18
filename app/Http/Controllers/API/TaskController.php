<?php

namespace App\Http\Controllers\API;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Task\TaskResource;
use App\Http\Resources\Task\TaskCollection;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
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

     public function show(Task $task): JsonResponse
    {
        try {
            $this->authorize('view', $task);
            return $this->success(new TaskResource($task), 'Task details retrieved');
        } catch (\Throwable $e) {
            return $this->error('Unauthorized or Task not found', 403, $e);
        }
    }

     public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        try {
            $this->authorize('update', $task);
            $task->update($request->validated());
            return $this->success(new TaskResource($task), 'Task updated successfully');
        } catch (\Throwable $e) {
            return $this->error('Failed to update task', 500, $e);
        }
    }

     public function destroy(Task $task): JsonResponse
    {
        try {
            $this->authorize('delete', $task);
            $task->delete();
            return $this->success(null, 'Task soft-deleted');
        } catch (\Throwable $e) {
            return $this->error('Failed to delete task', 500, $e);
        }
    }

     public function restore($id): JsonResponse
    {
        try {
            $task = Task::withTrashed()->findOrFail($id);
            $this->authorize('restore', $task);
            $task->restore();
            return $this->success(new TaskResource($task), 'Task restored successfully');
        } catch (\Throwable $e) {
            return $this->error('Failed to restore task', 500, $e);
        }
    }

     public function forceDelete($id): JsonResponse
    {
        try {
            $task = Task::withTrashed()->findOrFail($id);
            $this->authorize('forceDelete', $task);
            $task->forceDelete();
            return $this->success(null, 'Task permanently deleted');
        } catch (\Throwable $e) {
            return $this->error('Failed to permanently delete task', 500, $e);
        }
    }

     public function filter(Request $request): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:new,in_progress,completed,canceled'
        ]);

        try {
            $tasks = Auth::user()->tasks()
                ->where('status', $request->status)
                ->latest()
                ->paginate(10);

            return $this->success(new TaskCollection($tasks), 'Filtered tasks loaded');
        } catch (\Throwable $e) {
            return $this->error('Failed to filter tasks', 500, $e);
        }
    }

     public function updateStatus(Request $request, Task $task): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:new,in_progress,completed,canceled',
        ]);

        try {
            $this->authorize('update', $task);
            $task->update(['status' => $request->status]);

            return $this->success(new TaskResource($task), 'Task status updated');
        } catch (\Throwable $e) {
            return $this->error('Failed to update task status', 500, $e);
        }
    }

     public function summary(): JsonResponse
    {
        try {
            $summary = Auth::user()->tasks()
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status');

            $allStatuses = ['new', 'in_progress', 'completed', 'canceled'];
            $finalSummary = [];

            foreach ($allStatuses as $status) {
                $finalSummary[$status] = $summary[$status] ?? 0;
            }

            return $this->success($finalSummary, 'Task summary fetched successfully');
        } catch (\Throwable $e) {
            return $this->error('Failed to load task summary', 500, $e);
        }
    }

     public function trashed(): JsonResponse
    {
        try {
            $tasks = Auth::user()->tasks()
                ->onlyTrashed()
                ->latest()
                ->paginate(10);

            return $this->success(new TaskCollection($tasks), 'Trashed tasks fetched successfully');
        } catch (\Throwable $e) {
            return $this->error('Failed to fetch trashed tasks', 500, $e);
        }
    }
}
