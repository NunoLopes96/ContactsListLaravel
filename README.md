# LaravelContactsAPI

Hello there,

This is my playground to study/practice Domain-Driven-Design (DDD) software development and also Design Patterns.

There will be other repositories for this project:

[Domain Contacts Repository]( https://github.com/NunoLopesPT/DomainContacts )

React Repository:
- In my current company I've have experience with JavaScript ES6, creating small aplications, and as Backend Developer till the moment, I found
  very interesting and decided to explore more the frontend area, lately I've been in a course in Udemy of React/Redux. Which I will apply
  the best code I can there. The same as the Laravel Package, it is working but it lacks documentation and tests and so it is not available
  right now.
  
This Repository will be the application layer, a package for laravel that will be used mainly for Binding Contracts (DI (Probably will be removed)), Routes, 
Controllers and raising exceptions. It will be the API, no business logic should be here. The repository is working with Codeception API tests. All Controllers are 
100% covered, although I still have to work on the coverage reports.
 
This repository is also a laravel package, that can be auto-discovered using composer in your laravel package. The routes for the API will be
automatically created for the authentication and managing the contacts.
