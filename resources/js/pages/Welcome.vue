<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Welcome">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    <div class="bg-gradient-to-b from-blue-50 via-purple-50 to-pink-50 min-h-screen flex flex-col">
        <header class="bg-gradient-to-r from-blue-500 to-purple-600 text-white py-16 shadow-lg">
            <div class="container mx-auto px-4 flex flex-col items-center">
                <h1 class="text-4xl sm:text-5xl font-bold mb-4 text-center">Персональный Telegram-бот для управления финансами и покупками</h1>
                <p class="text-lg sm:text-xl mb-8 text-center max-w-2xl">Контролируйте финансы, следите за бюджетами, ведите списки покупок и храните закладки — всё прямо в Telegram!</p>
                <a href="https://t.me/sergeyem_bot" class="bg-white text-blue-700 font-semibold px-8 py-3 rounded-lg shadow hover:bg-blue-100 transition">Добавить бота в Telegram</a>
            </div>
        </header>

        <!-- Integrations Section -->
        <section class="container mx-auto px-4 py-12">
            <h2 class="text-2xl font-bold text-center mb-10 text-gray-800">Интеграции</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
                    <img src="https://cdn.jsdelivr.net/gh/simple-icons/simple-icons/icons/firefox.svg" alt="Firefly" class="h-12 mb-4 opacity-80">
                    <h3 class="font-semibold text-lg mb-2">Firefly III</h3>
                    <p class="text-gray-600 text-center">Управляйте своими финансами, бюджетами и расходами прямо из Telegram с помощью интеграции с Firefly.</p>
                </div>
                <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
                    <img src="https://cdn.jsdelivr.net/gh/simple-icons/simple-icons/icons/evernote.svg" alt="EasyList" class="h-12 mb-4 opacity-80">
                    <h3 class="font-semibold text-lg mb-2">EasyList</h3>
                    <p class="text-gray-600 text-center">Ведите и обновляйте список покупок в удобном формате, интегрируясь с сервисом EasyList.</p>
                </div>
                <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
                    <img src="https://cdn.jsdelivr.net/npm/@svg-icons/octicons@1.86.0/book.svg" alt="LinkAce" class="h-12 mb-4 opacity-80">
                    <h3 class="font-semibold text-lg mb-2">LinkAce</h3>
                    <p class="text-gray-600 text-center">Храните и организуйте закладки на внешнем сервисе LinkAce, прямо через Telegram-бота.</p>
                </div>
            </div>
        </section>

        <!-- Commands Section -->
        <section class="bg-white py-12 border-t border-b border-gray-100">
            <div class="container mx-auto px-4">
                <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">Доступные команды бота</h2>
                <div class="flex justify-center">
                    <ul class="bg-gray-50 rounded-xl shadow max-w-2xl w-full divide-y divide-gray-100">
                        <li class="flex items-center px-6 py-4"><span class="font-mono text-blue-600 mr-4">/help</span> — список доступных команд</li>
                        <li class="flex items-center px-6 py-4"><span class="font-mono text-blue-600 mr-4">/budget</span> — список бюджетов</li>
                        <li class="flex items-center px-6 py-4"><span class="font-mono text-blue-600 mr-4">/balance</span> — баланс по финансам</li>
                        <li class="flex items-center px-6 py-4"><span class="font-mono text-blue-600 mr-4">/minus</span> — зарегистрировать расходную операцию</li>
                        <li class="flex items-center px-6 py-4"><span class="font-mono text-blue-600 mr-4">/transfer</span> — перевод между счетами</li>
                        <li class="flex items-center px-6 py-4"><span class="font-mono text-blue-600 mr-4">/accounts</span> — список счетов</li>
                        <li class="flex items-center px-6 py-4"><span class="font-mono text-blue-600 mr-4">/transactions</span> — последние транзакции</li>
                        <li class="flex items-center px-6 py-4"><span class="font-mono text-blue-600 mr-4">/categories</span> — статистика по категориям</li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- Call to Action -->
        <section class="container mx-auto px-4 py-16 flex flex-col items-center">
            <h2 class="text-2xl font-bold mb-4 text-gray-800 text-center">Начните управлять своими финансами и покупками уже сейчас!</h2>
            <a href="https://t.me/sergeyem_bot" class="bg-blue-600 text-white font-semibold px-8 py-3 rounded-lg shadow hover:bg-blue-700 transition">Запустить Telegram-бота</a>
        </section>

        <!-- Footer -->
        <footer class="py-6 border-t border-gray-200 text-center text-gray-500 text-sm">
            &copy; 2024 Ваш Telegram-Бот. Все права защищены.
        </footer>
    </div>
</template>
