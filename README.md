## Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes.

The API documentation can be found at the following url: https://documenter.getpostman.com/view/22569385/2s93K1pez1

### Prerequisites
- [PHP](https://secure.php.net/downloads.php) >= 8.1
- [Composer](https://getcomposer.org/)

### Installation
1. Clone the repository
```
    git clone https://github.com/Ojsholly/hospital-backend-api.git
```

2. Install dependencies
```
    composer install
```

3. Create the project environment file
```
   cp .env.example .env
```

4. Generate a new application key
```  
    php artisan key:generate
```
5. Add mailing and database credentials.
```  
    Add mailing and database credentials to the .env file. Mailtrap is recommended for local and testing environments.
```

6. Seed the Database
```  
    php artisan migrate:fresh --seed
```

7. Start queue listener.
   Open a fresh terminal window in the project directory and run the following command.

```  
    php artisan queue:listen
```

### Usage

```
    The admin login credentials can be found in the .env.example file. The credentials are 
    'email' => 'olusolaojewunmi@gmail.com'
    'password' => '12345678'
    
    A user account can be retrived from the database and the same password used for attempting user login.

    
```

### Testing
The project includes a PHPUnit test suite. To run the tests, execute the following command:
```
 php artisan test
```
