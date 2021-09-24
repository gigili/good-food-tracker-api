# Good Food Tracker #

![Dependecy Badge](https://img.shields.io/librariesio/github/gigili/good-food-tracker-api?style=for-the-badge)
![Repo size badge](https://img.shields.io/github/repo-size/gigili/good-food-tracker-api?style=for-the-badge)
![Open issues badge](https://img.shields.io/github/issues/gigili/good-food-tracker-api?style=for-the-badge)
![Licence badge](https://img.shields.io/github/license/gigili/good-food-tracker-api?style=for-the-badge)
![Forks badge](https://img.shields.io/github/forks/gigili/good-food-tracker-api?style=for-the-badge)
<!--![Stars badge](https://img.shields.io/github/stars/gigili/good-food-tracker-api?style=for-the-badge)-->
<!--![Top language badge](https://img.shields.io/github/languages/top/gigili/good-food-tracker-api?style=for-the-badge)-->
<!-- ALL-CONTRIBUTORS-BADGE:START - Do not remove or modify this section -->
[![All Contributors](https://img.shields.io/badge/all_contributors-4-orange.svg?style=flat-square)](#contributors-)
<!-- ALL-CONTRIBUTORS-BADGE:END -->

The project aims to allow the users to take pictures and/or leave notes, ratings, comments about restaurants they visit
in order to be able to reference it later when they try to pick were they want to go eat out or order from.

### What is this repository for? ###

This repository is for the back end API built

For the list of existing or currently being developed feature please refer to the [features](#features) section of this
document.

### How do I get set up? ###

To get started on development follow these steps:

* Rename `.env.example` to `.env`;
    * Add your values to the `.env` file;
* Start up docker containers with `docker-compose up`
* Create `postgres` database;
    * Rename `.migration.example.config` into `.migration.config`
        * Add your values to the `.migration.config` file;
    * Run `php cli/migrate.php --init` to initialize the migrations table;
    * Run `php cli/migrate.php --up` to run all the migrations;
* Visit `localhost:${APACHE_PORT}` to see if everything works;

### Contribution guidelines ###

* Keep code clean and simple;
* **DO NOT** alter the database directly, use migrations;
* Always use branches and pull requests when making changes to the codebase;

### Who do I talk to? ###

* If you have any question you can contact Igor Ilić via [e-mail](mailto:github@igorilic.net)
  or [twitter](https://twitter.com/Gac_BL)
* If you have found a bug or want to ask for a new feature, open a new issue

### Features ###

List of currently completed or still being developed feature:

* [x] Auth
    * [x] Login
    * [x] Register
    * [x] Verify account
    * [x] Reset password
        * [x] Request password reset code
        * [x] Verify reset code & set new password
* [x] User profile
    * [x] Get user profile information
    * [x] Update user profile information
    * [x] Delete user profile
* [ ] Country
    * [x] List of countries
    * [x] Get single country info
    * [x] Add / edit country
    * [ ] Delete country
* [ ] City
    * [ ] List of cities
    * [ ] Add / edit city
    * [ ] Delete city
* [ ] Restaurants
    * [ ] List of all the restaurants
    * [ ] Add / edit restaurant
    * [ ] Delete restaurant
* [ ] Reviews
    * [ ] List of users reviews
    * [ ] Add / edit your review
        * [ ] Add / remove image for a review
    * [ ] Delete your own review

### Notes ###

Database diagram with current, future and optional tables can be found
on [dbDiagram.io](https://dbdiagram.io/embed/5f58bd9e88d052352cb6870d).

Possible future tables in the database diagram will be positioned on the right side of the diagram. Whereas the existing
ones or the ones that are being worked on are positioned on the left side.

## Contributors ✨

Thanks go to these wonderful people ([emoji key](https://allcontributors.org/docs/en/emoji-key)):

<!-- ALL-CONTRIBUTORS-LIST:START - Do not remove or modify this section -->
<!-- prettier-ignore-start -->
<!-- markdownlint-disable -->
<table>
  <tr>
    <td align="center"><a href="http://negue.github.io"><img src="https://avatars3.githubusercontent.com/u/842273?v=4?s=100" width="100px;" alt=""/><br /><sub><b>negue</b></sub></a><br /><a href="#ideas-negue" title="Ideas, Planning, & Feedback">🤔</a></td>
    <td align="center"><a href="https://subhamsahu.me"><img src="https://avatars1.githubusercontent.com/u/43654114?v=4?s=100" width="100px;" alt=""/><br /><sub><b>Subham Sahu</b></sub></a><br /><a href="https://github.com/gigili/good-food-tracker-api/commits?author=subhamX" title="Code">💻</a></td>
    <td align="center"><a href="http://kabartolo.com"><img src="https://avatars3.githubusercontent.com/u/11848944?v=4?s=100" width="100px;" alt=""/><br /><sub><b>Kate Bartolo</b></sub></a><br /><a href="https://github.com/gigili/good-food-tracker-api/commits?author=kabartolo" title="Documentation">📖</a></td>
    <td align="center"><a href="http://rajie.space"><img src="https://avatars.githubusercontent.com/u/37059749?v=4?s=100" width="100px;" alt=""/><br /><sub><b>Rajakavitha1</b></sub></a><br /><a href="https://github.com/gigili/good-food-tracker-api/commits?author=Rajakavitha1" title="Documentation">📖</a></td>
  </tr>
</table>

<!-- markdownlint-restore -->
<!-- prettier-ignore-end -->

<!-- ALL-CONTRIBUTORS-LIST:END -->

This project follows the [all-contributors](https://github.com/all-contributors/all-contributors) specification.
Contributions of any kind welcome!
