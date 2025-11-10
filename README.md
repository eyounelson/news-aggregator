# News Aggregator

## How to get started.
### Clone this project
Clone the project and switch ot it's working directory:
```shell
git clone https://github.com/eyounelson/news-aggregator.git
```
```shell
cd news-aggregator
```

### Setup API keys
Currently 3 data sources are supported and listed below. Use the Links to obtain their API keys and store safely.

### NewsApi.ORG
- Sign up at [NewsApi portal](https://newsapi.org/) to obtain your API Key

### New York Times 
- Sign up at the NY Times developers portal.
- Set up an APP
- Obtain your API key. More details on their [get started guide](https://developer.nytimes.com/get-started).
  
### The Guardian
- Sign uo to their Open Platform to obtain an API key. Start [here](https://open-platform.theguardian.com/access/).

## Configure The Project

### Setup API Keys
Now that you have got the various API Keys, update them in project `.env`.

When running for the first time, copy the example environments file to one that is read by Laravel.
```shell
cp .env.example .env
```

Next, if running the project for the first time, install the required packages and set up application test keys:
```shell
composer install && php artisan key:generate
```

Now, open the project wit your favourite code editor and update the .env with
the various News Source API keys accordingly:

```
NEWSAPI_API_KEY=
NY_TIMES_API_KEY=
THE_GUARDIAN_API_KEY=
```

### Setup The Database
Edit the `.env` file once again, this time update the database credentials accordingly.
This project has been tested wth MySQL 8+ and SQLite:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=news_aggregator
DB_USERNAME=root
DB_PASSWORD=
```

## Use The Project

### Pull In News Data
First, run the migrations for the first time to create the necessary tables:
```shell
php artisan migrate
```

Next and subsequently, run the news aggregating command to pull in some news

```shell
php artisan news:update
```

Additionally, to keep your database regularly updated, you will need to set up a cron job
for the project's scheduler.
This will update the news items every 30 minutes. 
More Info on that at the [Laravel Documentation](https://laravel.com/docs/12.x/scheduling#running-the-scheduler).

### Start The API Server

Use the inbuilt development server to get started:
```shell
php artisan serve
```

Next, you may the project using the `baseURL` printed out in your console using the instructions below.
Typically, that will be `http:127.0.0.1:8000`.
Take note of this URL as that will be dropped in into the endpoints provided below.

### Access The API
There are 4 main endpoints provided by this project:

#### List All Articles
This returns a paginated resource
```
Get {baseURL}/api/articles
```

#### Filter Articles
Article filtering can be achieved by passing a `filter[field_name]=value` query parameter to this endpoint. You can pass
a comma separated list as `value` to filter by multiple values. Ex. `filter[sources]=NyTimes,NewsApi` to get articles sourced
from NYTines or NewsApi.

Below are available filter fields.

| Filter Parameter   | Description                                                                                      |
|--------------------|--------------------------------------------------------------------------------------------------|
| filter[sources]    | Filter by the Given News Source. Possible Values are `NYTimes`, `NewsApi` and `TheGuardian`.     |
| filter[categories] | Filter post to the specified category names. Use the Categories endpoint to list all categories. |
| filter[authors]    | Filter posts by the specified authors. Use the authors endpoint to list all authors.             |
| filter[date_from]  | Only return articles published from the provided date upward, formatted as YYYY-MM-DD            |
| filter[date_to]    | Only return published before on on the specified date, formated as YYYY-MM-DD                    |
| filter[search]     | Only return articles that have the specified term in its title or content.                       |

#### List Categories
This returns a paginated resource.
```
GET {baseURL}/api/categories
```

To filter categories by it's name, add a `filter[name]=value` as a query parameter


#### List Authors
This returns a paginated resource
```shell
GET {baseURL}/api/authors
```
To filter authors by their name, add a `filter[name]=name` a query parameter. 

To filter by their affiliated source, use a `filter[source]=value`  query parameter.

#### List Sources
```shell
GET {baseURL}/api/sources
```
