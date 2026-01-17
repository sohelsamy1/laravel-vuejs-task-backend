<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    // এটি true না করলে ইউজাররা ইনডেক্স লিস্ট দেখতে পারবে না
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Task $task): bool
    {
        return $user->id === $task->user_id;
    }

    // এটি true না করলে নতুন টাস্ক স্টোর (create) হবে না
    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Task $task): bool
    {
        return $user->id === $task->user_id;
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->id === $task->user_id;
    }

    public function restore(User $user, Task $task): bool
    {
        return $user->id === $task->user_id;
    }

    public function forceDelete(User $user, Task $task): bool
    {
        return $user->id === $task->user_id;
    }
}
