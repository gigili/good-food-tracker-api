# Good Food Tracker #

Good food tracker project is a collection of a back end web API (Nodejs & Express), front end web app (Vuejs) and a mobile application (Kotlin). 

The project aims to allow the users to take pictures and/or leave notes, ratings, comments about restaurants they visit in order to be able to reference it later when they try to pick were they wanna go eat out or order from. 

### What is this repository for? ###

This repository is for the back end API built with Nodejs & Express 

### How do I get set up? ###

To get started on development follow these steps:
* Rename `.env.example` to `.env`;
    * Add your values to the `.env` file;
* Run `npm install`;
* Create `MySQL` database;
    * Run `db-migrate up` to run all database migrations;
* To start the dev version of the server run: `npm run dev`;
* Visit `localhost:3000` to see if everything works;
 
### Contribution guidelines ###

* Keep code clean and simple;
* **DO NOT** alter the database directly, use migrations;
* Always use branches and pull requests when making changes to the codebase;

### Who do I talk to? ###

* If you have any question you can contact Igor Ilić @ igorilicbl@gmail.com
* If you have found a bug or want to ask for a new feature, open a new issue

### Features ###
List of currently completed or still being developed feature: 

* [x] Login 
* [x] Register
* [x] User profile
    * [x] Get user profile information
    * [x] Update user profile information
    * [x] Delete user profile
* [x] Restaurants
    * [x] List of all the restaurants
    * [x] Add / edit restaurant
    * [x] Delete restaurant
* [ ] City
    * [ ] List of cities 
    * [ ] Add / edit city
    * [ ] Delete city
* [ ] Reviews
    * [ ] List of users reviews (personal / public)
    * [ ] List of all public reviews for a restaurant from everyone 
    * [ ] Add / edit your review
    * [ ] Delete your own review
    * [ ] Rate other users reviews (helpful / not helpful) 