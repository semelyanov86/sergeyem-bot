<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Welcome">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    <div class="flex min-h-screen flex-col bg-gradient-to-b from-blue-50 via-purple-50 to-pink-50">
        <header class="bg-gradient-to-r from-blue-500 to-purple-600 py-16 text-white shadow-lg">
            <div class="container mx-auto flex flex-col items-center px-4">
                <h1 class="mb-4 text-center text-4xl font-bold sm:text-5xl">Персональный Telegram-бот для управления финансами и покупками</h1>
                <p class="mb-8 max-w-2xl text-center text-lg sm:text-xl">
                    Контролируйте финансы, следите за бюджетами, ведите списки покупок и храните закладки — всё прямо в Telegram!
                </p>
                <a
                    href="https://t.me/sergeyem_bot"
                    class="rounded-lg bg-white px-8 py-3 font-semibold text-blue-700 shadow transition hover:bg-blue-100"
                    >Добавить бота в Telegram</a
                >
            </div>
        </header>

        <!-- Integrations Section -->
        <section class="container mx-auto px-4 py-12">
            <h2 class="mb-10 text-center text-2xl font-bold text-gray-800">Интеграции</h2>
            <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                <div class="flex flex-col items-center rounded-xl bg-white p-6 shadow">
                    <img src="https://cdn.jsdelivr.net/gh/simple-icons/simple-icons/icons/firefox.svg" alt="Firefly" class="mb-4 h-12 opacity-80" />
                    <h3 class="mb-2 text-lg font-semibold">Firefly III</h3>
                    <p class="text-center text-gray-600">
                        Управляйте своими финансами, бюджетами и расходами прямо из Telegram с помощью интеграции с Firefly.
                    </p>
                </div>
                <div class="flex flex-col items-center rounded-xl bg-white p-6 shadow">
                    <img src="https://cdn.jsdelivr.net/gh/simple-icons/simple-icons/icons/evernote.svg" alt="EasyList" class="mb-4 h-12 opacity-80" />
                    <h3 class="mb-2 text-lg font-semibold">EasyList</h3>
                    <p class="text-center text-gray-600">Ведите и обновляйте список покупок в удобном формате, интегрируясь с сервисом EasyList.</p>
                </div>
                <div class="flex flex-col items-center rounded-xl bg-white p-6 shadow">
                    <img src="https://cdn.jsdelivr.net/npm/@svg-icons/octicons@1.86.0/book.svg" alt="LinkAce" class="mb-4 h-12 opacity-80" />
                    <h3 class="mb-2 text-lg font-semibold">LinkAce</h3>
                    <p class="text-center text-gray-600">Храните и организуйте закладки на внешнем сервисе LinkAce, прямо через Telegram-бота.</p>
                </div>
            </div>
        </section>

        <!-- Commands Section -->
        <section class="border-t border-b border-gray-100 bg-white py-12">
            <div class="container mx-auto px-4">
                <h2 class="mb-6 text-center text-2xl font-bold text-gray-800">Доступные команды бота</h2>
                <div class="flex justify-center">
                    <ul class="w-full max-w-2xl divide-y divide-gray-100 rounded-xl bg-gray-50 shadow">
                        <li class="flex items-center px-6 py-4"><span class="mr-4 font-mono text-blue-600">/help</span> — список доступных команд</li>
                        <li class="flex items-center px-6 py-4"><span class="mr-4 font-mono text-blue-600">/budgets</span> — список бюджетов</li>
                        <li class="flex items-center px-6 py-4"><span class="mr-4 font-mono text-blue-600">/balance</span> — баланс по финансам</li>
                        <li class="flex items-center px-6 py-4">
                            <span class="mr-4 font-mono text-blue-600">/expense</span> — зарегистрировать расходную операцию
                        </li>
                        <li class="flex items-center px-6 py-4">
                            <span class="mr-4 font-mono text-blue-600">/transfer</span> — перевод между счетами
                        </li>
                        <li class="flex items-center px-6 py-4"><span class="mr-4 font-mono text-blue-600">/accounts</span> — список счетов</li>
                        <li class="flex items-center px-6 py-4">
                            <span class="mr-4 font-mono text-blue-600">/transactions</span> — последние транзакции
                        </li>
                        <li class="flex items-center px-6 py-4">
                            <span class="mr-4 font-mono text-blue-600">/categories</span> — статистика по категориям
                        </li>
                        <li class="flex items-center px-6 py-4">
                            <span class="mr-4 font-mono text-blue-600">/budgets</span> — статистика по бюджетам
                        </li>
                        <li class="flex items-center px-6 py-4">
                            <span class="mr-4 font-mono text-blue-600">/start</span> — Зарегистрироваться в сервисе
                        </li>
                        <li class="flex items-center px-6 py-4"><span class="mr-4 font-mono text-blue-600">/cancel</span> — Сбросить контекст</li>
                        <li class="flex items-center px-6 py-4">
                            <span class="mr-4 font-mono text-blue-600">/link</span> — Сохранить ссылку в linkace
                        </li>
                        <li class="flex items-center px-6 py-4">
                            <span class="mr-4 font-mono text-blue-600">/links</span> — Получить последние ссылки
                        </li>
                        <li class="flex items-center px-6 py-4">
                            <span class="mr-4 font-mono text-blue-600">/listlinks</span> — Вывести ссылки из определённого списка
                        </li>
                        <li class="flex items-center px-6 py-4">
                            <span class="mr-4 font-mono text-blue-600">/buylists</span> — Вывести список покупок
                        </li>
                        <li class="flex items-center px-6 py-4">
                            <span class="mr-4 font-mono text-blue-600">/items</span> — Выводит товары из заданного списка
                        </li>
                        <li class="flex items-center px-6 py-4">
                            <span class="mr-4 font-mono text-blue-600">/random</span> — Выводит рандомные слова для изучения
                        </li>
                        <li class="flex items-center px-6 py-4">
                            <span class="mr-4 font-mono text-blue-600">/saveword</span> — Запускает процесс сохранения слов
                        </li>
                        <li class="flex items-center px-6 py-4">
                            <span class="mr-4 font-mono text-blue-600">/delete</span> — Удаляет транзакцию по идентификатору
                        </li>
                        <li class="flex items-center px-6 py-4"><span class="mr-4 font-mono text-blue-600">/rates</span> — Выводит курсы валют</li>
                        <li class="flex items-center px-6 py-4">
                            <span class="mr-4 font-mono text-blue-600">/check</span> — Проверяет соединение с сайтами
                        </li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- Call to Action -->
        <section class="container mx-auto flex flex-col items-center px-4 py-16">
            <h2 class="mb-4 text-center text-2xl font-bold text-gray-800">Начните управлять своими финансами и покупками уже сейчас!</h2>
            <a href="https://t.me/sergeyem_bot" class="rounded-lg bg-blue-600 px-8 py-3 font-semibold text-white shadow transition hover:bg-blue-700"
                >Запустить Telegram-бота</a
            >
        </section>

        <!-- Footer -->
        <footer class="border-t border-gray-200 py-6 text-center text-sm text-gray-500">&copy; 2024 Ваш Telegram-Бот. Все права защищены.</footer>
    </div>
</template>
