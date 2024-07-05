# todolist
This project is a full stack practice ground created during my workshops.

> __Tech stack:__ JS, Vue.js 3 & Vite, SCSS, PHP, MySQL.

![Todo App preview](/frontend/public/todoapp-preview.png)
---

## Project setup

__Backend:__
- Create a MySQL database called `todo` and update the credentials in the index.php file.
  ```sql
  CREATE TABLE `tasks` (
    `id` smallint(6) NOT NULL AUTO_INCREMENT,
    `label` text NOT NULL,
    `description` text,
    `completed` tinyint(1) NOT NULL DEFAULT '0',
    `assignee` int(4) DEFAULT NULL,
    `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

  CREATE TABLE `users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `firstName` varchar(255) DEFAULT NULL,
    `lastName` varchar(255) DEFAULT NULL,
    `username` varchar(255) NOT NULL,
    `email` varchar(255) DEFAULT NULL,
    `password` varchar(255) NOT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

  ALTER TABLE `users`
    ADD PRIMARY KEY (`id`);

  ALTER TABLE `tasks`
    ADD PRIMARY KEY (`id`),
    ADD KEY `assignee` (`assignee`),
    ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`assignee`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION;
  ```
- `cd api`
- `composer install`

__Frontend:__
- `cd frontend`
- `pnpm i`

## Running the project
- Needs Apache running on port `:80` (or update the proxy in the vue.config.js)
- from the `frontend/` dir:
  ### Compiles and hot-reloads for development
  ```
  pnpm run serve
  ```

  ### Compiles and minifies for production
  ```
  pnpm run build
  ```

  ### Lints and fixes files
  ```
  pnpm run lint
  ```
