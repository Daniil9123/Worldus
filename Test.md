# Документация по тестированию

## Общая информация
Проект использует PHPUnit для модульного и интеграционного тестирования. Тесты находятся в папке `tests`. Конфигурация PHPUnit задана в файле `phpunit.xml`.

## Структура тестов

- `tests/AuthServiceTest.php` — модульные тесты для `AuthService`.
- `tests/AuthServiceIntegrationTest.php` — интеграционные тесты для `AuthService`.
- `tests/CategoryServiceTest.php` — модульные тесты для `CategoryService`.
- `tests/CategoryServiceIntegrationTest.php` — интеграционные тесты для `CategoryService`.
- `tests/LevelServiceTest.php` — модульные тесты для `LevelService`.
- `tests/LevelServiceIntegrationTest.php` — интеграционные тесты для `LevelService`.
- `tests/QuestionServiceTest.php` — модульные тесты для `QuestionService`.
- `tests/QuestionServiceIntegrationTest.php` — интеграционные тесты для `QuestionService`.
- `tests/WebE2ETest.php` — E2E-тесты взаимодействия с веб-приложением.
- `tests/IntegrationTestCase.php` — базовый класс для интеграционных тестов.
- `tests/E2EClient.php` — HTTP-клиент для E2E-тестов.

## Запуск тестов

1. Откройте терминал в корне проекта `d:\xampp\htdocs\Worldus`.
2. Убедитесь, что установлен Composer-зависимости и доступен `vendor/bin/phpunit`.
3. Выполните команду:

```bash
vendor\bin\phpunit
```

Если вы используете Windows PowerShell:

```powershell
.
vendor\bin\phpunit.bat
```

## Окружение для интеграционных тестов

- Интеграционные тесты используют базу данных `worldus_test` на `localhost`.
- В `tests/IntegrationTestCase.php` выполняется очистка таблиц `progress`, `answers`, `questions`, `levels`, `categories` и `users` перед каждым тестом.
- Убедитесь, что база данных `worldus_test` существует и доступна пользователю `root` без пароля, либо измените настройки подключения в `tests/IntegrationTestCase.php`.

## E2E-тесты

`tests/WebE2ETest.php` выполняет проверку веб-интерфейса через HTTP.

- Базовый URL задаётся переменной окружения `WORLDUS_BASE_URL`, по умолчанию `http://localhost/Worldus`.
- Перед запуском E2E-тестов необходимо, чтобы локальный веб-сервер Apache был запущен и приложение было доступно по указанному URL.

## Рекомендации

- Перед выполнением интеграционных или E2E-тестов убедитесь, что тестовое окружение не влияет на рабочую базу данных.
- При необходимости создайте отдельную копию базы данных и укажите её в тестовой конфигурации.
- Используйте `--filter` для запуска отдельных тестов, например:

```bash
vendor\bin\phpunit --filter AuthServiceTest
```

## Частые задачи

- Запустить только интеграционные тесты:

```bash
vendor\bin\phpunit --testsuite "Worldus Test Suite" --filter Integration
```

- Запустить только E2E-тесты:

```bash
vendor\bin\phpunit --filter WebE2ETest
```
