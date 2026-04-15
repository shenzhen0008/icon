-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2026-04-05 05:39:13
-- 服务器版本： 5.7.44-log
-- PHP 版本： 8.3.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `icon_market`
--

-- --------------------------------------------------------

--
-- 表的结构 `balance_ledgers`
--

CREATE TABLE `balance_ledgers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(16,2) NOT NULL,
  `before_balance` decimal(16,2) NOT NULL,
  `after_balance` decimal(16,2) NOT NULL,
  `biz_ref_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `biz_ref_id` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `occurred_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `balance_ledgers`
--

INSERT INTO `balance_ledgers` (`id`, `user_id`, `type`, `amount`, `before_balance`, `after_balance`, `biz_ref_type`, `biz_ref_id`, `occurred_at`, `created_at`, `updated_at`) VALUES
(1, 6, 'purchase_debit', -1.00, 111110.00, 111109.00, 'position', '1', '2026-04-04 06:01:52', '2026-04-04 06:01:52', '2026-04-04 06:01:52'),
(2, 6, 'purchase_debit', -5000.00, 111109.00, 106109.00, 'position', '2', '2026-04-04 06:08:28', '2026-04-04 06:08:28', '2026-04-04 06:08:28'),
(3, 6, 'purchase_debit', -60000.00, 106109.00, 46109.00, 'position', '3', '2026-04-04 06:18:29', '2026-04-04 06:18:29', '2026-04-04 06:18:29');

-- --------------------------------------------------------

--
-- 表的结构 `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `daily_settlements`
--

CREATE TABLE `daily_settlements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `position_id` bigint(20) UNSIGNED NOT NULL,
  `settlement_date` date NOT NULL,
  `rate` decimal(8,4) NOT NULL,
  `profit` decimal(16,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `exchange_metrics`
--

CREATE TABLE `exchange_metrics` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `exchange_code` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `exchange_name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_btc_volume` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '$0.00',
  `display_btc_liquidity` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `display_eth_volume` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '$0.00',
  `display_eth_liquidity` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `sort` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `exchange_metrics`
--

INSERT INTO `exchange_metrics` (`id`, `exchange_code`, `exchange_name`, `display_btc_volume`, `display_btc_liquidity`, `display_eth_volume`, `display_eth_liquidity`, `sort`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'binance', 'Binance', '$3,638,119,640.15', '962', '$1,841,624,841.81', '983', 10, 1, '2026-04-04 08:18:10', '2026-04-04 18:44:14'),
(2, 'huobi', 'Huobi', '$2,951,022,330.24', '960', '$1,528,801,122.43', '997', 20, 1, '2026-04-04 08:18:10', '2026-04-04 18:44:14'),
(3, 'gate', 'Gate', '$2,105,520,066.90', '937', '$1,314,476,998.22', '961', 30, 1, '2026-04-04 08:18:10', '2026-04-04 18:44:14'),
(4, 'okx', 'OKX', '$3,170,045,155.12', '958', '$1,714,422,107.66', '998', 40, 1, '2026-04-04 08:18:10', '2026-04-04 18:44:14'),
(5, 'kucoin', 'KuCoin', '$1,569,000,444.40', '884', '$947,004,412.18', '939', 50, 1, '2026-04-04 08:18:10', '2026-04-04 18:44:14'),
(6, 'kraken', 'Kraken', '$1,890,012,333.77', '917', '$1,020,004,555.18', '966', 60, 1, '2026-04-04 08:18:10', '2026-04-04 18:16:07');

-- --------------------------------------------------------

--
-- 表的结构 `home_display_settings`
--

CREATE TABLE `home_display_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `summary_people_count` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `summary_people_step_seconds` int(10) UNSIGNED NOT NULL DEFAULT '3',
  `summary_people_min_delta` decimal(10,2) NOT NULL DEFAULT '0.00',
  `summary_people_max_delta` decimal(10,2) NOT NULL DEFAULT '0.00',
  `summary_people_last_tick_at` timestamp NULL DEFAULT NULL,
  `summary_total_profit` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0.00 USDT',
  `summary_profit_step_seconds` int(10) UNSIGNED NOT NULL DEFAULT '3',
  `summary_profit_min_delta` decimal(12,2) NOT NULL DEFAULT '0.00',
  `summary_profit_max_delta` decimal(12,2) NOT NULL DEFAULT '0.00',
  `summary_profit_last_tick_at` timestamp NULL DEFAULT NULL,
  `shared_exchange_profit_base_value` decimal(12,2) NOT NULL DEFAULT '0.00',
  `shared_exchange_profit_step_seconds` int(10) UNSIGNED NOT NULL DEFAULT '3',
  `shared_exchange_profit_min_delta` decimal(10,2) NOT NULL DEFAULT '0.00',
  `shared_exchange_profit_max_delta` decimal(10,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `home_display_settings`
--

INSERT INTO `home_display_settings` (`id`, `summary_people_count`, `summary_people_step_seconds`, `summary_people_min_delta`, `summary_people_max_delta`, `summary_people_last_tick_at`, `summary_total_profit`, `summary_profit_step_seconds`, `summary_profit_min_delta`, `summary_profit_max_delta`, `summary_profit_last_tick_at`, `shared_exchange_profit_base_value`, `shared_exchange_profit_step_seconds`, `shared_exchange_profit_min_delta`, `shared_exchange_profit_max_delta`, `created_at`, `updated_at`) VALUES
(1, '11462', 3, '0.00', '0.00', NULL, '12806.98', 3, '0.00', '0.00', NULL, '2066.11', 3, '0.00', '0.00', '2026-04-16 12:00:00', '2026-04-16 12:00:00');

-- --------------------------------------------------------

--
-- 表的结构 `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_04_04_120000_add_username_to_users_table', 2),
(5, '2026_04_04_100216_backfill_and_make_username_not_null_on_users_table', 3),
(6, '2026_04_04_130000_backfill_and_make_username_not_null_on_users_table', 4),
(7, '2026_04_04_101237_expand_username_length_to_21_on_users_table', 5),
(8, '2026_04_04_140000_expand_username_length_to_21_on_users_table', 5),
(9, '2026_04_04_151000_create_product_position_settlement_tables', 6),
(10, '2026_04_04_152000_create_balance_ledgers_table', 6),
(11, '2026_04_04_153000_add_balance_to_existing_users_table', 6),
(12, '2026_04_04_154000_add_unit_price_to_products_table', 7),
(13, '2026_04_04_155000_drop_min_balance_required_from_products_table', 8),
(14, '2026_04_05_000000_create_exchange_metrics_table', 9),
(15, '2026_04_05_020000_add_profit_value_to_exchange_metrics_table', 10),
(16, '2026_04_05_030000_add_liquidity_columns_to_exchange_metrics_table', 11),
(17, '2026_04_16_120000_create_home_display_settings_table', 12),
(18, '2026_04_16_121000_convert_exchange_metrics_to_display_fields', 12),
(19, '2026_04_16_122000_add_dynamic_columns_to_home_display_settings_table', 13),
(20, '2026_04_16_123000_drop_display_updated_at_from_exchange_metrics_table', 14),
(21, '2026_04_16_124000_add_shared_profit_columns_and_drop_display_profit_from_exchange_metrics', 15);

-- --------------------------------------------------------

--
-- 表的结构 `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `positions`
--

CREATE TABLE `positions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `principal` decimal(16,2) NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `opened_at` timestamp NOT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `positions`
--

INSERT INTO `positions` (`id`, `user_id`, `product_id`, `principal`, `status`, `opened_at`, `closed_at`, `created_at`, `updated_at`) VALUES
(1, 6, 1, 1.00, 'open', '2026-04-04 06:01:52', NULL, '2026-04-04 06:01:52', '2026-04-04 06:01:52'),
(2, 6, 2, 5000.00, 'open', '2026-04-04 06:08:28', NULL, '2026-04-04 06:08:28', '2026-04-04 06:08:28'),
(3, 6, 4, 60000.00, 'open', '2026-04-04 06:18:29', NULL, '2026-04-04 06:18:29', '2026-04-04 06:18:29');

-- --------------------------------------------------------

--
-- 表的结构 `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit_price` decimal(16,2) NOT NULL DEFAULT '1000.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `products`
--

INSERT INTO `products` (`id`, `name`, `code`, `unit_price`, `is_active`, `sort`, `created_at`, `updated_at`) VALUES
(1, '稳健A', 'PROD_A', 1000.00, 1, 10, '2026-04-04 05:56:57', '2026-04-04 05:56:57'),
(2, '进阶B', 'PROD_B', 5000.00, 1, 20, '2026-04-04 05:56:57', '2026-04-04 05:56:57'),
(3, '增长C', 'PROD_C', 10000.00, 1, 30, '2026-04-04 05:56:57', '2026-04-04 05:56:57'),
(4, '旗舰D', 'PROD_D', 30000.00, 1, 40, '2026-04-04 05:56:57', '2026-04-04 05:56:57');

-- --------------------------------------------------------

--
-- 表的结构 `product_daily_returns`
--

CREATE TABLE `product_daily_returns` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `return_date` date NOT NULL,
  `rate` decimal(8,4) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(21) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `balance` decimal(16,2) NOT NULL DEFAULT '0.00',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `users`
--

INSERT INTO `users` (`id`, `username`, `name`, `email`, `email_verified_at`, `password`, `balance`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'AOFbTCI13VxMm2an4USa6', 'AOFbTCI13VxMm2an4USa6', 'aofbtci13vxmm2an4usa6@local.icon-market', NULL, '$2y$12$GWez0FS4WmbjmKReBunpG.csAlMUxlU1.xup330HbYDSKvFFNjScW', 0.00, 'qnOiKlDfHoRibV9ouz0TIe2OAHdaksE1mdgMT8S7T39Jxb9YoiX22Zn6LrbQ', '2026-04-04 02:15:45', '2026-04-04 02:15:45'),
(2, 'NTbd85gvoJkyG5P6iliBz', 'NTbd85gvoJkyG5P6iliBz', 'ntbd85gvojkyg5p6ilibz@local.icon-market', NULL, '$2y$12$hlCOXtVM49EYpJabaXfpY.EEkczh7cUhxcWtKEw0rn2yRWdoGD9IS', 0.00, 'Hx7Pgfd1SqiPvt77lOI0EPtgJrxp1Lb4X5JHGHVita9WSqcHYDiVVOAANQ6l', '2026-04-04 02:23:39', '2026-04-04 02:23:39'),
(3, '6fEYlWGqxI8mzKCZZ5iZN', '6fEYlWGqxI8mzKCZZ5iZN', '6feylwgqxi8mzkczz5izn@local.icon-market', NULL, '$2y$12$rmwb9LILMy1hnZPHQd1P0uiC.zkPD.R75J4AUhqGkvXVOYbUT67Jy', 0.00, 'nOy24kkbhQA2p0XWZWR71Ois6Qj4hPyLzE51d9M303MYzZF7hvSztFdBlkYV', '2026-04-04 02:41:27', '2026-04-04 02:41:27'),
(4, 'a9DPGuw0sUbMSLkMCi6kR', 'a9DPGuw0sUbMSLkMCi6kR', 'a9dpguw0submslkmci6kr@local.icon-market', NULL, '$2y$12$VG.JW9zzuwKTUxAnIV6nLeNrfL4d0oWwvPWrFPQOV8Vb9mXVSDkKe', 0.00, 'TSdvHUR6oZOjNswOMxzt6RMHlty14KZju094r5Awqvp9JzJxMIYUfDPIETER', '2026-04-04 02:56:26', '2026-04-04 02:56:26'),
(5, 're3jD3vlFzCIIqd5ig2v3', 're3jD3vlFzCIIqd5ig2v3', 're3jd3vlfzciiqd5ig2v3@local.icon-market', NULL, '$2y$12$IfgGOZgu6VZED/VBWfNicuWqnL4y.Qu32faHMCAcyQU8txyDQQbem', 0.00, 'c0we9inGbeadkkkNr0PkTQaxoJDKWmlVDBE4h3xaxy6jgIqWK9hRRZCMBFqB', '2026-04-04 02:59:59', '2026-04-04 02:59:59'),
(6, '9ye3RGCLmni5JOraJ0NS7', '9ye3RGCLmni5JOraJ0NS7', '9ye3rgclmni5joraj0ns7@local.icon-market', NULL, '$2y$12$NIQXhM7r.qdLgujOJhlD3.tGCw0O5z33QTb4YUq0u5MKRpaOpwIXa', 46109.00, 'HUvB8nHSu5tTqKcmeaLis5qr9VK0vHtjr3is2mDPdt0IxNuvbVgSK0eQBKJV', '2026-04-04 06:01:10', '2026-04-04 06:18:29');

--
-- 转储表的索引
--

--
-- 表的索引 `balance_ledgers`
--
ALTER TABLE `balance_ledgers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `balance_ledgers_biz_unique` (`user_id`,`type`,`biz_ref_type`,`biz_ref_id`),
  ADD KEY `balance_ledgers_user_id_occurred_at_index` (`user_id`,`occurred_at`);

--
-- 表的索引 `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- 表的索引 `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- 表的索引 `daily_settlements`
--
ALTER TABLE `daily_settlements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `position_date_settlement_unique` (`position_id`,`settlement_date`),
  ADD KEY `daily_settlements_user_id_settlement_date_index` (`user_id`,`settlement_date`),
  ADD KEY `daily_settlements_product_id_settlement_date_index` (`product_id`,`settlement_date`);

--
-- 表的索引 `exchange_metrics`
--
ALTER TABLE `exchange_metrics`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `exchange_metrics_exchange_code_unique` (`exchange_code`),
  ADD KEY `exchange_metrics_is_active_sort_index` (`is_active`,`sort`);

--
-- 表的索引 `home_display_settings`
--
ALTER TABLE `home_display_settings`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- 表的索引 `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- 表的索引 `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- 表的索引 `positions`
--
ALTER TABLE `positions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `positions_user_id_status_index` (`user_id`,`status`),
  ADD KEY `positions_product_id_status_index` (`product_id`,`status`);

--
-- 表的索引 `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `products_code_unique` (`code`),
  ADD KEY `products_is_active_sort_index` (`is_active`,`sort`);

--
-- 表的索引 `product_daily_returns`
--
ALTER TABLE `product_daily_returns`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_date_unique` (`product_id`,`return_date`),
  ADD KEY `product_daily_returns_return_date_index` (`return_date`);

--
-- 表的索引 `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- 表的索引 `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_username_unique` (`username`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `balance_ledgers`
--
ALTER TABLE `balance_ledgers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 使用表AUTO_INCREMENT `daily_settlements`
--
ALTER TABLE `daily_settlements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `exchange_metrics`
--
ALTER TABLE `exchange_metrics`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- 使用表AUTO_INCREMENT `home_display_settings`
--
ALTER TABLE `home_display_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- 使用表AUTO_INCREMENT `positions`
--
ALTER TABLE `positions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 使用表AUTO_INCREMENT `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- 使用表AUTO_INCREMENT `product_daily_returns`
--
ALTER TABLE `product_daily_returns`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- 限制导出的表
--

--
-- 限制表 `balance_ledgers`
--
ALTER TABLE `balance_ledgers`
  ADD CONSTRAINT `balance_ledgers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- 限制表 `daily_settlements`
--
ALTER TABLE `daily_settlements`
  ADD CONSTRAINT `daily_settlements_position_id_foreign` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `daily_settlements_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `daily_settlements_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- 限制表 `positions`
--
ALTER TABLE `positions`
  ADD CONSTRAINT `positions_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `positions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- 限制表 `product_daily_returns`
--
ALTER TABLE `product_daily_returns`
  ADD CONSTRAINT `product_daily_returns_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
