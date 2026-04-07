# Foo trip 🛫

### Required features:
-  Create a “Destination” entity with the following fields: name,
   description, price, duration of the trip, image of the destination. These fields should not be nullable
- Set up a database to store destinations
- Create a home page that displays the list of destination destinations
  honeymoon with their images and descriptions. Each destination should have a link so the user can click and access to the details of the destination
- Allow users to click on a destination to view the full details of that destination.
- Implement a basic authentication system for administrators.
- Add a backoffice where administrators can create, update and delete destinations.
- Add validation rule on the destination creation
- Expose a REST API for Destination, the API should be public that means no security token is needed
- We can filter the API per destination and name
- Create a Symfony command that GET all destinations via the API and export them into a CSV like the example below
- Add tests and tooling

| name  | description                        | price | duration |
|-------|------------------------------------|-------|----------|
| Paris | 3 nights in an hotel               | 100   | 7 days   |
| Tunis | 10 in a villa with a swimming pool | 200   | 17 days  |
|  |  |    |   |
|  |  |    |   |
|  |  |    |   |
And so on...

### Instructions

- You should use the Symfony framework (latest version)
- Don't waste time with CSS, we don't care for the exercice
- Add automated tests if possible
- Record avideo of the project with the functionalities

Contact me at `contact[at]smaine.me` in case of question.
