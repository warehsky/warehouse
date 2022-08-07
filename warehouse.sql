-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Авг 07 2022 г., 19:31
-- Версия сервера: 10.3.22-MariaDB
-- Версия PHP: 7.4.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `warehouse`
--
CREATE DATABASE IF NOT EXISTS `warehouse` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `warehouse`;

-- --------------------------------------------------------

--
-- Структура таблицы `admins`
--

CREATE TABLE `admins` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `chatName` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ' ',
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `api_token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `login` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `warehousep` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ' ' COMMENT 'пароль для api склада',
  `role` int(11) NOT NULL DEFAULT 0,
  `note` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `areaId` int(11) NOT NULL DEFAULT 1,
  `mobUserId` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `admins`
--

INSERT INTO `admins` (`id`, `name`, `chatName`, `email`, `email_verified_at`, `password`, `remember_token`, `api_token`, `created_at`, `updated_at`, `deleted_at`, `login`, `warehousep`, `role`, `note`, `areaId`, `mobUserId`) VALUES
(1, 'admin', 'admin', 'admin@mail.com', NULL, '$2y$10$QVx4CiguKece/kiPchP9mOcjWrgA3Pd.i53Kn8eeJaiVdJXCnwsMS', NULL, 'dFZ9qf0u0k9z5CzJfMQmG93CHQ27EAQnAO0ovBYKszt1k7pWBD3TOklv6NfP', '2019-08-12 04:45:06', '2022-08-05 16:41:48', NULL, 'admin', ' ', 10, '4', 0, 1309);

-- --------------------------------------------------------

--
-- Структура таблицы `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` int(10) UNSIGNED NOT NULL,
  `model_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` int(10) UNSIGNED NOT NULL,
  `model_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Model\\Admin', 1),
(1, 'App\\Model\\Admin', 3),
(1, 'App\\Model\\Admin', 36),
(1, 'App\\Model\\Admin', 48),
(1, 'App\\Model\\Admin', 49),
(1, 'App\\Model\\Admin', 50),
(1, 'App\\Model\\Admin', 53),
(4, 'App\\Model\\Admin', 31),
(4, 'App\\Model\\Admin', 32),
(4, 'App\\Model\\Admin', 33),
(4, 'App\\Model\\Admin', 35),
(4, 'App\\Model\\Admin', 37),
(4, 'App\\Model\\Admin', 55),
(4, 'App\\Model\\Admin', 56),
(4, 'App\\Model\\Admin', 58),
(4, 'App\\Model\\Admin', 59),
(4, 'App\\Model\\Admin', 62),
(4, 'App\\Model\\Admin', 63),
(4, 'App\\Model\\Admin', 64),
(4, 'App\\Model\\Admin', 65),
(4, 'App\\Model\\Admin', 66),
(4, 'App\\Model\\Admin', 70);

-- --------------------------------------------------------

--
-- Структура таблицы `options`
--

CREATE TABLE `options` (
  `id` int(11) NOT NULL,
  `field` varchar(20) NOT NULL,
  `value` longtext NOT NULL,
  `type` varchar(10) NOT NULL,
  `description` varchar(255) NOT NULL,
  `groupId` varchar(30) DEFAULT NULL COMMENT 'основная группа',
  `subgroup` varchar(30) DEFAULT NULL COMMENT 'подгруппа',
  `visible` int(11) NOT NULL DEFAULT 1 COMMENT '1 - Отображается в настройках; 0 - не отображается'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `options`
--

INSERT INTO `options` (`id`, `field`, `value`, `type`, `description`, `groupId`, `subgroup`, `visible`) VALUES
(1, 'orders_paginate', '25', 'integer', 'пагинация для страницы заказы', 'Отображение', 'Заказы', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `orderitem`
--

CREATE TABLE `orderitem` (
  `id` int(11) NOT NULL,
  `orderId` int(11) NOT NULL,
  `itemId` int(11) NOT NULL,
  `warehouse_id` int(11) NOT NULL DEFAULT 0,
  `quantity_base` double NOT NULL DEFAULT 0 COMMENT 'кол-во в первоначальном заказе',
  `quantity` double NOT NULL,
  `quantity_warehouse` double NOT NULL DEFAULT 0 COMMENT 'Кол-во указанное складом',
  `price` double NOT NULL,
  `priceType` int(11) NOT NULL DEFAULT 2,
  `percent` int(11) NOT NULL DEFAULT 0,
  `workerId` int(11) DEFAULT NULL COMMENT 'ID рабочего склада',
  `manually` tinyint(4) NOT NULL DEFAULT 0,
  `pickTm` timestamp NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'признак товар собран складом'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `orderlocks`
--

CREATE TABLE `orderlocks` (
  `orderId` bigint(11) NOT NULL,
  `userId` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `number` varchar(32) NOT NULL DEFAULT ' ',
  `course` double NOT NULL DEFAULT 0 COMMENT 'текущий курс гривны',
  `name` varchar(100) NOT NULL COMMENT 'имя закзчика',
  `webUserId` int(11) NOT NULL DEFAULT 0,
  `phone` varchar(20) NOT NULL,
  `phoneConsignee` varchar(20) DEFAULT NULL,
  `addr` varchar(255) NOT NULL,
  `entrance` tinyint(4) NOT NULL DEFAULT -1 COMMENT 'подъезд',
  `floor` tinyint(4) NOT NULL DEFAULT -1 COMMENT 'этаж',
  `flat` int(11) NOT NULL DEFAULT -1 COMMENT 'квартира',
  `deliveryCost` float NOT NULL DEFAULT 0,
  `status` int(11) NOT NULL DEFAULT 0,
  `pickupStatus` tinyint(4) NOT NULL DEFAULT 1 COMMENT 'состояние сборки на складе',
  `deliveryZone` int(11) NOT NULL DEFAULT 0,
  `deliveryZoneIn` int(11) NOT NULL DEFAULT 0,
  `userId` int(11) NOT NULL DEFAULT 0,
  `workerId` int(11) NOT NULL COMMENT 'ID рабочего склада',
  `note` text DEFAULT NULL,
  `sum_total` double NOT NULL,
  `sum_last` double NOT NULL DEFAULT 0,
  `sms` tinyint(4) NOT NULL DEFAULT 0,
  `lat` double NOT NULL DEFAULT 0,
  `lng` double NOT NULL DEFAULT 0,
  `guid` varchar(36) NOT NULL,
  `deliveryFrom` timestamp NULL DEFAULT NULL,
  `deliveryTo` timestamp NULL DEFAULT NULL,
  `deliveryDate` date DEFAULT current_timestamp(),
  `waveId` int(11) NOT NULL DEFAULT 1 COMMENT 'ID волны доставки',
  `gift` varchar(50) NOT NULL DEFAULT '0',
  `deviceType` int(11) DEFAULT NULL,
  `deviceInfo` varchar(255) DEFAULT NULL,
  `pension` tinyint(4) NOT NULL DEFAULT 0,
  `payment` int(11) NOT NULL DEFAULT 1,
  `order_number` varchar(36) NOT NULL DEFAULT '0',
  `bonus` int(11) NOT NULL DEFAULT 0 COMMENT 'число бонусов от операции',
  `discountId` int(11) NOT NULL DEFAULT 0 COMMENT 'промо код или дисконтная карта',
  `bonus_pay` int(11) NOT NULL DEFAULT 0 COMMENT 'бонусы расходуемые на оплату заказа',
  `discount_sum` float NOT NULL DEFAULT 0 COMMENT 'сумма скидки',
  `discount_proc` int(11) NOT NULL DEFAULT 0,
  `action` varchar(36) DEFAULT NULL COMMENT 'учавствует в акции',
  `orderNumber` varchar(50) DEFAULT NULL COMMENT 'банковский чек заказа',
  `sum_pay` double NOT NULL DEFAULT 0 COMMENT 'полученная сумма оплаты',
  `url_pay` varchar(255) NOT NULL DEFAULT '',
  `visiteTime` timestamp NULL DEFAULT NULL COMMENT 'ожидаемое время визита курьера',
  `remindSms` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'счетчик отосланных смс (напоминание о визите курьера)',
  `nopacks` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'признак заказ без пакетов',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `locked` int(11) NOT NULL DEFAULT 0,
  `codeView` varchar(10) NOT NULL DEFAULT ' '
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `permissions`
--

CREATE TABLE `permissions` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'orders_all', 'web', '2021-08-11 03:21:49', '2021-08-11 03:21:49'),
(3, 'options_all', 'web', '2021-08-11 03:22:03', '2021-08-11 03:22:03'),
(55, 'userAdmin_all', 'web', '2021-08-11 03:22:37', '2021-08-11 03:22:37');

-- --------------------------------------------------------

--
-- Структура таблицы `roles`
--

CREATE TABLE `roles` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'web', '2021-08-11 03:58:34', '2021-08-11 03:58:34'),
(4, 'operator', 'web', '2021-08-13 05:21:21', '2021-08-13 05:21:21');

-- --------------------------------------------------------

--
-- Структура таблицы `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` int(10) UNSIGNED NOT NULL,
  `role_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(1, 4),
(3, 1),
(55, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `usersmobile`
--

CREATE TABLE `usersmobile` (
  `phone` varchar(20) NOT NULL,
  `deviceId` varchar(64) NOT NULL,
  `token` varchar(50) NOT NULL,
  `code` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD KEY `login` (`login`);
ALTER TABLE `admins` ADD FULLTEXT KEY `users_email_unique` (`email`);

--
-- Индексы таблицы `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_type_model_id_index` (`model_type`,`model_id`);

--
-- Индексы таблицы `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_type_model_id_index` (`model_type`,`model_id`);

--
-- Индексы таблицы `options`
--
ALTER TABLE `options`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `field` (`field`);

--
-- Индексы таблицы `orderitem`
--
ALTER TABLE `orderitem`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mult` (`orderId`,`itemId`),
  ADD KEY `order_id` (`orderId`),
  ADD KEY `item_id` (`itemId`);

--
-- Индексы таблицы `orderlocks`
--
ALTER TABLE `orderlocks`
  ADD PRIMARY KEY (`orderId`),
  ADD KEY `userId` (`userId`);

--
-- Индексы таблицы `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `status` (`status`),
  ADD KEY `webUserId` (`webUserId`),
  ADD KEY `updated_at` (`updated_at`),
  ADD KEY `created_at` (`created_at`),
  ADD KEY `deliveryFrom` (`deliveryFrom`),
  ADD KEY `deliveryDate` (`deliveryDate`),
  ADD KEY `deliveryTo` (`deliveryTo`),
  ADD KEY `deliveryZone` (`deliveryZone`),
  ADD KEY `pickupStatus` (`pickupStatus`),
  ADD KEY `phone` (`phone`),
  ADD KEY `number` (`number`),
  ADD KEY `userId` (`userId`),
  ADD KEY `payment` (`payment`);

--
-- Индексы таблицы `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Индексы таблицы `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Индексы таблицы `usersmobile`
--
ALTER TABLE `usersmobile`
  ADD UNIQUE KEY `mult` (`deviceId`,`phone`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `admins`
--
ALTER TABLE `admins`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT для таблицы `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `options`
--
ALTER TABLE `options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `orderitem`
--
ALTER TABLE `orderitem`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1667062;

--
-- AUTO_INCREMENT для таблицы `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70730;

--
-- AUTO_INCREMENT для таблицы `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT для таблицы `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `orderitem`
--
ALTER TABLE `orderitem`
  ADD CONSTRAINT `orderItem_ibfk_1` FOREIGN KEY (`orderId`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `orderlocks`
--
ALTER TABLE `orderlocks`
  ADD CONSTRAINT `orderLocks_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `admins` (`id`) ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
