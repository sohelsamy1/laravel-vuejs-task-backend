<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Task\TaskCollection;
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
}
