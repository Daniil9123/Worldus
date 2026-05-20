# README

## RUS:

Worldus — это обучающая веб-платформа в формате квеста, где пользователь проходит уровни, отвечает на вопросы и открывает новые категории.
Проект вдохновлён игровыми системами обучения наподобие Duolingo.

В системе реализованы:

* регистрация и авторизация;
* категории;
* уровни;
* вопросы разных типов;
* система прогресса;
* административная панель;
* защита паролей;
* загрузка изображений.

Поддерживаются следующие типы вопросов:

* choice — выбор ответа;
* image — выбор изображения;
* input — ввод текста.

---

## Используемые технологии:

* PHP
* MySQL / MariaDB
* HTML
* CSS
* JavaScript
* XAMPP

---

## Основные возможности:

### Пользователь:

* регистрация;
* авторизация;
* прохождение уровней;
* сохранение прогресса;
* система блокировки уровней;
* отображение правильных и неправильных ответов.

### Администратор:

* создание категорий;
* создание уровней;
* создание вопросов;
* загрузка изображений;
* редактирование контента;
* удаление категорий и уровней.

---

## Запуск проекта

### Для запуска необходимо:

1. Установить XAMPP

2. Переместить папку проекта в:
   htdocs/

3. Запустить:

* Apache
* MySQL

4. Импортировать базу данных через phpMyAdmin

5. Открыть браузер и перейти по ссылке:
   [http://localhost/Worldus](http://localhost/Worldus)

---

## Безопасность

В проекте используются:

* password_hash();
* password_verify();
* проверка ролей администратора;
* валидация паролей.

---

## ENG:

Worldus is an educational quest-style web platform where users complete levels, answer questions, and unlock new categories.
The project is inspired by game-based learning systems similar to Duolingo.

The system includes:

* registration and login;
* categories;
* levels;
* multiple question types;
* progress tracking;
* admin panel;
* password protection;
* image uploading.

Supported question types:

* choice — multiple choice;
* image — image selection;
* input — text input.

---

## Technologies Used:

* PHP
* MySQL / MariaDB
* HTML
* CSS
* JavaScript
* XAMPP

---

## Main Features:

### User:

* registration;
* authorization;
* level progression;
* progress saving;
* locked levels system;
* correct and incorrect answer display.

### Administrator:

* create categories;
* create levels;
* create questions;
* upload images;
* edit content;
* delete categories and levels.

---

## Running the Project

### To start the project:

1. Install XAMPP

2. Move the project folder into:
   htdocs/

3. Start:

* Apache
* MySQL

4. Import the database using phpMyAdmin

5. Open the browser and go to:
   [http://localhost/Worldus](http://localhost/Worldus)

---

## Security

The project uses:

* password_hash();
* password_verify();
* admin role verification;
* password validation.
