-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : mer. 14 jan. 2026 à 08:40
-- Version du serveur : 10.11.13-MariaDB-0ubuntu0.24.04.1
-- Version de PHP : 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `cnc_files`
--

-- --------------------------------------------------------

--
-- Structure de la table `auth_logs`
--

CREATE TABLE `auth_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `username_try` varchar(50) DEFAULT NULL,
  `success` tinyint(1) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `occurred_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `auth_logs`
--

INSERT INTO `auth_logs` (`id`, `user_id`, `username_try`, `success`, `ip_address`, `user_agent`, `occurred_at`) VALUES
(1, NULL, 'admin', 0, '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0', '2026-01-13 16:47:15'),
(2, 1, 'admin', 1, '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0', '2026-01-13 16:53:28'),
(3, 1, 'admin', 1, '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0', '2026-01-14 08:15:25'),
(4, 1, 'admin', 1, '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0', '2026-01-14 08:16:35'),
(5, 1, 'admin', 1, '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0', '2026-01-14 08:22:30'),
(6, 1, 'admin', 1, '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:146.0) Gecko/20100101 Firefox/146.0', '2026-01-14 08:25:45');

-- --------------------------------------------------------

--
-- Structure de la table `cnc_machines`
--

CREATE TABLE `cnc_machines` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `depot_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `model` varchar(100) DEFAULT NULL,
  `serial_no` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `depots`
--

CREATE TABLE `depots` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `address` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `files`
--

CREATE TABLE `files` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `owner_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `original_name` varchar(255) NOT NULL,
  `stored_name` varchar(255) NOT NULL,
  `storage_path` text NOT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `file_ext` varchar(20) DEFAULT NULL,
  `size_bytes` bigint(20) UNSIGNED NOT NULL,
  `sha256` char(64) DEFAULT NULL,
  `tags` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `files`
--

INSERT INTO `files` (`id`, `owner_user_id`, `original_name`, `stored_name`, `storage_path`, `mime_type`, `file_ext`, `size_bytes`, `sha256`, `tags`, `created_at`, `updated_at`) VALUES
(1, 1, 'journal de bord.txt', 'job_69666e9aa94690.01664778.txt', 'uploads/job_69666e9aa94690.01664778.txt', NULL, 'txt', 412, 'bd6e42f1434380091d5a8315905b40ab2b004c2a5d9a7b7165b2a37831358009', NULL, '2026-01-13 17:11:06', '2026-01-13 17:11:06'),
(3, 1, 'BDD fonction.txt', 'job_6966707f77ac60.49651210.txt', 'uploads/job_6966707f77ac60.49651210.txt', NULL, 'txt', 334, '9845487794e7df78751da644db11508ded1a2a577cc6806e3a11a27cf3215085', NULL, '2026-01-13 17:19:11', '2026-01-13 17:19:11'),
(4, 1, 'TP_Lignes de transmissions_25_sujet.pdf', 'job_696742a1e3fa44.60967745.pdf', 'uploads/job_696742a1e3fa44.60967745.pdf', NULL, 'pdf', 229812, '43648bfbc0459628f087586134266ef36d7b627dfdd4fe49768383cd358e47f1', NULL, '2026-01-14 08:15:45', '2026-01-14 08:15:45');

-- --------------------------------------------------------

--
-- Structure de la table `file_transfers`
--

CREATE TABLE `file_transfers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `file_id` bigint(20) UNSIGNED NOT NULL,
  `requested_by` bigint(20) UNSIGNED DEFAULT NULL,
  `source` varchar(30) NOT NULL,
  `destination` varchar(30) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'queued',
  `started_at` datetime DEFAULT NULL,
  `finished_at` datetime DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `file_transfers`
--

INSERT INTO `file_transfers` (`id`, `file_id`, `requested_by`, `source`, `destination`, `status`, `started_at`, `finished_at`, `error_message`, `created_at`) VALUES
(1, 1, 1, 'designer', 'db', 'stored', '2026-01-13 17:11:06', '2026-01-13 17:11:06', NULL, '2026-01-13 17:11:06'),
(3, 3, 1, 'designer', 'db', 'stored', '2026-01-13 17:19:11', '2026-01-13 17:19:11', NULL, '2026-01-13 17:19:11'),
(4, 4, 1, 'designer', 'db', 'stored', '2026-01-14 08:15:45', '2026-01-14 08:15:45', NULL, '2026-01-14 08:15:45');

-- --------------------------------------------------------

--
-- Structure de la table `print_history`
--

CREATE TABLE `print_history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `file_id` bigint(20) UNSIGNED NOT NULL,
  `printed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `depot_id` bigint(20) UNSIGNED DEFAULT NULL,
  `cnc_machine_id` bigint(20) UNSIGNED DEFAULT NULL,
  `printed_at` datetime NOT NULL DEFAULT current_timestamp(),
  `job_name` varchar(255) DEFAULT NULL,
  `pages_or_parts` int(11) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'done',
  `notes` text DEFAULT NULL
) ;

--
-- Déchargement des données de la table `print_history`
--

INSERT INTO `print_history` (`id`, `file_id`, `printed_by`, `depot_id`, `cnc_machine_id`, `printed_at`, `job_name`, `pages_or_parts`, `status`, `notes`) VALUES
(1, 3, 1, NULL, NULL, '2026-01-13 17:20:38', NULL, NULL, 'done', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` varchar(30) NOT NULL DEFAULT 'user',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `last_login_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `role`, `is_active`, `created_at`, `last_login_at`) VALUES
(1, 'admin', NULL, '$2y$10$FADOKGiRk47PcP1RQinwnO4uY/5xpeKfr.WWBrOesN/vsFiLOyUpe', 'admin', 1, '2026-01-13 16:53:12', '2026-01-14 08:25:45');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `auth_logs`
--
ALTER TABLE `auth_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_authlogs_user_time` (`user_id`,`occurred_at`),
  ADD KEY `idx_authlogs_success_time` (`success`,`occurred_at`);

--
-- Index pour la table `cnc_machines`
--
ALTER TABLE `cnc_machines`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_machine_depot_name` (`depot_id`,`name`),
  ADD KEY `idx_machine_depot` (`depot_id`);

--
-- Index pour la table `depots`
--
ALTER TABLE `depots`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_depots_name` (`name`);

--
-- Index pour la table `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_files_stored_name` (`stored_name`),
  ADD KEY `idx_files_owner_created` (`owner_user_id`,`created_at`),
  ADD KEY `idx_files_ext` (`file_ext`);

--
-- Index pour la table `file_transfers`
--
ALTER TABLE `file_transfers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_transfers_file_created` (`file_id`,`created_at`),
  ADD KEY `idx_transfers_requested_by` (`requested_by`);

--
-- Index pour la table `print_history`
--
ALTER TABLE `print_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_print_depot_time` (`depot_id`,`printed_at`),
  ADD KEY `idx_print_file_time` (`file_id`,`printed_at`),
  ADD KEY `idx_print_machine_time` (`cnc_machine_id`,`printed_at`),
  ADD KEY `fk_print_user` (`printed_by`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_username` (`username`),
  ADD UNIQUE KEY `uq_users_email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `auth_logs`
--
ALTER TABLE `auth_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `cnc_machines`
--
ALTER TABLE `cnc_machines`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `depots`
--
ALTER TABLE `depots`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `files`
--
ALTER TABLE `files`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `file_transfers`
--
ALTER TABLE `file_transfers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `print_history`
--
ALTER TABLE `print_history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `auth_logs`
--
ALTER TABLE `auth_logs`
  ADD CONSTRAINT `fk_authlogs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `cnc_machines`
--
ALTER TABLE `cnc_machines`
  ADD CONSTRAINT `fk_machine_depot` FOREIGN KEY (`depot_id`) REFERENCES `depots` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `files`
--
ALTER TABLE `files`
  ADD CONSTRAINT `fk_files_owner` FOREIGN KEY (`owner_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `file_transfers`
--
ALTER TABLE `file_transfers`
  ADD CONSTRAINT `fk_transfers_file` FOREIGN KEY (`file_id`) REFERENCES `files` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_transfers_user` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `print_history`
--
ALTER TABLE `print_history`
  ADD CONSTRAINT `fk_print_depot` FOREIGN KEY (`depot_id`) REFERENCES `depots` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_print_file` FOREIGN KEY (`file_id`) REFERENCES `files` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_print_machine` FOREIGN KEY (`cnc_machine_id`) REFERENCES `cnc_machines` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_print_user` FOREIGN KEY (`printed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
