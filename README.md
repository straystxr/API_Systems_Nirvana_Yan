# API_Systems_Nirvana_Vella
This repository will be used for all the API Systems unit assignments
Log Ins - kyn@flashpoint.mt 
          newpassword456

          eln@flashpoint.mt
          password123

## Main Functionalities
Within this application the main features are the interactive map, adding pins to add content, and the verification system. 
The app will have three primary users: Regular Users, Premium Users & Admin/Verifiers.

## Application Roles
This section will be used to explain the different types of roles within the application and their restrictions.
### Regular Users
Regular Users will only be able to view articles, bookmarks articles and comment string comments with a maximum of 3 comments without any media. 
### Premium Users
Premium users will be separated into two, regular premium & journalistic.
#### Regular Premium
Regular premium users will have access to all the articles but will only be able to view them not create, comments are unlimited and can be with media (the media will also be verified by the admin team). These users will also be given customization to their profile but without a portfolio section.
#### Journalistic Premium
These users will be the only type of users apart from admin which will be able to post articles upon this map with media, their posts must be verified by an admin, their profile will also be customizable but with a portfolio section to showcase their abilities + an opportunity to find a job.
## Admin
The admin users will be able to verify, delete any content going against the "guidelines", be able to upload short-form videos on the media tab and will be able to do any changes necessary so basically anything that is programmed to do within the application.

## Core Classes
The Flashpoint application has the following classes which allow us to manipulate any data that needs to be manipulated from the database which is connected through the config.php/helpers.php
### Users.php - Done by Yan
### Memberships.php - Done by Yan
### Articles.php - Done by Nirvana
### Bookmarks.php - Done by Nirvana
### Comments.php - Done by Nirvana

## Main App Scripts
The application is a standalone Angular CLI Ionic project with a tabs template included within the project as in the early stages of the design it was immediately clear that th application would need at least a minimum amount of 3 tabs which then evolved into 4 tabs to navigate between the four main pages after logging in: Map Tab (tab1.page), Media Tab(tab4.page), Bookmark Tabs(tab2.page) & Profile Tab(tab3.page).


# Sources
### Map Intergration 
Video 1: https://www.youtube.com/watch?v=L-izDYEeJmA
Video 2: https://www.youtube.com/watch?v=ls_Eue1xUtY&list=PLyWyQBSWLw1NH1wsA0wkSMTlQ45P0AqCj&index=1
