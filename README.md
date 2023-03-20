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

### Deployment
The project can be hosted on any VPS via the following steps:
1. Install PHP and Composer on the VPS.
2. Clone the repository.
3. Install dependencies.
4. Create the project environment file.
5. Generate a new application key.
6. Add mailing, storage (cloudinary) database, and payment credentials.
7. Seed the Database.
8. Start queue listener.
9. Configure the web server to serve the project.


### Testing
The project includes a PHPUnit test suite. To run the tests, execute the following command:
```
 php artisan test
```

### Improvements
- Add a feature to allow users to view their appointment and transaction history.
- Add a feature to allow users to view their profile.
- Add a feature to allow the admins to see all users and doctors.
- Add a feature to allow the admins to view all appointments and transactions.
- Add a feature to allow the admins to view all appointments and transactions for a particular user.
- Add a feature to allow the admins to view all appointments and transactions for a particular doctor.
- Add a feature to allow the admins to view all appointments and transactions for a particular date.
- Add a feature to allow the doctors withdraw their earnings to their bank accounts.

