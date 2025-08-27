# Filament Practice Project

This is a practice project created for learning and testing various features of Filament. The project also incorporates Livewire for dynamic interactions.

## Features

- Filament for content management
- Livewire implementation for dynamic interactions
- Big Plan management system
- Authentication system
- RTL (Right-to-Left) Persian user interface

## Technologies

- Laravel
- Filament
- Livewire
- Tailwind CSS
- Alpine.js

## Installation

### 1. **Start All Containers**
Bring up all services (app, db, nginx, redis, queue, scheduler, vite, etc.):
```sh
docker-compose up -d
```

---

### 2. **Set Up Your Environment File**
If you haven’t already:
```sh
cp .env.example .env
```
Edit `.env` and set:
```
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=filament
DB_USERNAME=filament
DB_PASSWORD=filament_password
REDIS_HOST=redis
```
(Adjust values if you changed them in `docker-compose.yml`.)

---

### 3. **Install Composer Dependencies**
```sh
docker-compose exec app composer install
```

---

### 4. **Generate Laravel App Key**
```sh
docker-compose exec app php artisan key:generate
```

---

### 5. **Run Migrations**
```sh
docker-compose exec app php artisan migrate:fresh --seed
```

---

### 6.
 ```sh
    docker-compose exec app npm install
 **(Optional) Build Frontend Assets**
If you want to build assets manually (for production):

docker-compose exec app npm run build 
```


---

### 7. **Access Your App**
- Visit: [http://localhost:8000/admin](http://localhost:8000/admin) (Nginx proxy to Laravel public directory)

```sh
user : admin@filamoneh.com 
pass : password 
```
---

**You now have a full TALL stack (Laravel, Tailwind, Alpine, Livewire, Filament) running in Docker!**




