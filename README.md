# 🐂 BullPlans Server — Laravel REST API backend

A **BullPlans Server** a BullPlans platform szerveroldali komponense: egy **biztonságos, skálázható és jól strukturált Laravel backend**, amely REST API-n keresztül kiszolgálja a mobilalkalmazást.

A rendszer célja, hogy átlátható módon összekösse a **személyi edzőket** és a **felhasználókat**, támogatva a profilkezelést, edzéstervekhez kapcsolódó adatkezelést, valamint a fájlok biztonságos kezelését.

---

## ✨ Fő funkciók

### 👨‍🏫 Edzőknek
- Edzői profilkezelés
- Kliensekhez kapcsolódó adatok kezelése
- Fájlok feltöltése és kezelése (PDF, képek, segédanyagok)
- Biztonságos fájlletöltés jogosultság ellenőrzéssel

### 🏃 Felhasználóknak
- Profilkezelés
- Fiókhoz kapcsolódó adatok és hozzáférések kezelése
- Edzői fájlok elérése jogosultsági szabályok alapján

### 🛡️ Rendszerszintű funkciók
- REST API endpoint struktúra
- Service alapú üzleti logika (Service réteg)
- Controller réteg a végpontok kezelésére
- Request validációk (FormRequest)
- Middleware-ek a hozzáférésekhez és védelemhez
- Naplózás és hibakezelés

---

## 🧱 Technológiai stack
- Backend: PHP / Laravel
- Adatbázis: MySQL / MariaDB (teszteknél SQLite)
- API: REST
- Storage / Fájlkezelés: Laravel Filesystem + saját FileService
- Tesztelés: PHPUnit (Unit + Feature)

---

## 🏗️ Architektúra áttekintése

A projekt rétegzett felépítésű:
- Routes → API útvonalak (routes/api.php)
- Controllers → HTTP kezelőréteg
- Services → üzleti logika, feldolgozás
- Models / DB → adattárolás Eloquent + migrációk
- Requests → bemeneti adatok validálása
- Middleware → hozzáférés és jogosultság ellenőrzés

---

## 🔐 Biztonság

A BullPlans Server fejlesztésénél a biztonság kiemelt szempont:
- hashelt jelszavak (Laravel hashing)
- token alapú hitelesítés
- role-based hozzáférés és middleware védelem
- input validációk (FormRequest)
- biztonságos fájlkezelés
  - jogosultság ellenőrzés letöltés előtt
  - fájl típus / méret korlátozás (igény szerint)
- naplózás és hibakezelés

---

## 📁 Projektstruktúra

A főbb mappák:
- app/ — alkalmazás logika (controllers, services, models, requests, middleware)
- routes/ — API / web route-ok
- database/ — migrációk, seeders, factories
- tests/ — unit és feature tesztek
- storage/ — runtime fájlok (log, cache, feltöltések)
- public/ — belépési pont + publikus assetek

Megjegyzés: a vendor/, storage/ runtime tartalma és a .env NEM része a repónak.

---

## 🚀 Telepítés és futtatás

1) Függőségek telepítése: composer install  
2) Környezeti fájl létrehozása: .env.example → .env  
3) Laravel kulcs generálása: php artisan key:generate  
4) Migrációk futtatása: php artisan migrate  
5) Storage link (ha szükséges): php artisan storage:link  
6) Szerver indítása: php artisan serve  

Alapértelmezett cím: http://127.0.0.1:8000

---

## ✅ Tesztelés

A projekt Unit és Feature teszteket tartalmaz.  
Teszt futtatása: php artisan test  

A tesztkörnyezet alapból SQLite :memory: adatbázissal fut (gyors és izolált).

---

## 📌 API használat

Az API végpontok a routes/api.php fájlban találhatók.  
A végpontok Controller → Service rétegen keresztül működnek.

---

## 🎯 Fejlesztési célok
- tiszta, bővíthető backend architektúra
- jól strukturált Laravel kód (service + request + middleware)
- magas szintű biztonság
- automatikus tesztek
- jól karbantartható API

---

## ℹ️ Megjegyzés

Ez egy aktív fejlesztés alatt álló szakdolgozati / portfólió projekt, amely hosszú távon éles felhasználásra is bővíthető.

---

## 📩 Kapcsolat

Fejlesztő: Horváth János  
Projekt: BullPlans Server
