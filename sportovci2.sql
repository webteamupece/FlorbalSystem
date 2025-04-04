CREATE TABLE `city` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `name` VARCHAR(255)
);

CREATE TABLE `organization` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `name` VARCHAR(255),
  `city_id` INT,
  FOREIGN KEY (`city_id`) REFERENCES `city`(`id`)
);

CREATE TABLE `tournament` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `name` VARCHAR(255),
  `year` INT,
  `city_id` INT,
  `date` DATE,
  FOREIGN KEY (`city_id`) REFERENCES `city`(`id`)
);

CREATE TABLE `roster` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `name` VARCHAR(100),
  `tournament_id` INT,
  `organization_id` INT,
  FOREIGN KEY (`tournament_id`) REFERENCES `tournament`(`id`),
  FOREIGN KEY (`organization_id`) REFERENCES `organization`(`id`)
);

CREATE TABLE `player` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `jersey_number` INT,
  `player_first_name` VARCHAR(100),
  `player_last_name` VARCHAR(100)
);

CREATE TABLE `player_roster` (
  `player_id` INT,
  `roster_id` INT,
  FOREIGN KEY (`player_id`) REFERENCES `player`(`id`),
  FOREIGN KEY (`roster_id`) REFERENCES `roster`(`id`)
);

CREATE TABLE `match` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `tournament_id` INT,
  `roster1_id` INT,
  `roster2_id` INT,
  FOREIGN KEY (`tournament_id`) REFERENCES `tournament`(`id`),
  FOREIGN KEY (`roster1_id`) REFERENCES `roster`(`id`),
  FOREIGN KEY (`roster2_id`) REFERENCES `roster`(`id`)
);

CREATE TABLE `goal` (
  `player_id` INT,
  `match_id` INT,
  FOREIGN KEY (`player_id`) REFERENCES `player`(`id`),
  FOREIGN KEY (`match_id`) REFERENCES `match`(`id`)
);
