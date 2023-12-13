# Qewam Backend Developer Project

This readme provides a simple guide to understand and set up the Qewam Backend Developer Project.

## Task Description

Create an invoicing API. The API needs to have endpoints to
1. Create a new invoice for a customer and persist the invoice data.
2. Show the details for one invoice. Each Customer pays by number and quality of
   Users. You need to determine the number and the quality of Users for each invoice
   and make sure Users are not counted twice across invoice periods.

## Installation

Follow these steps to install the project:

1. Clone the project repository:

   ```
   git clone git@github.com:abdurrahmantarek/Qewam-task.git
   ```

2. Navigate to the project directory:

   ```
   cd qawan-task
   ```

3. Create an environment configuration file by copying the example file:

   ```
   cp .env.example .env
   ```

## Environment Configuration

In the `.env` file, you'll find important configuration keys:

- `APP_PORT`: Set the application port (default is 80). You can change it if needed.

## Starting the Project

To start the project, use the following command:
```
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php82-composer:latest \
    composer install --ignore-platform-reqs
```
```
vendor/bin/sail up -d
```
```
vendor/bin/sail artisan key:generate
```

## Database Diagram

A database diagram showing entity relationships is available at ```/docs/erd/index.html ``` 

This diagram helps visualize the data structure.


## Postman Collection

This repository includes a Postman collection named ```Qewam.postman_collection.json```

## Running Test Cases
I've developed test scenarios to verify most aspects of the invoice functionality.

To run test cases for the project, use the following command:

```
vendor/bin/sail php artisan test --env="local"
```
