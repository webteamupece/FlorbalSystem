-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Hostiteľ: db
-- Čas generovania: So 05.Apr 2025, 22:02
-- Verzia serveru: 8.4.4
-- Verzia PHP: 8.2.27

SET
SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET
time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Databáza: `myapp`
--
CREATE
DATABASE IF NOT EXISTS `myapp` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE
`myapp`;

-- --------------------------------------------------------

--
-- Štruktúra tabuľky pre tabuľku `city`
--

CREATE TABLE `city`
(
    `id`   int NOT NULL,
    `name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Sťahujem dáta pre tabuľku `city`
--

INSERT INTO `city` (`id`, `name`)
VALUES (1, 'Poprad'),
       (2, 'Bratislava'),
       (3, 'Prešov'),
       (4, 'Banská Bystrica'),
       (5, 'Zvolen'),
       (6, 'Ružomberok'),
       (7, 'Košice'),
       (8, 'Trnava'),
       (9, 'Trenčín'),
       (10, 'Žilina'),
       (11, 'Martin'),
       (12, 'Nitra');

-- --------------------------------------------------------

--
-- Štruktúra tabuľky pre tabuľku `duel`
--

CREATE TABLE `duel`
(
    `id`            int       NOT NULL,
    `starting_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `state`         enum('SCHEDULED','ONGOING','FINISHED') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
    `stage_id`      int       NOT NULL,
    `tournament_id` int                DEFAULT NULL,
    `roster1_id`    int                DEFAULT NULL,
    `roster2_id`    int                DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Sťahujem dáta pre tabuľku `duel`
--

INSERT INTO `duel` (`id`, `starting_time`, `state`, `stage_id`, `tournament_id`, `roster1_id`, `roster2_id`)
VALUES (1, '2025-04-05 13:00:00', 'FINISHED', 5, 1, 1, 3),
       (2, '2025-04-05 21:37:37', 'ONGOING', 5, 1, 4, 5),
       (3, '2025-04-07 14:00:00', 'SCHEDULED', 6, 1, 3, 4),
       (4, '2025-04-05 21:37:37', 'ONGOING', 5, 2, 4, 5),
       (5, '2025-04-07 14:00:00', 'SCHEDULED', 6, 2, 3, 4);
-- --------------------------------------------------------
-- --------------------------------------------------------

--
-- Štruktúra tabuľky pre tabuľku `goal`
--

CREATE TABLE `goal`
(
    `player_id`      int     NOT NULL,
    `duel_id`        int     NOT NULL,
    `own_goal_count` tinyint NOT NULL DEFAULT '0',
    `goal_count`     tinyint NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Sťahujem dáta pre tabuľku `goal`
--

INSERT INTO `goal` (`player_id`, `duel_id`, `own_goal_count`, `goal_count`)
VALUES (1, 1, 1, 1),
       (2, 1, 0, 2),
       (4, 1, 0, 3),
       (5, 2, 0, 2);

-- --------------------------------------------------------

--
-- Štruktúra tabuľky pre tabuľku `organization`
--

CREATE TABLE `organization`
(
    `id`         int          NOT NULL,
    `short_name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
    `full_name`  varchar(255) NOT NULL,
    `city_id`    int                                                          DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Sťahujem dáta pre tabuľku `organization`
--

INSERT INTO `organization` (`id`, `short_name`, `full_name`, `city_id`)
VALUES (1, 'UPeCe BA', 'Univerzitné pastoračné centrum svätého Jozefa Freinademetza (UPeCe BA)', 2),
       (2, 'Svoradov BA', 'Študentský domov Svoradov', 2),
       (3, 'UPeCe Prešov', 'Univerzitné pastoračné centrum Dr. Š. Héseka', 3),
       (4, 'UPC BB', 'Univerzitné pastoračné centrum\r\nŠtefana Moysesa, biskupa', 4),
       (5, 'CUP Zvolen', 'Centrum univerzitnej pastorácie \r\nEmauzských učeníkov', 5),
       (6, 'UPaC Ružomberok', 'Univerzitné pastoračné centrum\r\nJána Vojtaššáka', 6),
       (7, 'UPC KE', 'Univerzitné pastoračné centrum\r\nsv. Košických mučeníkov', 7),
       (8, 'UPeCe Trnava', 'Univerzitné pastoračné centrum\r\nsvätého Stanislava Kostku v Trnave', 8),
       (9, 'UPC Trenčín', 'Univerzitné pastoračné centrum\r\nsv. Andreja Svorada a Benedikta', 9),
       (10, 'UPeCe Žilina', 'Univerzitné pastoračné centrum\r\npri Žilinskej univerzite', 10),
       (11, 'CUP Martin', 'Centrum univerzitnej pastorácie v Martine', 11),
       (12, 'UPC Nitra', 'Univerzitné pastoračné centrum\r\nPavla Straussa', 12);

-- --------------------------------------------------------

--
-- Štruktúra tabuľky pre tabuľku `player`
--

CREATE TABLE `player`
(
    `id`            int NOT NULL,
    `jersey_number` int                                                           DEFAULT NULL,
    `first_name`    varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
    `last_name`     varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Sťahujem dáta pre tabuľku `player`
--

INSERT INTO `player` (`id`, `jersey_number`, `first_name`, `last_name`)
VALUES (1, 7, 'Marek', 'Kormoš'),
       (2, 14, 'Benjamín', 'Čornec'),
       (3, 7, 'Tomáš', 'Slavkov'),
       (4, 13, 'Michal', 'Slavkov'),
       (5, 99, 'Test', 'Testovací'),
       (6, 98, 'Test2', 'Testovací2');

-- --------------------------------------------------------

--
-- Štruktúra tabuľky pre tabuľku `player_roster`
--

CREATE TABLE `player_roster`
(
    `player_id` int DEFAULT NULL,
    `roster_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Sťahujem dáta pre tabuľku `player_roster`
--

INSERT INTO `player_roster` (`player_id`, `roster_id`)
VALUES (2, 1),
       (1, 1),
       (1, 2),
       (2, 2),
       (3, 2),
       (3, 3),
       (4, 3),
       (5, 4);

-- --------------------------------------------------------

--
-- Štruktúra tabuľky pre tabuľku `roster`
--

CREATE TABLE `roster`
(
    `id`              int NOT NULL,
    `name`            varchar(100) DEFAULT NULL,
    `tournament_id`   int          DEFAULT NULL,
    `organization_id` int          DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Sťahujem dáta pre tabuľku `roster`
--

INSERT INTO `roster` (`id`, `name`, `tournament_id`, `organization_id`)
VALUES (1, 'Beskyho anjelici', 1, 1),
       (2, 'Bratislavskí zabijaci', 2, 1),
       (3, 'Žilinskí zradcovia', 1, 10),
       (4, 'testovací', 1, 2),
       (5, 'Testovací2', 1, 6);

-- --------------------------------------------------------

--
-- Štruktúra tabuľky pre tabuľku `stage`
--

CREATE TABLE `stage`
(
    `id`          int         NOT NULL,
    `code`        enum('group','quarterfinals','semifinals','finals','third_place') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
    `name`        varchar(64) NOT NULL,
    `order_index` int         NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Sťahujem dáta pre tabuľku `stage`
--

INSERT INTO `stage` (`id`, `code`, `name`, `order_index`)
VALUES (1, 'group', 'Skupina A', 5),
       (2, 'group', 'Skupina B', 6),
       (3, 'group', 'Skupina C', 7),
       (4, 'quarterfinals', 'Štvrťfinále', 4),
       (5, 'semifinals', 'Semifinále', 3),
       (6, 'finals', 'Finále', 1),
       (7, 'third_place', 'Zápas o tretie miesto', 2);

-- --------------------------------------------------------

--
-- Štruktúra tabuľky pre tabuľku `tournament`
--

CREATE TABLE `tournament`
(
    `id`           int NOT NULL,
    `name`         varchar(255) DEFAULT NULL,
    `year`         int          DEFAULT NULL,
    `host_city_id` int          DEFAULT NULL,
    `date`         date         DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Sťahujem dáta pre tabuľku `tournament`
--

INSERT INTO `tournament` (`id`, `name`, `year`, `host_city_id`, `date`)
VALUES (1, 'Medzi UPeCe Florbalový turnaj 2024 (MUF CUP)', 2024, 2, '2024-04-13'),
       (2, 'Medzi UPeCe Florbalový turnaj 2025 (MUF CUP)', 2025, 2, '2025-04-26');

--
-- Kľúče pre exportované tabuľky
--

--
-- Indexy pre tabuľku `city`
--
ALTER TABLE `city`
    ADD PRIMARY KEY (`id`);

--
-- Indexy pre tabuľku `duel`
--
ALTER TABLE `duel`
    ADD PRIMARY KEY (`id`),
  ADD KEY `duel_ibfk_1` (`tournament_id`),
  ADD KEY `duel_ibfk_2` (`roster1_id`),
  ADD KEY `duel_ibfk_3` (`roster2_id`),
  ADD KEY `duel_ibfk_4` (`stage_id`);

--
-- Indexy pre tabuľku `goal`
--
ALTER TABLE `goal`
    ADD PRIMARY KEY (`duel_id`, `player_id`),
  ADD KEY `goal_ibfk_1` (`player_id`);

--
-- Indexy pre tabuľku `organization`
--
ALTER TABLE `organization`
    ADD PRIMARY KEY (`id`),
  ADD KEY `city_id` (`city_id`);

--
-- Indexy pre tabuľku `player`
--
ALTER TABLE `player`
    ADD PRIMARY KEY (`id`);

--
-- Indexy pre tabuľku `player_roster`
--
ALTER TABLE `player_roster`
    ADD KEY `player_roster_ibfk_1` (`player_id`),
  ADD KEY `player_roster_ibfk_2` (`roster_id`);

--
-- Indexy pre tabuľku `roster`
--
ALTER TABLE `roster`
    ADD PRIMARY KEY (`id`),
  ADD KEY `roster_ibfk_1` (`tournament_id`),
  ADD KEY `roster_ibfk_2` (`organization_id`);

--
-- Indexy pre tabuľku `stage`
--
ALTER TABLE `stage`
    ADD PRIMARY KEY (`id`);

--
-- Indexy pre tabuľku `tournament`
--
ALTER TABLE `tournament`
    ADD PRIMARY KEY (`id`),
  ADD KEY `tournament_ibfk_1` (`host_city_id`);

--
-- AUTO_INCREMENT pre exportované tabuľky
--

--
-- AUTO_INCREMENT pre tabuľku `city`
--
ALTER TABLE `city`
    MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pre tabuľku `duel`
--
ALTER TABLE `duel`
    MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pre tabuľku `organization`
--
ALTER TABLE `organization`
    MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pre tabuľku `player`
--
ALTER TABLE `player`
    MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pre tabuľku `roster`
--
ALTER TABLE `roster`
    MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pre tabuľku `stage`
--
ALTER TABLE `stage`
    MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pre tabuľku `tournament`
--
ALTER TABLE `tournament`
    MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Obmedzenie pre exportované tabuľky
--

--
-- Obmedzenie pre tabuľku `duel`
--
ALTER TABLE `duel`
    ADD CONSTRAINT `duel_ibfk_1` FOREIGN KEY (`tournament_id`) REFERENCES `tournament` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `duel_ibfk_2` FOREIGN KEY (`roster1_id`) REFERENCES `roster` (`id`) ON
DELETE
RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `duel_ibfk_3` FOREIGN KEY (`roster2_id`) REFERENCES `roster` (`id`) ON DELETE
RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `duel_ibfk_4` FOREIGN KEY (`stage_id`) REFERENCES `stage` (`id`) ON DELETE
RESTRICT ON UPDATE CASCADE;

--
-- Obmedzenie pre tabuľku `goal`
--
ALTER TABLE `goal`
    ADD CONSTRAINT `goal_ibfk_1` FOREIGN KEY (`player_id`) REFERENCES `player` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `goal_ibfk_2` FOREIGN KEY (`duel_id`) REFERENCES `duel` (`id`) ON
DELETE
RESTRICT ON UPDATE CASCADE;

--
-- Obmedzenie pre tabuľku `organization`
--
ALTER TABLE `organization`
    ADD CONSTRAINT `organization_ibfk_1` FOREIGN KEY (`city_id`) REFERENCES `city` (`id`);

--
-- Obmedzenie pre tabuľku `player_roster`
--
ALTER TABLE `player_roster`
    ADD CONSTRAINT `player_roster_ibfk_1` FOREIGN KEY (`player_id`) REFERENCES `player` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `player_roster_ibfk_2` FOREIGN KEY (`roster_id`) REFERENCES `roster` (`id`) ON
DELETE
RESTRICT ON UPDATE CASCADE;

--
-- Obmedzenie pre tabuľku `roster`
--
ALTER TABLE `roster`
    ADD CONSTRAINT `roster_ibfk_1` FOREIGN KEY (`tournament_id`) REFERENCES `tournament` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `roster_ibfk_2` FOREIGN KEY (`organization_id`) REFERENCES `organization` (`id`) ON
DELETE
RESTRICT ON UPDATE CASCADE;

--
-- Obmedzenie pre tabuľku `tournament`
--
ALTER TABLE `tournament`
    ADD CONSTRAINT `tournament_ibfk_1` FOREIGN KEY (`host_city_id`) REFERENCES `city` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

CREATE TABLE `user`
(
    `id`         INT          NOT NULL AUTO_INCREMENT,
    `username`   VARCHAR(100) NOT NULL UNIQUE,
    `password`   VARCHAR(255) NOT NULL,
    `role`       ENUM('admin', 'volunteer') NOT NULL DEFAULT 'volunteer',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
);

/*TODO hashovanie*/

INSERT INTO `user` (`username`, `password`, `role`)
VALUES ('admin', 'admin123', 'admin');

INSERT INTO `user` (`username`, `password`, `role`)
VALUES ('vol1', '111', 'volunteer');

INSERT INTO `user` (`username`, `password`, `role`)
VALUES ('vol2', '222', 'volunteer');