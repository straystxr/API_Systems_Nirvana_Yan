# FlashPoint API Documentation

## API Used
This application uses a RESTful API principles. With every request, there will be a JSON response. Some of the endpoints are public and accessible by any user while others aren't, meaning the user has to be logged to access **protected endpoints.**
### Authentication Flow

1. Register a user account
2. Log in to receive an access token
3. Include the token in the Authorization header of every protected request

Authorization: Bearer *******************************************************************************************...
Tokens expire after 1 hour. Users must log in again after that 1 hour.

## Different Types of Users
* General - This user will not be permitted to create articles, is able to read articles, bookmark and comment on them.
* Journalistic - Everything a general user can do + create articles
* Verifier - Everything a general user can do but will be able to verify/remove any type of media on the application.
* Admin - This user type is allowed to do everything and have access to everything.

## HTTP Source Codes within Application
| **Source** | **Meaning** |
-- | --
200 | Success
201 | Successfully created
400 | Bad request — missing or invalid data
401 | Unauthorized — no token or invalid token
403 | Forbidden — you don't have permission
404 | Not found — resource doesn't exist
405 | Method not allowed — wrong HTTP verb
409 | Conflict — already exists (e.g. duplicate bookmark)
500 | Server error

## All the Endpoints
1. Register        POST auth/auth.php?action=register [done by Yan]
2. Login           POST auth/auth.php?action=login  → copy access_token [done by Yan]
3. View articles   GET  articles.php?action=list [done by Nirvana]
4. Create article  POST articles.php?action=create  (journalist token) [done by Nirvana]
5. Verify article  PATCH articles.php?action=verify (verifier token) [done by Nirvana]
6. Comment         POST articles.php?action=comments [done by Nirvana]
7. Bookmark        POST bookmarks.php?action=add [done by Nirvana]
8. View bookmarks  GET  bookmarks.php?action=list [done by Nirvana]
9. Remove bookmark DELETE bookmarks.php?action=remove [done by Nirvana]

## Register Endpoint
## Login Endpoint
## View Articles - GET
* Returns a list of all articles.
* This endpoint is public — no token needed.
* Results can be filtered using optional query parameters.

#### No Filtering
GET articles.php?action=list
#### Filtering by Status
GET articles.php?action=list&status=pending
#### Filtering by Category
GET articles.php?action=list&category=politics

#### Filtering Unverified Articles
GET articles.php?action=list&verified=0
#### Query Parameters
Parameter Types Required **Description** **Action** & **String**  

Must be list status string

* Filter by article status e.g. pending, publishedcategorystring
* Filter by category e.g. news, politics, sportverifiedinteger
* 0 for unverified, 1 for verified
### Success Response
200 - Success
```
json{
  "count": 2,
  "articles": [
    {
      "id": 1,
      "user_id": 1,
      "title": "New Protest in Valletta",
      "body": "Hundreds gathered outside Parliament...",
      "category": "politics",
      "lat": 35.8997,
      "lng": 14.5147,
      "source": "FlashPoint Reporter",
      "url": "https://example.com/article",
      "status": "pending",
      "verification_status": "unverified",
      "created_at": "2026-05-16 10:30:00",
      "author_name": "nirvana_vella",
      "author_display": "Nirvana Vella"
    }
  ]
}
```
## Create Articles - POST
* Submits a new article. 
* Requires authentication. 
* Only users with the role journalist or admin can create articles. 

POST articles.php?action=create

The article is automatically set to status: pending and verification_status: unverified — it must be reviewed by a verifier before it is published.
### Headers Required
1. Authorization: Bearer YOUR_TOKEN_HERE
2. Content-Type: application/json
### Request Body
```
{
  "title": "New Protest in Valletta",
  "body": "Hundreds gathered outside Parliament this morning calling for environmental reform...",
  "category": "politics",
  "lat": 35.8997,
  "lng": 14.5147,
  "source": "FlashPoint Reporter",
  "url": "https://example.com/full-article"
}
```
### Success Response
**201 - Successfully Created**
```
{
  "message": "Article submitted for verification",
  "article_id": 3,
  "status": "pending",
  "verification_status": "unverified"
}
```
**401 - Authorization Required**
Cause - No token provided

**403 - Only Journalists Allowed**
Cause: User is logged in with general

**400 - Missing field: title**
Cause: A required field is absent.

**500 - Failed to create article**
Cause: Database error (server-side)
## Verify Articles - PATCH
* Updates the verification status of an article.
* Requires authentication.
* Only users with the role verifier or admin can use this endpoint.

PATCH articles.php?action=verify

Articles follow a structured three-stage verification flow:
**unverified** → **in_progress** → **verified**

* **unverified** — just submitted, no verifier has reviewed it yet
* **in_progress** — a verifier is actively reviewing it
* **verified** — approved and automatically published

Headers Required
```
Authorization: Bearer YOUR_TOKEN_HERE
Content-Type: application/json
```

#### Request Body — Move to In Progress
```
json{
  "article_id": 3,
  "status": "in_progress"
}
```
#### Request Body — Approve and Publish
```
json{
  "article_id": 3,
  "status": "verified"
}
```
#### Fields
The ID of the article to verifystatusstring
* Must be one of: unverified, in_progress, verified
### Source Responses
200 - Successfully Updated
```
json{
  "message": "Article verification updated",
  "article_id": 3,
  "verification_status": "verified"
}
```
* 401 - Unauthorized
Cause: No token provided or token is invalid
* 403 - Forbidden
Cause: User does not have the verifier or admin role
* 400 - Missing article_id
Cause: article_id field is absent from the request body
* 400 - Invalid status
Cause: Status value is not one of the allowed options
* 404 - Article Not Found
Cause: No article exists with the provided article_id
## Comment - GET/POST/PATCH
## Bookmark - POST
## View Bookmark - GET
## Remove Bookmark - DELETE

