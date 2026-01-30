## Pre prep

clone this repository and run the following command. 
rename the .env.example file to .env 
delete the compose.lock file if available. 

## How to start the application

docker run --rm \
-u "$(id -u):$(id -g)" \
-v "$(pwd):/var/www/html" \
-w /var/www/html \
laravelsail/php82-composer:latest \
composer install --ignore-platform-reqs


## Plugins seeder queue
Since the seeding branches and tags of plugins, from git to database takes time, seperate jobs have been created to sync them.  
Those jobs will run in seperate queue: **pluginsSync**  
run above queue worker with
> php artisan queue:work --queue=pluginsSync  --timeout=900


## Post prep

Once the instance is up, access the laravel.test image using following docker command 

'docker-compose exec laravel.test bash' to access the shell
'php artisan migrate' to create the tables
'php artisan db:Seed' to insert test data
'php artisan passport:keys' to generate the auth keys

'php artisan passport:client --password' to generate client id and client secret. Once entered you will be prompted  two messages as following. Hit enter without adding any value for below prompts. 

* What should we name the password grant client? [Laravel Password Grant Client]:
* Which user provider should this client use to retrieve users? [users]:

You will be prompted two keys as following. Copy and keep them. 

Client ID: 1
Client secret: GM4cWDzm6l2PZ9x3onf8G3y4BZPOvJA6tNmGaL3s


## API architecture

The architecture of the laravel source is modified from the original source to Application, Domain, Infrastructure structure. 

**Application** consists of Controllers and Requests which directly communicates with the routes ( API and Web routes).

**Domain** interacts with business rules, Entities and data classes that defines entity objects. Also definition of *Service repository architecture* that granularize further helping to define attrubution in the business logic.

**Infrastructure** defines how application behaves directry with the server side events such as *Commands*, *Exception handling*, providers ..etc

**Jobs** interacts with queues and background tasks in the application.