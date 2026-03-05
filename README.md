### PHP verzió: 8.3 vagy újabb
### Keretrendszer: Laravel 12
### Frontend keretrendszer: Vue 3.x

Klónozd a projektet  
git clone https://github.com/Giruna/egyszerusitett_pontszamito.git  
cd egyszerusitett_pontszamito  

### Backend függőségek telepítése  
composer install  

Környezeti változók beállítása (Backend)  
Másold le az alapértelmezett környezeti konfigurációt, majd generálj alkalmazáskulcsot.  
cp .env.example .env (windows: copy .env.example .env)  
php artisan key:generate  

Backend alkalmazás indítása  
php artisan serve

A backend alapértelmezett URL-je:  
http://127.0.0.1:8000

### Frontend függőségek telepítése és indítása  

cd frontend  
cp .env.example .env (windows: copy .env.example .env)  
npm install  
npm run dev  
cd ..  

A frontend alapértelmezett URL-je:  
http://localhost:5173
