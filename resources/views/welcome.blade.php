<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel Todo</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet"/>

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.class = `mb-4 p-4 rounded-lg text-white ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} transition-opacity duration-300`;
            toast.textContent = message;

            container.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => {
                    container.removeChild(toast);
                }, 300);
            }, 3000);
        }
    </script>
</head>
<body>
<div class="min-h-screen bg-gray-50">
    <div id="toast-container" class="fixed top-4 right-4 z-50"></div>
    <div class="max-w-2xl mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Tasks</h1>

        <header class="sticky top-4 z-10">
            <form @submit.prevent="handleSubmit" class="bg-white shadow-md rounded-xl p-4 mb-4">
                <div class="flex items-start gap-3">
                    <div class="w-12 h-12 rounded-full bg-gray-200 flex-shrink-0"></div>
                    <div class="flex-grow">
                            <textarea
                                x-model="newTaskText"
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
                            class="w-6 h-6 rounded-full border-2 flex items-center justify-center flex-shrink-0 mt-1 transition-colors"
                            :class="todo.completed ? 'bg-[#1DA1F2] border-[#1DA1F2]' : 'border-gray-300 hover:border-[#1DA1F2]'"
                        >
                            <template x-if="todo.completed">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="16"
                                    height="16"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="w-4 h-4 text-white"
                                >
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                            </template>
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
                                       :class="todo.completed ? 'line-through text-gray-500' : 'text-gray-900'"
                                       x-text="todo.text"></p>
                                    <div class="flex items-center justify-between text-sm text-gray-500">
                                        <span x-text="new Date(todo.createdAt).toLocaleDateString()"></span>
                                        <div class="flex items-center gap-2">
                                            <button
                                                @click="todo.isEditing = true"
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
