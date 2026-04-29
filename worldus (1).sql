-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1
-- Время создания: Апр 29 2026 г., 14:02
-- Версия сервера: 10.4.32-MariaDB
-- Версия PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `worldus`
--

-- --------------------------------------------------------

--
-- Структура таблицы `answers`
--

CREATE TABLE `answers` (
  `id` int(11) NOT NULL,
  `question_id` int(11) DEFAULT NULL,
  `answer_text` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_correct` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Дамп данных таблицы `answers`
--

INSERT INTO `answers` (`id`, `question_id`, `answer_text`, `image`, `is_correct`) VALUES
(1, 1, '14 век', NULL, NULL),
(2, 1, '18 век', NULL, NULL),
(3, 1, '20 век', NULL, 1),
(4, 1, '21 век', NULL, NULL),
(5, 2, NULL, 'assets/images/Flag_of_Estonia.png', 1),
(6, 2, NULL, 'assets/images/Flag_of_Russia.png', 0),
(7, 2, NULL, 'assets/images/Flag_of_Lithuania.png', 0),
(8, 3, '6 июня 1921 года', NULL, 0),
(9, 3, '1 сентября 1939 года', NULL, 0),
(10, 3, '22 июля 1940 года', NULL, 1),
(11, 3, '24 июля 1945', NULL, 0),
(12, 4, '3 апреля 1987 года', NULL, 0),
(13, 4, '8 мая 1990 года', NULL, 0),
(14, 4, '20 августа 1991 года', NULL, 1),
(15, 4, '28 июня 1992 года', NULL, 0),
(16, 5, '1992', NULL, 0),
(17, 5, '2000', NULL, 0),
(18, 5, '2004', NULL, 1),
(19, 5, '2005', NULL, 0),
(20, 6, '2004', NULL, 1),
(21, 7, 'партизаны', NULL, 1),
(22, 8, '1939-1947', NULL, 0),
(23, 8, '1940-1945', NULL, 0),
(24, 8, '1940-1953', NULL, 1),
(25, 8, '1940-1967', NULL, 0),
(26, 9, NULL, 'assets/images/German', 0),
(27, 9, NULL, 'assets/images/Baltic.png', 1),
(28, 9, NULL, 'assets/images/Europa.png', 0),
(29, 10, 'Руководитель Таллинской мануфактуры', NULL, 0),
(30, 10, 'Глава партии вапсы', NULL, 0),
(31, 10, 'Первый президент Эстонской Республике', NULL, 1),
(32, 10, 'Глава Эстляндской трудовой коммуны', NULL, 0),
(33, 11, 'Балканы', NULL, 0),
(34, 11, 'Прибалтика', NULL, 1),
(35, 11, 'Скандинавия', NULL, 0),
(36, 11, 'Иберия', NULL, 0),
(37, 12, 'Латвия, Россия', NULL, 1),
(38, 12, 'Латвия, Россия, Финляндия', NULL, 0),
(39, 12, 'Латвия, Россия, Литва', NULL, 0),
(40, 12, 'Латвия, Швеция', NULL, 0),
(41, 13, 'Ладожское озеро', NULL, 1),
(42, 13, 'Псковское озеро ', NULL, 0),
(43, 13, 'Чудское озеро', NULL, 0),
(44, 13, 'Скадарское озеро', NULL, 0),
(45, 14, NULL, 'assets/images/Jenevsk.jpg', 0),
(46, 14, NULL, 'assets/images/Lake.jpg', 0),
(47, 14, NULL, 'assets/images/Chudsk.jpg', 1),
(48, 14, NULL, 'assets/images/Ladoz.jpg', 0),
(49, 15, 'Западный Шпицберген и Эдж', NULL, 0),
(50, 15, 'Крит и Кипр', NULL, 0),
(51, 15, 'Ирландия', NULL, 0),
(52, 15, 'Сааремаа и Хийумаа', NULL, 1),
(53, 16, 'Финляндия', NULL, 1),
(54, 17, 'Равнины, болота, холмы', NULL, 0),
(55, 17, 'Равнины, холмы, горы', NULL, 0),
(56, 17, 'Низменность, болота, горы', NULL, 0),
(57, 17, 'Низменность, холмы, болота', NULL, 1),
(58, 18, '1%', NULL, 0),
(59, 18, '5%', NULL, 1),
(60, 18, '10%', NULL, 0),
(61, 18, '25%', NULL, 0),
(62, 19, '0%', NULL, 0),
(63, 19, '25%', NULL, 0),
(64, 19, '50%', NULL, 1),
(65, 19, '75%', NULL, 0),
(66, 20, 'Арктический', NULL, 0),
(67, 20, 'Умеренный', NULL, 1),
(68, 20, 'Тропический', NULL, 0),
(69, 20, 'Экваториальный', NULL, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` mediumtext DEFAULT NULL,
  `image` blob DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `image`) VALUES
(1, 'История Эстонии', '10 уровней', NULL),
(2, 'География Эстонии', '5 уровней', NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `levels`
--

CREATE TABLE `levels` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `level_order` int(11) DEFAULT NULL,
  `required_level` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `levels`
--

INSERT INTO `levels` (`id`, `category_id`, `title`, `level_order`, `required_level`) VALUES
(1, 1, 'Уровень 1', 1, NULL),
(2, 1, 'Уровень 2', 2, 1),
(3, 1, 'Уровень 3', 3, 2),
(4, 1, 'Уровень 4', 4, 3),
(5, 1, 'Уровень 5', 5, 4),
(6, 2, 'Уровень 1', 1, NULL),
(7, 2, 'Уровень 2', 2, 6),
(8, 2, 'Уровень 3', 3, 7),
(9, 2, 'Уровень 4', 4, 8),
(10, 2, 'Уровень 5', 5, 9);

-- --------------------------------------------------------

--
-- Структура таблицы `progress`
--

CREATE TABLE `progress` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `level_id` int(11) DEFAULT NULL,
  `completed` tinyint(1) DEFAULT 0,
  `score` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Дамп данных таблицы `progress`
--

INSERT INTO `progress` (`id`, `user_id`, `level_id`, `completed`, `score`) VALUES
(1, NULL, 1, 1, 0),
(2, NULL, 1, 1, 0),
(3, NULL, 1, 1, 0),
(4, NULL, 2, 1, 0),
(5, NULL, 3, 1, 0),
(6, NULL, 4, 1, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `questions`
--

CREATE TABLE `questions` (
  `id` int(11) NOT NULL,
  `level_id` int(11) DEFAULT NULL,
  `question_text` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `type` enum('input','choice','image') DEFAULT 'choice'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Дамп данных таблицы `questions`
--

INSERT INTO `questions` (`id`, `level_id`, `question_text`, `image`, `type`) VALUES
(1, 1, 'В каком веку образовалась Эстония как страна?', NULL, 'choice'),
(2, 1, 'Выберите картинку, которая обозначает флаг Эстонии', NULL, 'image'),
(3, 2, 'Когда Эстония вступила в состав СССР?', NULL, 'choice'),
(4, 2, 'Когда Эстония обрела независимость?', NULL, 'choice'),
(5, 3, 'В каком году Эстония вступила в НАТО?', NULL, 'choice'),
(6, 3, 'В каком году Эстония вступила в Европейский Союз?', NULL, 'input'),
(7, 4, 'Кто такие Лесные Братья?', NULL, 'input'),
(8, 4, 'Когда началось движение Лесных Братьев и его окончание?', NULL, 'choice'),
(9, 5, 'Где находится Эстония?', NULL, 'image'),
(10, 5, 'Кто такой Константин Пятс?', NULL, 'choice'),
(11, 6, 'В каком регионе располагается Эстония?', NULL, 'choice'),
(12, 6, 'С кем граничит Эстония?', NULL, 'choice'),
(13, 7, 'Самое крупное озеро в Эстонии?', NULL, 'choice'),
(14, 7, 'Какой из них Чудское озеро?', NULL, 'image'),
(15, 8, 'Крупнейшие острова Эстонии?', NULL, 'choice'),
(16, 8, 'Какая страна находится там через северный пролив от Эстонии?', NULL, 'input'),
(17, 9, 'Основная местность Эстонии?', NULL, 'choice'),
(18, 9, 'Сколько озера занимают территории Эстонии?', NULL, 'choice'),
(19, 10, 'Сколько территории Эстонии занимают леса?', NULL, 'choice'),
(20, 10, 'На каком климатическом поясе располагается Эстония?', NULL, 'choice');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('user','admin') DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `role`) VALUES
(1, 'admin@emal.ee', '123456', 'admin'),
(2, 'tester@test.ee', 'test', 'user'),
(3, 'user@emal.ee', '654321', 'user'),
(4, 'admin2@ee.ee', '$2y$10$xweheNpOvXEuPwPW1bMye.XFlJM4.U8CEdFe82Howl9OSmQmRTuta', 'user'),
(5, 'user123@ee.ee', '$2y$10$goVLPlHvv3fegARSrqp0BOCsPLWNz2VCbDHwm3Ms087/sAqWfnysy', 'user');

-- --------------------------------------------------------

--
-- Структура таблицы `user_answers`
--

CREATE TABLE `user_answers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `question_id` int(11) DEFAULT NULL,
  `answer_id` int(11) DEFAULT NULL,
  `is_correct` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `answers`
--
ALTER TABLE `answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `question_id` (`question_id`);

--
-- Индексы таблицы `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `levels`
--
ALTER TABLE `levels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Индексы таблицы `progress`
--
ALTER TABLE `progress`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `level_id` (`level_id`);

--
-- Индексы таблицы `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `level_id` (`level_id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Индексы таблицы `user_answers`
--
ALTER TABLE `user_answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `question_id` (`question_id`),
  ADD KEY `answer_id` (`answer_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `answers`
--
ALTER TABLE `answers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT для таблицы `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `levels`
--
ALTER TABLE `levels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT для таблицы `progress`
--
ALTER TABLE `progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `questions`
--
ALTER TABLE `questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `user_answers`
--
ALTER TABLE `user_answers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `answers`
--
ALTER TABLE `answers`
  ADD CONSTRAINT `answers_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `levels`
--
ALTER TABLE `levels`
  ADD CONSTRAINT `levels_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Ограничения внешнего ключа таблицы `progress`
--
ALTER TABLE `progress`
  ADD CONSTRAINT `progress_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `progress_ibfk_2` FOREIGN KEY (`level_id`) REFERENCES `levels` (`id`);

--
-- Ограничения внешнего ключа таблицы `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`level_id`) REFERENCES `levels` (`id`);

--
-- Ограничения внешнего ключа таблицы `user_answers`
--
ALTER TABLE `user_answers`
  ADD CONSTRAINT `user_answers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `user_answers_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`),
  ADD CONSTRAINT `user_answers_ibfk_3` FOREIGN KEY (`answer_id`) REFERENCES `answers` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
