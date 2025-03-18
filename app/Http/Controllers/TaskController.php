<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    public function index()
    {
        Log::info('インデックスメソッドが呼び出されました');
        $tasks = Task::orderBy('created_at', 'desc')->get();
        return view('welcome', ['todos' => $tasks]);
    }

    public function store(Request $request)
    {
        try {
            Log::info('タスク作成開始', [
                'request' => json_encode($request->all(), JSON_UNESCAPED_UNICODE)
            ]);

            $validated = $request->validate([
                'content' => 'required|max:255',
            ]);

            $task = Task::create([
                'content' => $validated['content'],
                'completed' => false,
            ]);

            Log::info('タスク作成成功', [
                'task' => json_encode($task->toArray(), JSON_UNESCAPED_UNICODE)
            ]);

            return response()->json($task);

        } catch (\Exception $e) {
            Log::error('タスク作成エラー', [
                'message' => $e->getMessage()
            ]);
            return response()->json(['error' => 'タスクの作成に失敗しました'], 500);
        }
    }

    public function update(Request $request, Task $task)
    {
        try {
            Log::info('タスク更新リクエスト', [
                'task_id' => $task->id,
                'request_data' => $request->all()
            ]);

            $validated = $request->validate([
                'completed' => 'boolean|nullable',
                'content' => 'string|max:255|nullable'
            ]);

            $task->update($validated);

            Log::info('タスク更新完了', [
                'task_id' => $task->id,
                'updated_data' => $task->toArray()
            ]);

            return response()->json([
                'id' => $task->id,
                'content' => $task->content,
                'completed' => $task->completed,
                'created_at' => $task->created_at
            ]);

        } catch (\Exception $e) {
            Log::error('タスク更新エラー: ' . $e->getMessage());
            return response()->json(['error' => 'タスクの更新に失敗しました'], 500);
        }
    }

    public function destroy(Task $task)
    {
        try {
            $task->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('タスク削除エラー: ' . $e->getMessage());
            return response()->json(['error' => 'タスクの削除に失敗しました'], 500);
        }
    }
}
