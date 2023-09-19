# Simple forum task

This is a small task I did for a code test, the focus was for the backend, and only some frontend to vizualize the forum content and the possibility to view different forums. I have only saved part of the code on the server I have been working on and making additions to, as such this is not the complete server with files. I have made sure to protect the server of the company by censoring the server IP address. If the server is still up, then it is accessible by the outside using http://91.123.202.69 through a browser, feel free to take a look.

I tried to solve this task implementing a Token Service for token creation and authorization (using Firebase JWT), in the token I save the user type in the payload so I don´t have to make new requests to the database after having validated the user.

I also use Aura SQL Query builder to further prevent SQL injections and laying the foundation to easily implement more complicated queries in the future. I have a static class Response Handler that handles the response string creation.

This README was as much for me as for you reading this, I just wanted to collect a few commands that I happened to be running many times.

## Table of Contents

- [Error Handling and Debugging](#error-handling-and-debugging)
- [Testing the Backend](#testing-the-backend)
  - [Create a New Post](#create-a-new-post)
  - [Create a New User](#create-a-new-user)
  - [Delete a User](#delete-a-user)
  - [Update a User](#update-a-user)
- [Testing the Frontend](#testing-the-frontend)

---

## Error Handling and Debugging

If you encounter any issues or the server is not running as expected, follow these steps for error handling and debugging:

1. Look for any error logs related to your server or PHP in path /var/log/nginx/error.log. This log file may contain valuable information about the cause of the issue. You can clear the error log using:

```shell
sudo truncate -s 0 /var/log/nginx/error.log
cat /var/log/nginx/error.log
```

2. Enable error reporting: To display PHP errors on the page, enable error reporting by adding the following code at the top of the relevant PHP files:

```php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
```

Enabling error reporting will help you see any PHP errors or warnings that might be occurring during the execution of your code.
3. You can curl to the server locally using the tag -i to retireve any prints from PHP:

```shell
curl http://XXX.X.X.X:XXXX/login -X GET -H "Content-Type: application/json" -u "user3:secret3" -i
```

## Testing the Backend

You can test the backend API endpoints using cURL commands. Here are some examples of working and non-working cURL commands:

### Get your Auth token:

To get a new token as a user or admin:

```shell
curl -X GET -H "Content-Type: application/json" -u "<username>:<password>" http://XXX.X.X.X:XXXX/login -i
```

If you want do some tests as a guest user you can just do a simple get request to the server, and a token will be returned. If you try any requests without including the bearer token, the server will be fetching a new token for you along with the request.

### Create a New Post

You can create a new post as long as the user ID exists in the user table and forum ID exists in the forum table. There are no restrictions to who is allowed to make a post for a certain user in a forum. This could be changed by for example including the user id in the token payload and along with the validation and make sure that the user ID in the bearer token corresponds with the user_id in the input data.
To create a new post, use the following cURL command:

```shell
curl -X POST -H "Content-Type: application/json" -H "Authorization: Bearer <token>" -d '{ "forum_id":"<forum_id>", "user_id":"<user_id>", "message":"<message>" }' http://XXX.X.X.X:XXXX/post -i
```

### Create a New User

Only and admin can make changes to the user table, so in order to create a user you have to log in with an admin and recieve a bearer token.
To create a new user, use the following cURL command:

```shell
curl -X POST -H "Content-Type: application/json" -H "Authorization: Bearer <token>" -d '{ "name": "<name>", "password": "<password>", "admin":"<admin>" }' http://XXX.X.X.X:XXXX/user -i
```

### Delete a User

To delete a user, use the following cURL command:

```shell
curl -X DELETE -H "Content-Type: application/json" -H "Authorization: Bearer <token>" http://XXX.X.X.X:XXXX/user/<id> -i
```

### Update a User

To update a user, use the following cURL command:

```shell
curl -X PUT -H "Content-Type: application/json" -"Authorization: Bearer <token>" -d '{ "name": "<name>", "password": "<password>", "admin": <admin> }' http://XXX.X.X.X:XXXX/user/<id> -i
```

### Fetch Data from Server

Here are some examples on how you can fetch data by sending a get request to the server.

#### Get users

```shell
curl -X GET -H "Content-Type: application/json" http://XXX.X.X.X:XXXX/user -i
```

#### Get posts

```shell
curl -X GET -H "Content-Type: application/json" http://XXX.X.X.X:XXXX/post -i
```

#### Get forums

```shell
curl -X GET -H "Content-Type: application/json" http://XXX.X.X.X:XXXX/forum -i
```

#### Get specific entry

```shell
curl -X GET -H "Content-Type: application/json" http://XXX.X.X.X:XXXX/<entity>/<id> -i
```

#### Get posts for specific forum

```shell
curl -X GET -H "Content-Type: application/json" http://XXX.X.X.X:XXXX/post/forum/<forum_id> -i
```

### Complete Test Example

Here is a full example you can follow to test the backend:

#### Negative Tests

We will start with the negative tests, scenarios that should not work.

##### Guest User Trying Priviliged Actions

First use a get request to recieve the token, use this token for the other curl commands:

```shell
curl -X GET -H "Content-Type: application/json" http://XXX.X.X.X:XXXX/forum -i
curl -X POST -H "Content-Type: application/json" -H "Authorization: Bearer <token>" -d '{ "name": "<name>" }' http://XXX.X.X.X:XXXX/user -i
curl -X PUT -H "Content-Type: application/json" -H "Authorization: Bearer <token>" -d '{ "name": "<name>", "password": "<password>", "admin": <admin> }' http://XXX.X.X.X:XXXX/user/<id> -i
curl -X DELETE -H "Content-Type: application/json" -H "Authorization: Bearer <token>" -d '{ "id": "<user_id>" }' http://XXX.X.X.X:XXXX/user/<id> -i
curl -X POST -H "Content-Type: application/json" -H "Authorization: Bearer <token>" -d '{ "forum_id": "<forum_id>", "message": "<message>" }' http://XXX.X.X.X:XXXX/post -i
```

Confirm manually that nothing in the database changed either by checking in the browser or in the shell:
http://91.123.202.69:8000/user
http://91.123.202.69:8000/post

```shell
curl -X GET -H "Content-Type: application/json" http://XXX.X.X.X:XXXX/user -i
curl -X GET -H "Content-Type: application/json" http://XXX.X.X.X:XXXX/post -i
```

##### User Trying Priviliged Actions

Now log in with a user that is missing admin rights and use that token in the following curl commands:

```shell
curl -X GET -H "Content-Type: application/json" http://XXX.X.X.X:XXXX/user -i
curl -X GET -H "Content-Type: application/json" -u "<username>:<password>" http://XXX.X.X.X:XXXX/login -i
curl -X POST -H "Content-Type: application/json" -H "Authorization: Bearer <token>" -d '{ "name": "testName", "password": "testPassword", "admin": 0 }' http://XXX.X.X.X:XXXX/user -i
curl -X PUT -H "Content-Type: application/json" -H "Authorization: Bearer <token>" -d '{ "name": "nameTest", "password": "passwordTest", "admin": 0 }' http://XXX.X.X.X:XXXX/user/<id> -i
curl -X DELETE -H "Content-Type: application/json" -H "Authorization: Bearer <token>" -d '{ "id": "<user_id>" }' http://XXX.X.X.X:XXXX/user/<id> -i
```

Confirm manually that nothing in the database changed either by checking in the browser or in the shell:
http://91.123.202.69:8000/user

```shell
curl -X GET -H "Content-Type: application/json" http://XXX.X.X.X:XXXX/user -i
```

##### Admin Accessing Non Existing Entities

Log in with a user that has Admin rights and try to make changes and delete a non existing user:

```shell
curl -X GET -H "Content-Type: application/json" http://XXX.X.X.X:XXXX/user -i
curl -X GET -H "Content-Type: application/json" -u "AdminUser:This is the secret admin user password http://XXX.X.X.X:XXXX/login -i
curl -X PUT -H "Content-Type: application/json" -H "Authorization: Bearer <token>" -d '{ "name": "<name>", "password": "<password>", "admin": <admin> }' http://XXX.X.X.X:XXXX/user/<id> -i
curl -X DELETE -H "Content-Type: application/json" -H "Authorization: Bearer <token>" -d '{ "id": "<user_id>" }' http://XXX.X.X.X:XXXX/user/<id> -i
```

Confirm manually that nothing in the database changed either by checking in the browser or in the shell:
http://91.123.202.69:8000/user

```shell
curl -X GET -H "Content-Type: application/json" http://XXX.X.X.X:XXXX/user -i
```

#### Positive Tests

Now we will try to succsessfully make changes to the database.

##### Admin Creates, Changes and Deletes User

Since you are already logged in as an admin user from the previous step:

```shell
curl -X POST -H "Content-Type: application/json" -H "Authorization: Bearer <token>" -d '{ "name": "testName" }' http://XXX.X.X.X:XXXX/user -i
```

Now check that the user has been created, and check the user id:

```shell
curl -X GET -H "Content-Type: application/json" http://XXX.X.X.X:XXXX/user -i
```

Now we will try to do an update without any data, this should be ignored and not access the database for unnecessary update then we will double check the database, update only name, check the database, delete the user and check the database again to confirm the changes.

```shell
curl -X PUT -H "Content-Type: application/json" -H "Authorization: Bearer <token>" -d '{ "name": "<name>", "password": "<password>", "admin": <admin> }' http://XXX.X.X.X:XXXX/user/<id> -i
curl -X GET -H "Content-Type: application/json" http://XXX.X.X.X:XXXX/user/<id> -i
curl -X PUT -H "Content-Type: application/json" -H "Authorization: Bearer <token>" -d '{ "name": "<name>", "password": "<password>", "admin": <admin> }' http://XXX.X.X.X:XXXX/user/<id> -i
curl -X GET -H "Content-Type: application/json" http://XXX.X.X.X:XXXX/user/<id> -i
curl -X DELETE -H "Content-Type: application/json" -H "Authorization: Bearer <token>" -d '{ "id": "<user_id>" }' http://XXX.X.X.X:XXXX/user/<id> -i
curl -X GET -H "Content-Type: application/json" http://XXX.X.X.X:XXXX/user/<id> -i
```

##### Admin and Regular User Creates a Post

I was going to add some more manual test steps here but time ran out on me.

## Testing the Frontend

The frontend is accessible through IP-address http://91.123.202.69/, it is open for requests on port 8000, that can be used to fetch the JSON objects, through the frontend you can view the forums, posts of a forum and the task description. The forum posts are descending in order so the newest post is shown first, and the olders in the bottom. I have not made any changes to how many posts that are to be viewed in a page, and deleted users that return a null value while fetching posts for the forum will simply print out \<deleted\>. I was not sure how we wanted the design here, but usually we want to keep the user and their posts if they ever want to reinstate their account for example, but I could ofcourse also have made sure during user deletion to remove all associated posts.

### In Browser

#### Get All Posts

http://91.123.202.69:8000/post

#### Get All Users

http://91.123.202.69:8000/user

#### Get All Forums

http://91.123.202.69:8000/forum

#### Get Individual JSON

http://91.123.202.69:8000/post/1
