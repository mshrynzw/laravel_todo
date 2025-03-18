<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Laravel Todo</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet"/>

    <!-- Styles / Scripts -->
    <script src="//unpkg.com/alpinejs" defer></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `mb-4 p-4 rounded-lg text-white ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} transition-opacity duration-300`;
            toast.textContent = message;

            container.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => {
                    container.removeChild(toast);
                }, 300);
            }, 3000);
        }

        document.addEventListener('alpine:init', () => {
            Alpine.data('taskApp', () => ({
                todos: @json($todos),
                newTaskText: '',

                async handleSubmit() {
                    if (!this.newTaskText.trim()) return;

                    try {
                        const response = await fetch('/tasks', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                content: this.newTaskText
                            })
                        });

                        if (!response.ok) {
                            const data = await response.json();
                            throw new Error(data.error || 'エラーが発生しました');
                        }

                        const data = await response.json();
                        this.todos.unshift(data);
                        this.newTaskText = '';
                        showToast('タスクを追加しました');
                    } catch (error) {
                        showToast(error.message || 'エラーが発生しました', 'error');
                    }
                },

                async handleEdit(todo) {
                    try {
                        const response = await fetch(`/tasks/${todo.id}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                content: todo.editText
                            })
                        });

                        if (!response.ok) {
                            const data = await response.json();
                            throw new Error(data.error || 'エラーが発生しました');
                        }

                        const updatedTask = await response.json();
                        todo.content = updatedTask.content;
                        todo.isEditing = false;
                        showToast('タスクを更新しました');
                    } catch (error) {
                        console.error('Error details:', error);
                        showToast(error.message || 'エラーが発生しました', 'error');
                    }
                },

                async toggleTodo(id) {
                    const todo = this.todos.find(t => t.id === id);
                    if (!todo) return;

                    try {
                        const response = await fetch(`/tasks/${id}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                completed: !todo.completed
                            })
                        });

                        const updatedTask = await response.json();
                        todo.completed = updatedTask.completed;
                        showToast(todo.completed ? 'タスクを完了しました' : 'タスクを未完了に戻しました');
                    } catch (error) {
                        showToast('エラーが発生しました', 'error');
                    }
                },

                async deleteTodo(id) {
                    try {
                        const response = await fetch(`/tasks/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });

                        if (!response.ok) {
                            const data = await response.json();
                            throw new Error(data.error || 'エラーが発生しました');
                        }

                        // 成功したら配列からタスクを削除
                        this.todos = this.todos.filter(todo => todo.id !== id);
                        showToast('タスクを削除しました');
                    } catch (error) {
                        console.error('Error details:', error);
                        showToast(error.message || 'エラーが発生しました', 'error');
                    }
                }
            }));
        });
    </script>
</head>
<body>
    <div id="toast-container" class="fixed top-4 right-4 z-50"></div>
    <div class="min-h-screen bg-gray-50" x-data="taskApp">
        <div class="max-w-2xl mx-auto px-4 py-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">Tasks</h1>

            <header class="sticky top-4 z-10">
                <form @submit.prevent="handleSubmit" class="bg-white shadow-md rounded-xl p-4 mb-4">
                    <div class="flex items-start gap-3">
                        <div class="w-12 h-12 rounded-full bg-gray-200 flex-shrink-0"></div>
                        <div class="flex-grow">
                                <textarea
                                    x-model="newTaskText"
                                    id="newTaskText"
                                    name="content"
                                    placeholder="何をする必要がありますか？"
                                    class="w-full p-2 min-h-[80px] text-lg border-b focus:border-blue-500 focus:outline-none resize-none"
                                ></textarea>
                            <div class="flex justify-end mt-2">
                                <button
                                    type="submit"
                                    :disabled="!newTaskText.trim()"
                                    class="bg-blue-500 text-white px-6 py-2 rounded-full font-semibold flex items-center gap-2 hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="18"
                                        height="18"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="white"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    >
                                        <path d="M5 12h14M12 5l7 7-7 7"/>
                                    </svg>
                                    タスクを追加
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </header>

            <main class="space-y-4">
                <template x-for="todo in todos" :key="todo.id">
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-4 mb-3 transition-opacity"
                         :class="{ 'opacity-75': todo.completed }">
                        <div class="flex items-start gap-3">
                            <button
                                @click="toggleTodo(todo.id)"
                                class="w-6 h-6 rounded-full border-2 flex items-center justify-center flex-shrink-0 mt-1 transition-colors cursor-pointer"
                                :class="todo.completed ? 'bg-blue-500 border-blue-500 hover:bg-blue-600 hover:border-blue-600' : 'border-gray-300 hover:border-blue-500'"
                            >
                                <svg
                                    x-show="todo.completed"
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="16"
                                    height="16"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="white"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                            </button>

                            <div class="flex-grow">
                                <template x-if="todo.isEditing">
                                    <div class="space-y-2">
                                            <textarea
                                                x-model="todo.editText"
                                                class="w-full p-2 border rounded-lg focus:outline-none focus:border-[#1DA1F2]"
                                                autofocus
                                            ></textarea>
                                        <div class="flex justify-end gap-2">
                                            <button
                                                @click="todo.isEditing = false"
                                                class="px-4 py-1 rounded-full text-gray-600 hover:bg-gray-100"
                                            >
                                                キャンセル
                                            </button>
                                            <button
                                                @click="handleEdit(todo)"
                                                class="px-4 py-1 rounded-full bg-[#1DA1F2] text-white hover:bg-[#1a91da]"
                                            >
                                                保存
                                            </button>
                                        </div>
                                    </div>
                                </template>

                                <template x-if="!todo.isEditing">
                                    <div class="space-y-1">
                                        <p class="text-lg"
                                           :class="todo.completed ? 'line-through text-gray-500' : 'text-white'"
                                           x-text="todo.content"></p>
                                        <div class="flex items-center justify-between text-sm text-gray-500">
                                            <span x-text="new Date(todo.created_at).toLocaleString('ja-JP', {
                                                year: 'numeric',
                                                month: 'long',
                                                day: 'numeric',
                                                hour: '2-digit',
                                                minute: '2-digit',
                                                hour12: false
                                            })"></span>
                                            <div class="flex items-center gap-2">
                                                <button
                                                    @click="todo.editText = todo.content; todo.isEditing = true"
                                                    class="p-1 hover:text-[#1DA1F2] transition-colors"
                                                >
                                                    <svg
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        width="18"
                                                        height="18"
                                                        viewBox="0 0 24 24"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        stroke-width="2"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        class="w-[18px] h-[18px]"
                                                    >
                                                        <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/>
                                                    </svg>
                                                </button>
                                                <button
                                                    @click="deleteTodo(todo.id)"
                                                    class="p-1 hover:text-red-500 transition-colors"
                                                >
                                                    <svg
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        width="18"
                                                        height="18"
                                                        viewBox="0 0 24 24"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        stroke-width="2"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        class="w-[18px] h-[18px] black"
                                                    >
                                                        <path d="M3 6h18"/>
                                                        <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/>
                                                        <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                                                        <line x1="10" y1="11" x2="10" y2="17"/>
                                                        <line x1="14" y1="11" x2="14" y2="17"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>

                <template x-if="todos.length === 0">
                    <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                        まだタスクがありません。上から追加してください！
                    </div>
                </template>
            </main>
        </div>
    </div>
</body>
</html>
