# Telegram Personal Finance & Shopping Bot

![Laravel](https://img.shields.io/badge/laravel-10.x-red.svg)
![PHP](https://img.shields.io/badge/php-8.3-blue.svg)
![License](https://img.shields.io/github/license/yourusername/yourrepo.svg)

A personal Telegram bot for managing finances, budgets, shopping lists, and bookmarks. Integrates with Firefly III, EasyList, and LinkAce.

---

## 🚀 Features

- **Integration with [Firefly III](https://firefly-iii.org/)** — Track expenses, budgets, balances, and transfers directly from Telegram.
- **Integration with EasyList** — Manage your shopping list conveniently from Telegram.
- **Integration with [LinkAce](https://www.linkace.org/)** — Save and organize bookmarks via a third-party service.
- Intuitive command interface through your personal Telegram bot.

---

## ⚡️ Available Commands

| Command         | Description                           |
|-----------------|--------------------------------------|
| `/help`         | Show the list of available commands   |
| `/budget`       | Display your list of budgets          |
| `/balance`      | Show current balances                 |
| `/minus`        | Register an expense                   |
| `/transfer`     | Transfer funds between accounts       |
| `/accounts`     | List all available accounts           |
| `/transactions` | Show recent transactions              |
| `/categories`   | View statistics by categories         |

---

## 🛠️ Installation

1. **Clone the repository:**
    ```bash
    git clone https://github.com/yourusername/yourrepo.git
    cd yourrepo
    ```

2. **Install dependencies:**
    ```bash
    composer install
    npm install && npm run build
    ```

3. **Create the `.env` configuration:**
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```
   Fill in your API keys for Firefly, EasyList, LinkAce, and your Telegram Bot.

4. **Run migrations and seeders:**
    ```bash
    php artisan migrate --seed
    ```

5. **Start the server:**
    ```bash
    php artisan serve
    ```

---

## ⚙️ Configuration

Add your API keys and integration settings to your `.env` file:

```
TELEGRAM_BOT_TOKEN=your_bot_token
FIREFLY_API_URL=https://firefly.example.com
FIREFLY_API_TOKEN=your_firefly_token
EASYLIST_API_URL=https://easylist.example.com
EASYLIST_API_TOKEN=your_easylist_token
LINKACE_API_URL=https://linkace.example.com
LINKACE_API_TOKEN=your_linkace_token
```

---

## 🌐 Integrations

- **Firefly III** — Connect via API to manage your finances and budgets.
- **EasyList** — Sync your shopping list.
- **LinkAce** — Save and access bookmarks from anywhere.

---

## 📦 Tech Stack

- Laravel 10.x
- PHP 8.3+
- Tailwind CSS
- Telegram Bot API
- Firefly III
- EasyList
- LinkAce

---

## 🤖 Running the Telegram Bot

The bot works via Telegram webhooks.  
Set up the webhook with:

```bash
php artisan telegram:webhook-set
```

---

## 📝 License

MIT © [your_nickname](https://github.com/semelyanov86)

---

## 💬 Contact

For questions, suggestions, or contributions, open an [Issue](https://github.com/semelyanov86/bot-new/issues) or reach out via Telegram: [@yourusername](https://t.me/sergeyem)
